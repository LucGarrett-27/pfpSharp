#!/usr/bin/env bash
# Automated F2 acceptance test (User Accounts & Roles).
# Works on a COPY of the database, so real accounts are never changed.
#   From the project folder:  bash tests/f2_acceptance.sh
set -u
cd "$(dirname "$0")/.."

PORT=${PORT:-8765}
BASE="http://127.0.0.1:$PORT/public"
TMP=$(mktemp -d)
cp database/pfpsharp.db "$TMP/test.db"
export PFP_SHARP_DB_PATH="$TMP/test.db"
PASS=0; FAIL=0
check(){ if eval "$2"; then echo "  PASS  $1"; PASS=$((PASS+1)); else echo "  FAIL  $1"; FAIL=$((FAIL+1)); fi; }

# Bring the test copy up to date and add a test Admin with a known password.
php database/migrate.php >/dev/null
php -r '
require "config/database.php";
$pdo->prepare("INSERT INTO users (email, name, password_hash, role, active) VALUES (?, ?, ?, ?, 1)")
    ->execute(["testadmin@usna.edu", "Test Admin", password_hash("AdminPassw0rd!", PASSWORD_DEFAULT), "admin"]);'

php -S 127.0.0.1:$PORT >"$TMP/server.log" 2>&1 &
SERVER=$!
trap 'kill $SERVER 2>/dev/null; rm -rf "$TMP"' EXIT
sleep 1

# ---- helpers: each "who" has its own cookie jar (its own browser) ----
jar()   { echo "$TMP/$1.cookies"; }
get()   { curl -s -b "$(jar "$1")" -c "$(jar "$1")" -o "$TMP/body" -w '%{http_code}' "$BASE/$2"; }
token() { grep -o 'name="csrf" value="[^"]*"' "$TMP/body" | head -1 | sed 's/.*value="//; s/"$//'; }
post()  { # post <who> <page> <field=value>...   (gets a fresh CSRF token from the page first)
    local who=$1 page=$2; shift 2
    get "$who" "$page" >/dev/null
    local args=(--data-urlencode "csrf=$(token)") kv
    for kv in "$@"; do args+=(--data-urlencode "$kv"); done
    curl -s -b "$(jar "$who")" -c "$(jar "$who")" -o "$TMP/body" -w '%{http_code}' "${args[@]}" "$BASE/$page"; }
login() { curl -s -b "$(jar "$1")" -c "$(jar "$1")" -o "$TMP/body" -w '%{redirect_url}' \
          --data-urlencode "email=$2" --data-urlencode "password=$3" "$BASE/login.php"; }
body()  { cat "$TMP/body"; }

echo "F2 acceptance test"
echo "Step 1: Log in as Admin; go to users; create a user with role = Scheduler"
check "Admin login goes to the dashboard" '[[ "$(login admin testadmin@usna.edu AdminPassw0rd!)" == *dashboard.php ]]'
check "User Management opens for Admin" '[ "$(get admin users.php)" = 200 ]'
post admin users.php action=create "name=ENS Sam Scheduler" email=sched@usna.edu 'password=SchedPassw0rd!' 'roles[]=scheduler' >/dev/null
check "user appears with the Scheduler role" 'body | tr -d "\n" | grep -q "sched@usna.edu</td> *<td data-roles=\"scheduler\""'

echo "Step 2: Log out; log in as the new Scheduler"
get admin logout.php >/dev/null
check "after logout, dashboard sends you to login" '[ "$(get admin dashboard.php)" = 302 ]'
check "Scheduler login goes to the dashboard" '[[ "$(login sched sched@usna.edu SchedPassw0rd!)" == *dashboard.php ]]'
get sched dashboard.php >/dev/null
check "Scheduler tiles visible" 'body | grep -q "data-module=\"schedule\""'
check "User Management tile NOT visible" '! body | grep -q "data-module=\"users\""'

echo "Step 3: As the Scheduler, go directly to the users page"
check "users.php is denied (403)" '[ "$(get sched users.php)" = 403 ]'
check "module.php?module=users is denied too" '[ "$(curl -s -L -b "$(jar sched)" -o /dev/null -w "%{http_code}" "$BASE/module.php?module=users")" = 403 ]'

echo "Step 4: Log in as a Reviewer and attempt to edit a schedule"
login admin testadmin@usna.edu AdminPassw0rd! >/dev/null      # Admin signed out in step 2; sign back in to create the Reviewer
post admin users.php action=create "name=LCDR Pat Reviewer" email=rev@usna.edu 'password=RevPassw0rd!!' 'roles[]=reviewer' reviewer_level=OPSO >/dev/null
login rev rev@usna.edu 'RevPassw0rd!!' >/dev/null
check "Reviewer can view the schedule" '[ "$(get rev "module.php?module=schedule")" = 200 ]'
check "page says View only, no edit button" 'body | grep -q "data-access=\"view\"" && ! body | grep -q "data-testid=\"edit-button\""'
check "an edit sent directly is refused (403)" '[ "$(post rev "module.php?module=schedule")" = 403 ]'
check "the same edit works for the Scheduler" '[ "$(post sched "module.php?module=schedule")" = 200 ] && body | grep -q "Edit access confirmed"'

check "Schedule page opens for the Reviewer as View only" '[ "$(get rev Schedule.php)" = 200 ] && body | grep -q "data-access=\"view\""'
check "Schedule page lets the Scheduler edit" '[ "$(get sched Schedule.php)" = 200 ] && body | grep -q "data-access=\"edit\""'
check "Deconfliction tile opens the Schedule page" 'get sched dashboard.php >/dev/null; body | grep -q "href=\"Schedule.php\""'

echo "Step 5: Enter a wrong password on login"
check "sent back to login with the generic error" '[[ "$(login wrong testadmin@usna.edu not-the-password)" == *login.html?error=invalid ]]'
check "no session granted" '[ "$(get wrong dashboard.php)" = 302 ]'

echo "Extra checks"
REV_ID=$(php -r 'require "config/database.php"; echo $pdo->query("SELECT id FROM users WHERE email=\"rev@usna.edu\"")->fetchColumn();')
post admin users.php action=toggle_active user_id=$REV_ID >/dev/null
check "Admin can deactivate a user" 'body | grep -q "Deactivated LCDR Pat Reviewer"'
check "deactivated user is signed out on next click" '[ "$(get rev dashboard.php)" = 302 ]'
check "deactivated user cannot log back in" '[[ "$(login rev2 rev@usna.edu RevPassw0rd!!)" == *error=invalid ]]'
ADMIN_ID=$(php -r 'require "config/database.php"; echo $pdo->query("SELECT id FROM users WHERE email=\"testadmin@usna.edu\"")->fetchColumn();')
post admin users.php action=toggle_active user_id=$ADMIN_ID >/dev/null
check "Admin cannot deactivate themselves" 'body | grep -q "cannot deactivate your own account"'
post admin users.php action=create "name=Two Hats" email=both@usna.edu 'password=BothPassw0rd!' 'roles[]=scheduler' 'roles[]=reviewer' reviewer_level=AOIC >/dev/null
login both both@usna.edu BothPassw0rd! >/dev/null; get both "module.php?module=schedule" >/dev/null
check "user with two roles gets the access of both" 'body | grep -q "data-access=\"edit\""'
check "Schedule page requires login" '[ "$(get nobody Schedule.php)" = 302 ]'
check "form without the CSRF token is rejected" '[ "$(curl -s -b "$(jar admin)" -o /dev/null -w "%{http_code}" --data "action=create&name=x" "$BASE/users.php")" = 400 ]'
KEPT=$(php -r 'require "config/database.php"; echo $pdo->query("SELECT COUNT(*) FROM users WHERE role = \"admin\" AND email <> \"testadmin@usna.edu\"")->fetchColumn();')
ORIG=$(php -r '$p = new PDO("sqlite:database/pfpsharp.db"); echo $p->query("SELECT COUNT(*) FROM users WHERE lower(role) = \"admin\"")->fetchColumn();')
check "existing Admin accounts kept their role ($KEPT of $ORIG)" '[ "$KEPT" = "$ORIG" ]'
echo
echo "$PASS passed, $FAIL failed"
[ "$FAIL" -eq 0 ]

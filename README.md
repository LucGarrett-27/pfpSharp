# pfpSharp
Link to view Website https://midn.cs.usna.edu/~m270288/SI447/pfpSharp/public/login.html

## Setup after pulling the F2 (user roles) changes
Run this ONCE on the server, from the `pfpSharp` folder. It adds the new columns to the users table
and keeps every existing account and password:

    php database/migrate.php

Until it has run, signed-in pages will show a server error.

## Where things are
| Path | What it is |
|---|---|
| `public/` | Pages people open: `login.html`, `dashboard.php`, `Schedule.php` (schedule, editable by Schedulers only), `module.php` (other workspaces), `users.php` (Admin-only user management) |
| `includes/auth.php` | Every signed-in page includes this. Redirects to login if not signed in, and loads the user and their roles. |
| `includes/permissions.php` | The roles and what each one can view/edit. Change permissions here. |
| `includes/topbar.php` | Shared page header and navigation bar |
| `config/database.php` | Database connection (SQLite file in `database/`) |
| `database/migrate.php` | Updates the database structure (run once, see above) |
| `docs/roles.md` | Role permissions (F2 documentation) |
| `docs/f2AcceptanceTest.txt` | F2 acceptance test checklist |
| `tests/f2_acceptance.sh` | Automated F2 test: `bash tests/f2_acceptance.sh` (uses a copy of the database) |

`database/`, `config/`, `includes/`, `docs/`, and `tests/` each have an `.htaccess` that blocks them from the web.

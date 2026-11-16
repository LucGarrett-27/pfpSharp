# User Roles and Permissions (F2)

Every account has one or more roles. The rules live in one place:
`includes/permissions.php` (the `PERMISSIONS` list). Change them there.

| Workspace | Admin | Scheduler | Reviewer (OPSO / AOIC / OIC) |
|---|---|---|---|
| Schedule Deconfliction | View + edit | View + edit | View |
| View Schedule | View | View + edit | View |
| Student Data | View + edit | View + edit | View |
| Aircraft & Resources | View + edit | View + edit | View |
| Conflict Reports | View | View | View |
| User Management | View + edit | No access | No access |

## Who is who
- **Admin**: manages user accounts and the reference data (students, aircraft).
- **Scheduler**: the Scheds O. Builds and fixes the daily schedule.
- **Reviewer**: OPSO, AOIC, or OIC. Views everything and (in F13) approves or returns the schedule. The level is stored on the account now so F13 can use it.

## Rules the code enforces
- **Checked on the server, not just hidden.** Tiles and buttons a role can't use are hidden, but every page also checks permission. Typing the URL or sending the form directly still gets "Access denied" (403).
- **More than one role** (F2 edge case): a user can be, for example, Scheduler and Reviewer. They get everything either role allows. Stored as `scheduler,reviewer` in the `role` column.
- **Deactivated users** cannot log in, and are signed out on their next click.
- **Wrong email, wrong password, or deactivated account** all get the same message: "Invalid email or password."
- **No lock-out:** an Admin cannot deactivate their own account or remove their own Admin role, and there is always at least one active Admin.
- **Audit log:** logins, failed logins, denied pages, and account changes are written to the `audit_log` table.

## Managing users
Sign in as an Admin, open **User Management** on the dashboard, and use the form to create a user,
pick roles (and a level for Reviewers), and give them the temporary password in person.
Use **Edit** to change roles or reset a password, and **Deactivate** to turn an account off.

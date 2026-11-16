<?php
// F2: only signed-in users can open the schedule, and only roles allowed to EDIT
// 'deconfliction' (see includes/permissions.php) get editable cells.
require_once __DIR__ . '/../includes/auth.php';
require_permission('view', 'deconfliction');
$canEdit = can('edit', 'deconfliction');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PFP Sharp - Schedules</title>

    <!-- Bootstrap 5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="css/styles.css">

</head>

<body>

<nav class="topbar" aria-label="Main navigation">
    <a class="brand" href="dashboard.php"><span class="brand-icon">P</span><span>PFP SHARP</span></a>
    <div class="topbar-right">
        <span class="user-chip">
            <span class="user-email"><?= h($currentUser['email']) ?></span>
            <span class="user-role"><?= h(role_label($currentUser['roles'], $currentUser['reviewer_level'])) ?></span>
        </span>
        <a class="logout" href="dashboard.php">← Dashboard</a>
        <a class="logout" href="logout.php">Sign out ↗</a>
    </div>
</nav>


<div class="page-shell">
    <div class="welcome-row">
        <div>
            <p class="eyebrow">PFP SHARP</p>
            <h1 class="page-title">Schedules</h1>
            <p class="welcome-copy">Daily operations and scheduling information.</p>
            <?php if ($canEdit): ?>
                <span class="access-badge access-edit" data-access="edit">You can view and edit</span>
            <?php else: ?>
                <span class="access-badge access-view" data-access="view">View only</span>
            <?php endif; ?>
        </div>
    </div>



    <!-- WATCHBILL -->
    <section class="schedule-section">
        <h3>WATCHBILL</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover schedule-table"
                   data-type="standard">
                <thead>
                    <tr>
                        <th>START</th>
                        <th>END</th>
                        <th>WATCH</th>
                        <th>NAME</th>
                        <th>PHONE NUMBER</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- PFP STAFF -->
    <section class="schedule-section">
        <h3>PFP STAFF</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover schedule-table"
                   data-type="staff">
                <tbody>
                    <tr>
                        <th>PFP Director</th>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                    </tr>
                    <tr>
                        <th>PFP OIC</th>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                    </tr>
                    <tr>
                        <th>PFP OPSO</th>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                    </tr>
                    <tr>
                        <th>PFP AOPS</th>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- DATA -->
    <section class="schedule-section">
        <h3>DATA</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover schedule-table"
                   data-type="data">
                <tbody>
                    <tr>
                        <th>Sunrise</th>
                        <td contenteditable="true" class="editable"></td>
                    </tr>
                    <tr>
                        <th>Sunset</th>
                        <td contenteditable="true" class="editable"></td>
                    </tr>
                    <tr>
                        <th>Training Day</th>
                        <td contenteditable="true" class="editable"></td>
                    </tr>
                    <tr>
                        <th>Flights</th>
                        <td contenteditable="true" class="editable"></td>
                    </tr>
                    <tr>
                        <th>Events Schedules</th>
                        <td contenteditable="true" class="editable"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- SORTIES -->
    <section class="schedule-section">
        <h3>SORTIES</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover schedule-table"
                   data-type="standard">
                <thead>
                    <tr>
                        <th>WAVE</th>
                        <th>EVENT</th>
                        <th>BRIEF</th>
                        <th>T/O</th>
                        <th>LAND</th>
                        <th>INSTRUCTOR</th>
                        <th>STUDENT 1</th>
                        <th>STUDENT 2</th>
                        <th>MISSION</th>
                        <th>AIRCRAFT</th>
                        <th>WORKING AREA</th>
                        <th>FIELD</th>
                        <th>NOTES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- TRBs -->
    <section class="schedule-section">
        <h3>TRBs</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover schedule-table"
                   data-type="standard">
                <thead>
                    <tr>
                        <th>TIME</th>
                        <th>STUDENT</th>
                        <th>CFI</th>
                        <th>STAFF</th>
                        <th>LOCATION</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- CLASSROOM EVENTS -->
    <section class="schedule-section">
        <h3>CLASSROOM EVENTS</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover schedule-table"
                   data-type="standard">
                <thead>
                    <tr>
                        <th>START</th>
                        <th>END</th>
                        <th>EVENT</th>
                        <th>LOCATION</th>
                        <th>PERSONNEL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- TRANSPORTATION -->
    <section class="schedule-section">
        <h3>TRANSPORTATION</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover schedule-table"
                   data-type="standard">
                <thead>
                    <tr>
                        <th>FROM</th>
                        <th>TO</th>
                        <th>ETD</th>
                        <th>ETA</th>
                        <th>NOTES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                        <td contenteditable="true" class="editable"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- NOTES -->
    <section class="schedule-section">
        <h3>NOTES</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover schedule-table"
                   data-type="notes">
                <tbody>
                    <tr>
                        <td>
                            <select class="form-select notes-select">
                                <option value="">Select a note category...</option>
                                <option>General</option>
                                <option>Operations</option>
                                <option>Safety</option>
                                <option>Maintenance</option>
                                <option>Weather</option>
                                <option>Other</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

</div>
</div>

<script src="js/schedules.js"></script>
<?php if (!$canEdit): ?>
<script>
    // View-only roles (Admin, Reviewer): lock every cell and hide the "+" add-row buttons.
    // NOTE: this only changes the page. When the schedule starts SAVING to the database (F7),
    // the PHP that saves it must also call require_permission('edit', 'deconfliction').
    document.querySelectorAll('[contenteditable]').forEach(function (cell) {
        cell.contentEditable = 'false';
        cell.classList.remove('editable');
    });
    document.querySelectorAll('.add-row').forEach(function (cell) { cell.closest('tfoot').remove(); });
    document.querySelectorAll('select').forEach(function (select) { select.disabled = true; });
</script>
<?php endif; ?>
</body>
</html>
<?php
// The dashboard tiles. Each key matches a row in PERMISSIONS (includes/permissions.php).
const MODULES = [
    // 'link' sends a tile to its own page instead of the generic module.php placeholder.
    'deconfliction' => ['title' => 'Schedule Deconfliction', 'tag' => 'Core workspace', 'image' => 'assets/deconfliction.svg',
                        'link' => 'Schedule.php',
                        'description' => 'Review potential scheduling conflicts and see where further attention may be needed.',
                        'edit_label' => 'Resolve conflicts'],
    'schedule'      => ['title' => 'View Schedule', 'tag' => 'Scheduling', 'image' => 'assets/schedule.svg',
                        'description' => 'Open the current schedule and review planned events in one place.',
                        'edit_label' => 'Edit schedule'],
    'students'      => ['title' => 'Student Data', 'tag' => 'Records', 'image' => 'assets/students.svg',
                        'description' => 'View and maintain student records and related scheduling information.',
                        'edit_label' => 'Edit student records'],
    'aircraft'      => ['title' => 'Aircraft & Resources', 'tag' => 'Resources', 'image' => 'assets/aircraft.svg',
                        'description' => 'Keep track of aircraft and other resources that may affect a schedule.',
                        'edit_label' => 'Edit aircraft'],
    'reports'       => ['title' => 'Conflict Reports', 'tag' => 'Analysis', 'image' => 'assets/reports.svg',
                        'description' => 'Review summaries of flagged conflicts and scheduling activity.',
                        'edit_label' => null],
    'users'         => ['title' => 'User Management', 'tag' => 'Administration', 'image' => 'assets/users.svg',
                        'description' => 'Manage access roles and workspace settings for your team.',
                        'edit_label' => null],
];

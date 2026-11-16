<?php
//entire file from AI. Have no idea how databases work, just using this a temporary testing 
//model since we are not sure if we are even going to useSQLIT
// The automated test (tests/f2_acceptance.sh) points this at a throwaway copy instead.
$dbPath = getenv('PFP_SHARP_DB_PATH') ?: dirname(__DIR__) . '/database/pfpsharp.db';

try {
    $pdo = new PDO('sqlite:' . $dbPath);

    // Enable PDO error reporting
    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    // Return database results as associative arrays
    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

    // Enable foreign key constraints
    $pdo->exec('PRAGMA foreign_keys = ON');

} catch (PDOException $e) {

    error_log("Database connection error: " . $e->getMessage());

    http_response_code(500);
    exit("Unable to connect to the database.");

}
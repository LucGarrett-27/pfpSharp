<?php

// Start the PHP session (only once, even if this file is included twice).
if (session_status() !== PHP_SESSION_ACTIVE) {

    // Make the session cookie harder to steal:
    //  httponly  - JavaScript on the page cannot read it
    //  samesite  - other websites cannot send it along with their requests
    //  secure    - only sent over HTTPS (midn.cs.usna.edu uses HTTPS)
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);

    session_start();
}

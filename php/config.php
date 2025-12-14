<?php
// Basic configuration for the admin
return [
    // Initial admin credentials used only to bootstrap the credentials file on first run.
    // The admin user and password will be stored securely (hashed) in `data/admin.json` and
    // can be changed from the admin UI after first login.
    'initial_admin_user' => 'admin',
    'initial_admin_password' => 'FRx9$g7pTq2Lm#4H',
    // Path to SQLite DB file (writable by webserver)
    'db_path' => __DIR__ . '/../data/tracking.db',
];

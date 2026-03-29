<?php
// ============================================================
// config/db.php — Database Connection (MySQLi)
// XAMPP defaults: host=localhost, user=root, pass=''
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nexus_db');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('<h2 style="font-family:sans-serif;color:red;padding:2rem;">
        Database connection failed: ' . mysqli_connect_error() . '
        <br><small>Make sure XAMPP MySQL is running and nexus_db exists.</small>
    </h2>');
}

// Set charset to UTF-8
mysqli_set_charset($conn, 'utf8mb4');

<?php
session_start();

// Database configuration
define('DB_HOST', 'sql112.infinityfree.com');
define('DB_USER', 'if0_42059990 ');
define('DB_PASS', 'PMafXtN3tpcamKc');
define('DB_NAME', 'if0_42059990_noodle_store');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Manila');

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<?php

$session_path = __DIR__ . '/../storage/sessions';
if (!is_dir($session_path)) {
    mkdir($session_path, 0775, true);
}
session_save_path($session_path);
session_start();

$request_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$is_local = php_sapi_name() === 'cli'
    || str_starts_with($request_host, 'localhost')
    || str_starts_with($request_host, '127.0.0.1');

if ($is_local) {
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'staffless_noodle_store');
    define('BASE_URL', 'http://' . $request_host . '/webDesign_progect-main/midtermgroup');
} else {
    define('DB_HOST', 'sql112.infinityfree.com');
    define('DB_USER', 'if0_42059990');
    define('DB_PASS', 'PMafXtN3tpcamKc');
    define('DB_NAME', 'if0_42059990_noodle_store');
    define('BASE_URL', 'https://stafflessnoodle.infy.click');
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db_error = '';

if ($conn->connect_error) {

    $db_error = 'Database connection is temporarily unavailable.';
}

date_default_timezone_set('Asia/Taipei');

error_reporting(E_ALL);
ini_set('display_errors', $is_local ? '1' : '0');
?>

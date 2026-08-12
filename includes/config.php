<?php
/**
 * Global configuration.
 * Edit the DB credentials below to match your hosting account,
 * or (recommended) create includes/config.local.php with the same
 * define() calls — that file is git-ignored so real credentials
 * never get pushed to GitHub. See README.md.
 */

// ---- Database -----------------------------------------------------------
// If config.local.php exists, it is loaded FIRST so its values win —
// the defined() guards below then skip re-defining anything it already set.
$__local_config = __DIR__ . '/config.local.php';
if (file_exists($__local_config)) {
    require $__local_config;
}

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'hostingref_db');
if (!defined('DB_USER')) define('DB_USER', 'hostingref_user');
if (!defined('DB_PASS')) define('DB_PASS', 'CHANGE_ME');

// ---- App -----------------------------------------------------------------
if (!defined('SITE_URL')) {
    define('SITE_URL', 'https://your-domain.com'); // no trailing slash
}
define('DEFAULT_LANG', 'ar');
define('UPLOADS_DIR', __DIR__ . '/../uploads');
define('UPLOADS_URL', SITE_URL . '/uploads');

error_reporting(E_ALL);
ini_set('display_errors', '0'); // set to '1' locally while developing

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('Database connection failed. Please check includes/config.php credentials.');
}

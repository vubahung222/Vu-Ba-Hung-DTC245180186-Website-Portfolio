<?php
/**
 * Portfolio - Front Controller
 * Entry point: public/index.php
 * PHP 8.2 | UTF-8
 */

declare(strict_types=1);

// ── Encoding ──────────────────────────────────
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
header('Content-Type: text/html; charset=UTF-8');

// ── Error handling ────────────────────────────
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// ── Autoload ──────────────────────────────────
define('BASE_PATH', dirname(__DIR__));
define('SRC_PATH', BASE_PATH . '/src');
define('VIEWS_PATH', SRC_PATH . '/views');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('UPLOADS_URL', '/uploads');

// Simple PSR-4 autoloader
spl_autoload_register(function (string $class): void {
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

require_once SRC_PATH . '/bootstrap.php';

// ── Router ────────────────────────────────────
$router = new \Core\Router();
require_once SRC_PATH . '/routes.php';
$router->dispatch();

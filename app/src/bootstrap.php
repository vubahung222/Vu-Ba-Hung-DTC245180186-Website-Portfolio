<?php
/**
 * Bootstrap - Khởi tạo ứng dụng
 */

// ── Đường dẫn hệ thống ─────────────────────────
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (!defined('SRC_PATH')) {
    define('SRC_PATH', BASE_PATH . '/src');
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', BASE_PATH . '/public');
}
if (!defined('UPLOADS_PATH')) {
    define('UPLOADS_PATH', BASE_PATH . '/uploads');
}
if (!defined('UPLOADS_URL')) {
    define('UPLOADS_URL', '/uploads');
}

// ── Require Core classes ──────────────────────
require_once SRC_PATH . '/Core/Database.php';
require_once SRC_PATH . '/Core/Router.php';
require_once SRC_PATH . '/Core/Request.php';
require_once SRC_PATH . '/Core/Response.php';
require_once SRC_PATH . '/Core/Session.php';
require_once SRC_PATH . '/Core/Csrf.php';
require_once SRC_PATH . '/Core/Validator.php';
require_once SRC_PATH . '/Core/View.php';
require_once SRC_PATH . '/Core/Auth.php';
require_once SRC_PATH . '/helpers.php';

// ── Models ────────────────────────────────────
require_once SRC_PATH . '/models/Profile.php';
require_once SRC_PATH . '/models/Skill.php';
require_once SRC_PATH . '/models/Project.php';
require_once SRC_PATH . '/models/Education.php';
require_once SRC_PATH . '/models/Experience.php';
require_once SRC_PATH . '/models/ContactMessage.php';
require_once SRC_PATH . '/models/AdminUser.php';

// ── Controllers ───────────────────────────────
require_once SRC_PATH . '/controllers/PublicController.php';
require_once SRC_PATH . '/controllers/AdminController.php';
require_once SRC_PATH . '/controllers/AuthController.php';
require_once SRC_PATH . '/controllers/ApiController.php';

// ── Session start ─────────────────────────────
if (php_sapi_name() !== 'cli') {
    \Core\Session::start();
}

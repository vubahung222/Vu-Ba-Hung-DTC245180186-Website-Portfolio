<?php
/**
 * Bootstrap - Khởi tạo ứng dụng
 */

declare(strict_types=1);

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
\Core\Session::start();

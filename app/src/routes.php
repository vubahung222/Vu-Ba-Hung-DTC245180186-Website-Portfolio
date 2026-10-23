<?php
/**
 * Routes - Định nghĩa tất cả routes của ứng dụng
 */

declare(strict_types=1);

// ── Public routes ─────────────────────────────
$router->get('/', [\PublicController::class, 'home']);
$router->get('/du-an', [\PublicController::class, 'projects']);
$router->get('/du-an/{id}', [\PublicController::class, 'projectDetail']);
$router->get('/lien-he', [\PublicController::class, 'contact']);
$router->post('/lien-he/gui', [\PublicController::class, 'contactSubmit']);
$router->get('/health', [\PublicController::class, 'health']);

// ── Auth routes ───────────────────────────────
$router->get('/admin/login', [\AuthController::class, 'loginForm']);
$router->post('/admin/login', [\AuthController::class, 'login']);
$router->get('/admin/logout', [\AuthController::class, 'logout']);
$router->post('/admin/logout', [\AuthController::class, 'logout']);

// ── Admin routes (yêu cầu đăng nhập) ─────────
$router->get('/admin', [\AdminController::class, 'dashboard']);
$router->get('/admin/dashboard', [\AdminController::class, 'dashboard']);

// Profile
$router->get('/admin/profile', [\AdminController::class, 'profileEdit']);
$router->post('/admin/profile', [\AdminController::class, 'profileUpdate']);
$router->post('/admin/profile/avatar', [\AdminController::class, 'avatarUpload']);

// Skills
$router->get('/admin/skills', [\AdminController::class, 'skillsList']);
$router->post('/admin/skills/create', [\AdminController::class, 'skillCreate']);
$router->post('/admin/skills/{id}/update', [\AdminController::class, 'skillUpdate']);
$router->post('/admin/skills/{id}/delete', [\AdminController::class, 'skillDelete']);

// Projects
$router->get('/admin/projects', [\AdminController::class, 'projectsList']);
$router->get('/admin/projects/create', [\AdminController::class, 'projectCreate']);
$router->post('/admin/projects/store', [\AdminController::class, 'projectStore']);
$router->get('/admin/projects/{id}/edit', [\AdminController::class, 'projectEdit']);
$router->post('/admin/projects/{id}/update', [\AdminController::class, 'projectUpdate']);
$router->post('/admin/projects/{id}/delete', [\AdminController::class, 'projectDelete']);

// Education
$router->get('/admin/education', [\AdminController::class, 'educationList']);
$router->post('/admin/education/create', [\AdminController::class, 'educationCreate']);
$router->post('/admin/education/{id}/update', [\AdminController::class, 'educationUpdate']);
$router->post('/admin/education/{id}/delete', [\AdminController::class, 'educationDelete']);

// Experience
$router->get('/admin/experiences', [\AdminController::class, 'experienceList']);
$router->post('/admin/experiences/create', [\AdminController::class, 'experienceCreate']);
$router->post('/admin/experiences/{id}/update', [\AdminController::class, 'experienceUpdate']);
$router->post('/admin/experiences/{id}/delete', [\AdminController::class, 'experienceDelete']);

// Contact messages
$router->get('/admin/messages', [\AdminController::class, 'messagesList']);
$router->post('/admin/messages/{id}/read', [\AdminController::class, 'messageRead']);
$router->post('/admin/messages/{id}/delete', [\AdminController::class, 'messageDelete']);

// Change password
$router->get('/admin/password', [\AdminController::class, 'passwordForm']);
$router->post('/admin/password', [\AdminController::class, 'passwordChange']);

// 404 fallback
$router->setNotFound(function (): void {
    http_response_code(404);
    \Core\View::render('errors/404', ['title' => 'Trang không tìm thấy']);
});

<?php
/**
 * Script tạo tài khoản admin lần đầu
 * Chạy: docker compose exec php php /var/www/html/scripts/create-admin.php
 *
 * Hoặc với biến môi trường:
 * docker compose exec php php /var/www/html/scripts/create-admin.php --email=admin@example.com --password=MyPass123
 */

declare(strict_types=1);

// Load environment
require_once dirname(__DIR__) . '/src/bootstrap.php';

// Parse arguments
$email    = null;
$password = null;
$name     = 'Admin';

foreach ($argv ?? [] as $arg) {
    if (str_starts_with($arg, '--email='))    $email    = substr($arg, 8);
    if (str_starts_with($arg, '--password=')) $password = substr($arg, 11);
    if (str_starts_with($arg, '--name='))     $name     = substr($arg, 7);
}

// Fallback to env
if (!$email)    $email    = getenv('ADMIN_EMAIL')    ?: 'admin@portfolio.local';
if (!$password) $password = getenv('ADMIN_PASSWORD') ?: null;

if (!$password) {
    echo "❌ Chưa có mật khẩu. Dùng --password=YourPass hoặc set ADMIN_PASSWORD trong .env\n";
    exit(1);
}

if (strlen($password) < 8) {
    echo "❌ Mật khẩu phải có ít nhất 8 ký tự.\n";
    exit(1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "❌ Email không hợp lệ: {$email}\n";
    exit(1);
}

// Check if admin exists
if (AdminUser::exists()) {
    $existing = AdminUser::findByEmail($email);
    if ($existing) {
        echo "⚠️  Tài khoản {$email} đã tồn tại. Cập nhật mật khẩu...\n";
        AdminUser::updatePassword($existing['id'], $password);
        echo "✅ Đã cập nhật mật khẩu cho: {$email}\n";
    } else {
        echo "ℹ️  Đã có admin user. Thêm tài khoản mới...\n";
        AdminUser::create($email, $password, $name);
        echo "✅ Đã tạo admin: {$email}\n";
    }
} else {
    AdminUser::create($email, $password, $name);
    echo "✅ Đã tạo admin đầu tiên: {$email}\n";
}

echo "🔐 Đăng nhập tại: https://localhost/admin/login\n";

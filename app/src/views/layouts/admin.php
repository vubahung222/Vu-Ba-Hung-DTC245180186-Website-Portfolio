<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title ?? 'Admin') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body class="admin-body">
<?php if (isset($user)): ?>
<!-- ADMIN LAYOUT with sidebar -->
<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar" aria-label="Menu quản trị">
        <div class="sidebar-header">
            <a href="/admin/dashboard" class="sidebar-brand">
                <div>
                    <div class="sidebar-title">Admin Panel</div>
                    <div class="sidebar-subtitle text-xs text-muted"><?= e($user['name'] ?? 'Admin') ?></div>
                </div>
            </a>
        </div>

        <nav class="sidebar-nav" aria-label="Admin navigation">
            <?php $currentUri = \Core\Request::uri(); ?>
            <?php
            $navItems = [
                ['/admin/dashboard', 'Dashboard'],
                ['/admin/profile',   'Hồ sơ'],
                ['/admin/skills',    'Kỹ năng'],
                ['/admin/projects',  'Dự án'],
                ['/admin/education', 'Học vấn'],
                ['/admin/experiences','Kinh nghiệm'],
                ['/admin/messages',  'Tin nhắn'],
                ['/admin/password',  'Đổi mật khẩu'],
            ];
            foreach ($navItems as [$href, $label]):
                $isActive = str_starts_with($currentUri, $href);
            ?>
            <a href="<?= $href ?>" class="sidebar-link <?= $isActive ? 'active' : '' ?>">
                <span><?= $label ?></span>
                <?php if ($href === '/admin/messages' && ($unreadCount = \ContactMessage::countUnread()) > 0): ?>
                <span class="sidebar-badge"><?= $unreadCount ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-footer">
            <a href="/" class="sidebar-link text-sm">
                Xem website
            </a>
            <form action="/admin/logout" method="POST">
                <?= csrf_field() ?>
                <button type="submit" class="sidebar-link sidebar-logout w-full">
                    Đăng xuất
                </button>
            </form>
        </div>
    </aside>

    <!-- Mobile header -->
    <header class="admin-mobile-header">
        <button class="btn btn-ghost btn-sm" id="sidebarToggle" aria-label="Mở sidebar" aria-expanded="false" aria-controls="adminSidebar">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <span class="font-bold"><?= e($title ?? 'Admin') ?></span>
        <span></span>
    </header>

    <!-- Overlay for mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main content -->
    <main class="admin-main" id="adminMain">
        <div class="admin-content">
            <?= $content ?>
        </div>
    </main>
</div>
<?php else: ?>
<!-- Login page (no sidebar) -->
<main class="admin-login-page">
    <?= $content ?>
</main>
<?php endif; ?>

<script src="/js/admin.js"></script>
</body>
</html>

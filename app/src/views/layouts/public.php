<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($profile['bio_short'] ?? 'Portfolio cá nhân') ?>">
    <title><?= e($title ?? 'Portfolio') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/public.css">
</head>
<body>
<a href="#main-content" class="skip-link">Bỏ qua navigation</a>

<!-- HEADER -->
<header class="site-header" id="top" role="banner">
    <div class="container">
        <nav class="header-nav" aria-label="Navigation chính">
            <a href="/" class="nav-brand" aria-label="Trang chủ">
                <span class="brand-icon">👨‍💻</span>
                <span class="brand-name"><?= e($profile['full_name'] ?? '[HỌ TÊN]') ?></span>
            </a>

            <button class="nav-toggle" id="navToggle" aria-expanded="false" aria-controls="navMenu" aria-label="Mở menu">
                <span class="hamburger"></span>
            </button>

            <ul class="nav-links" id="navMenu" role="list">
                <li><a href="/#about" class="nav-link">Giới thiệu</a></li>
                <li><a href="/#skills" class="nav-link">Kỹ năng</a></li>
                <li><a href="/#projects" class="nav-link">Dự án</a></li>
                <li><a href="/#timeline" class="nav-link">Học vấn</a></li>
                <li><a href="/lien-he" class="nav-link">Liên hệ</a></li>
            </ul>
        </nav>
    </div>
</header>

<!-- MAIN CONTENT -->
<main id="main-content" tabindex="-1">
    <?= $content ?>
</main>

<!-- FOOTER -->
<footer class="site-footer" role="contentinfo">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-left">
                <span class="brand-icon">👨‍💻</span>
                <strong><?= e($profile['full_name'] ?? '[HỌ TÊN]') ?></strong>
                <span class="text-muted"> — <?= e($profile['title'] ?? 'Portfolio') ?></span>
            </div>
            <div class="footer-links">
                <?php if (!empty($profile['github_url']) && $profile['github_url'] !== '[GITHUB_URL]'): ?>
                <a href="<?= e($profile['github_url']) ?>" target="_blank" rel="noopener" class="footer-link" aria-label="GitHub">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.17 6.839 9.49.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.604-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.464-1.11-1.464-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0112 6.836c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.579.688.481C19.138 20.167 22 16.418 22 12c0-5.523-4.477-10-10-10z"/></svg>
                </a>
                <?php endif; ?>
                <?php if (!empty($profile['email'])): ?>
                <a href="mailto:<?= e($profile['email']) ?>" class="footer-link" aria-label="Email">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <p class="footer-copy">
            &copy; <?= date('Y') ?> <?= e($profile['full_name'] ?? '[HỌ TÊN]') ?>.
            Được xây dựng bằng PHP, MySQL, Docker &amp; Nginx.
            &bull; <a href="/admin/login" class="text-muted" style="text-decoration:none;" title="Đăng nhập quản trị viên">🔐 Quản trị</a>
        </p>
    </div>
</footer>

<script src="/js/main.js"></script>
</body>
</html>

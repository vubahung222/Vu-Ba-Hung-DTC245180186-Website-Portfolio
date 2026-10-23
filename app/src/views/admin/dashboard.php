<div class="page-header">
    <div>
        <h1 class="page-title">📊 Dashboard</h1>
        <p class="page-subtitle">Tổng quan hệ thống portfolio</p>
    </div>
    <a href="/" target="_blank" class="btn btn-outline btn-sm">🌐 Xem website</a>
</div>

<?php if ($notice = \Core\Session::getFlash('notice')): ?>
<div class="alert alert-success" role="alert"><?= e($notice) ?></div>
<?php endif; ?>

<!-- Stats (Clickable) -->
<div class="stats-grid">
    <a href="/admin/projects" class="stat-card" title="Xem danh sách dự án">
        <div class="stat-icon">🚀</div>
        <div>
            <div class="stat-value"><?= $stats['projects'] ?></div>
            <div class="stat-label">Dự án</div>
        </div>
    </a>
    <a href="/admin/skills" class="stat-card" title="Quản lý kỹ năng">
        <div class="stat-icon">⚡</div>
        <div>
            <div class="stat-value"><?= $stats['skills'] ?></div>
            <div class="stat-label">Kỹ năng</div>
        </div>
    </a>
    <a href="/admin/education" class="stat-card" title="Quản lý học vấn">
        <div class="stat-icon">🎓</div>
        <div>
            <div class="stat-value"><?= $stats['education'] ?></div>
            <div class="stat-label">Học vấn</div>
        </div>
    </a>
    <a href="/admin/experiences" class="stat-card" title="Quản lý kinh nghiệm">
        <div class="stat-icon">💼</div>
        <div>
            <div class="stat-value"><?= $stats['experiences'] ?></div>
            <div class="stat-label">Kinh nghiệm</div>
        </div>
    </a>
    <a href="/admin/messages" class="stat-card" title="Xem tin nhắn liên hệ">
        <div class="stat-icon <?= $stats['unread'] > 0 ? 'red' : 'green' ?>">💬</div>
        <div>
            <div class="stat-value"><?= $stats['messages'] ?></div>
            <div class="stat-label">Tin nhắn <?php if ($stats['unread'] > 0): ?><span class="sidebar-badge"><?= $stats['unread'] ?> chưa đọc</span><?php endif; ?></div>
        </div>
    </a>
    <a href="/health" target="_blank" class="stat-card" title="Kiểm tra trạng thái hệ thống">
        <div class="stat-icon green">✅</div>
        <div>
            <div class="stat-value" style="font-size:1.25rem; color:var(--color-success)">Online</div>
            <div class="stat-label">Hệ thống</div>
        </div>
    </a>
</div>

<!-- Quick actions -->
<div class="form-panel">
    <div class="form-panel-title">⚡ Thao tác nhanh</div>
    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: var(--space-3);">
        <a href="/admin/profile"   class="btn btn-outline">👤 Cập nhật hồ sơ</a>
        <a href="/admin/projects/create" class="btn btn-outline">➕ Thêm dự án</a>
        <a href="/admin/skills"    class="btn btn-outline">⚡ Quản lý kỹ năng</a>
        <a href="/admin/messages"  class="btn btn-outline">💬 Xem tin nhắn <?= $stats['unread'] > 0 ? "({$stats['unread']})" : '' ?></a>
        <a href="/admin/education" class="btn btn-outline">🎓 Học vấn</a>
        <a href="/admin/experiences" class="btn btn-outline">💼 Kinh nghiệm</a>
    </div>
</div>

<!-- System info -->
<div class="form-panel">
    <div class="form-panel-title">🐳 Thông tin hệ thống</div>
    <div class="table-responsive">
    <table class="table">
        <tr><th>PHP Version</th><td><?= phpversion() ?></td></tr>
        <tr><th>Server Time</th><td><?= date('d/m/Y H:i:s') ?></td></tr>
        <tr><th>Database</th><td>
            <?php
            try { \Core\Database::fetchOne("SELECT 1"); echo '<span class="badge badge-success">✅ Kết nối OK</span>'; }
            catch (\Exception $e) { echo '<span class="badge" style="background:#FEF2F2;color:#991B1B">❌ ' . e($e->getMessage()) . '</span>'; }
            ?>
        </td></tr>
        <tr><th>Upload Dir</th><td><?= is_writable(UPLOADS_PATH) ? '<span class="badge badge-success">✅ Có thể ghi</span>' : '<span class="badge" style="background:#FEF2F2;color:#991B1B">❌ Không thể ghi</span>' ?></td></tr>
        <tr><th>Environment</th><td><?= e($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'unknown') ?></td></tr>
    </table>
    </div>
</div>

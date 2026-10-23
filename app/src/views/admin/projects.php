<div class="page-header">
    <div>
        <h1 class="page-title">Dự án</h1>
        <p class="page-subtitle">Quản lý danh sách dự án</p>
    </div>
    <a href="/admin/projects/create" class="btn btn-primary">Thêm dự án</a>
</div>

<?php $msgSuccess = $success ?? \Core\Session::getFlash('success'); if (!empty($msgSuccess)): ?><div class="alert alert-success"><?= e($msgSuccess) ?></div><?php endif; ?>
<?php $msgError = $error ?? \Core\Session::getFlash('error'); if (!empty($msgError)): ?><div class="alert alert-error"><?= e($msgError) ?></div><?php endif; ?>

<div class="table-panel">
    <div class="table-panel-header">
        <span class="table-panel-title">Danh sách dự án (<?= count($projects) ?>)</span>
    </div>
    <?php if (empty($projects)): ?>
    <div class="empty-state"><p>Chưa có dự án nào. <a href="/admin/projects/create" class="text-primary">Thêm dự án đầu tiên</a></p></div>
    <?php else: ?>
    <div class="table-responsive">
    <table class="table">
        <thead>
            <tr><th>#</th><th>Tiêu đề</th><th>Công nghệ</th><th>Nổi bật</th><th>Ngày tạo</th><th>Thao tác</th></tr>
        </thead>
        <tbody>
        <?php foreach ($projects as $proj): ?>
        <tr>
            <td><?= $proj['id'] ?></td>
            <td>
                <div class="font-bold"><?= e($proj['title']) ?></div>
                <div class="text-sm text-muted"><?= e(mb_substr($proj['description'], 0, 60, 'UTF-8')) ?>...</div>
            </td>
            <td>
                <?php foreach (array_slice(Project::getTechStack($proj), 0, 3) as $t): ?>
                <span class="tech-tag" style="margin:2px"><?= e($t) ?></span>
                <?php endforeach; ?>
            </td>
            <td><?= $proj['is_featured'] ? '<span class="badge badge-success">Nổi bật</span>' : '<span class="badge badge-gray">Thường</span>' ?></td>
            <td class="text-sm text-muted"><?= date('d/m/Y', strtotime($proj['created_at'])) ?></td>
            <td>
                <div style="display:flex;gap:var(--space-2);">
                    <a href="/admin/projects/<?= $proj['id'] ?>/edit" class="btn btn-ghost btn-sm" aria-label="Sửa">Sửa</a>
                    <form action="/admin/projects/<?= $proj['id'] ?>/delete" method="POST" id="del-proj-<?= $proj['id'] ?>">
                        <?= csrf_field() ?>
                        <button type="button" class="btn btn-danger btn-sm"
                                data-confirm="Xóa dự án '<?= e($proj['title']) ?>'?"
                                aria-label="Xóa">Xóa</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<!-- Confirm modal -->
<div class="modal-overlay" id="confirmModal" role="dialog" aria-modal="true">
    <div class="modal confirm-modal">
        <h3>Xác nhận xóa</h3>
        <p id="confirmMessage" class="text-muted mt-4"></p>
        <div class="confirm-actions">
            <button class="btn btn-ghost" id="confirmNo">Hủy</button>
            <button class="btn btn-danger" id="confirmYes">Xóa</button>
        </div>
    </div>
</div>

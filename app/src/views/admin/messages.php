<div class="page-header">
    <div>
        <h1 class="page-title">Tin nhắn liên hệ</h1>
        <p class="page-subtitle">Tin nhắn từ form liên hệ trên website</p>
    </div>
</div>

<?php $msgSuccess = $success ?? \Core\Session::getFlash('success'); if (!empty($msgSuccess)): ?><div class="alert alert-success"><?= e($msgSuccess) ?></div><?php endif; ?>
<?php $msgError = $error ?? \Core\Session::getFlash('error'); if (!empty($msgError)): ?><div class="alert alert-error"><?= e($msgError) ?></div><?php endif; ?>

<div class="table-panel">
    <div class="table-panel-header">
        <span class="table-panel-title">
            Tin nhắn (<?= count($messages) ?>)
            <?php $unread = array_filter($messages, fn($m) => !$m['is_read']); ?>
            <?php if (count($unread) > 0): ?>
            <span class="sidebar-badge"><?= count($unread) ?> chưa đọc</span>
            <?php endif; ?>
        </span>
    </div>

    <?php if (empty($messages)): ?>
    <div class="empty-state"><p>Chưa có tin nhắn nào.</p></div>
    <?php else: ?>
    <div class="table-responsive">
    <table class="table">
        <thead>
            <tr><th>#</th><th>Người gửi</th><th>Tiêu đề</th><th>Ngày gửi</th><th>Trạng thái</th><th>Thao tác</th></tr>
        </thead>
        <tbody>
        <?php foreach ($messages as $msg): ?>
        <tr style="<?= !$msg['is_read'] ? 'background:var(--color-primary-light);' : '' ?>">
            <td><?= $msg['id'] ?></td>
            <td>
                <div class="font-bold <?= !$msg['is_read'] ? '' : 'text-muted' ?>"><?= e($msg['sender_name']) ?></div>
                <div class="text-xs text-muted"><?= e($msg['sender_email']) ?></div>
            </td>
            <td class="<?= !$msg['is_read'] ? 'font-bold' : '' ?>"><?= e($msg['subject']) ?></td>
            <td class="text-sm text-muted"><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></td>
            <td>
                <?php if ($msg['is_read']): ?>
                <span class="badge badge-gray">Đã đọc</span>
                <?php else: ?>
                <span class="badge badge-primary">Chưa đọc</span>
                <?php endif; ?>
            </td>
            <td>
                <div style="display:flex;gap:var(--space-2);">
                    <button type="button" class="btn btn-outline btn-sm"
                            data-msg-id="<?= $msg['id'] ?>"
                            data-name="<?= e($msg['sender_name']) ?>"
                            data-email="<?= e($msg['sender_email']) ?>"
                            data-subject="<?= e($msg['subject']) ?>"
                            data-message="<?= e($msg['message']) ?>"
                            data-date="<?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?>">
                        Xem
                    </button>
                    <form action="/admin/messages/<?= $msg['id'] ?>/delete" method="POST">
                        <?= csrf_field() ?>
                        <button type="button" class="btn btn-danger btn-sm"
                                data-confirm="Xóa tin nhắn từ '<?= e($msg['sender_name']) ?>'?">Xóa</button>
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

<!-- Message View Modal -->
<div class="modal-overlay" id="msgModal" role="dialog" aria-modal="true" aria-labelledby="msgModalTitle">
    <div class="modal" style="max-width:560px">
        <button class="modal-close" id="msgModalClose" aria-label="Đóng">✕</button>
        <h3 id="msgModalTitle" style="margin-bottom:var(--space-5)">Nội dung tin nhắn</h3>
        <div style="display:grid;grid-template-columns:auto 1fr;gap:var(--space-2) var(--space-4);margin-bottom:var(--space-5);font-size:.9rem;">
            <span class="text-muted font-bold">Người gửi:</span><span id="msgModal-name" class="font-bold"></span>
            <span class="text-muted font-bold">Email:</span><a id="msgModal-email" href="#" class="text-primary"></a>
            <span class="text-muted font-bold">Tiêu đề:</span><span id="msgModal-subject" class="font-bold"></span>
            <span class="text-muted font-bold">Ngày gửi:</span><span id="msgModal-date" class="text-muted"></span>
        </div>
        <div class="message-preview" id="msgModal-message"></div>
        <div style="display:flex;gap:var(--space-3);justify-content:flex-end;margin-top:var(--space-5);">
            <button type="button" class="btn btn-ghost" id="msgModalClose2">Đóng</button>
            <form id="msgReadForm" method="POST">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary">Đánh dấu đã đọc</button>
            </form>
        </div>
    </div>
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

<script>
document.getElementById('msgModalClose2')?.addEventListener('click', () => {
    document.getElementById('msgModal')?.classList.remove('active');
});
// Update email href when message opens
document.querySelectorAll('[data-msg-id]').forEach(btn => {
    btn.addEventListener('click', () => {
        const emailEl = document.getElementById('msgModal-email');
        if (emailEl) emailEl.href = 'mailto:' + btn.dataset.email;
    });
});
</script>

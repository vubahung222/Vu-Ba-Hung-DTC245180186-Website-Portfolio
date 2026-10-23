<div class="page-header">
    <div>
        <h1 class="page-title">Kinh nghiệm</h1>
        <p class="page-subtitle">Quản lý kinh nghiệm làm việc</p>
    </div>
</div>

<?php $msgSuccess = $success ?? \Core\Session::getFlash('success'); if (!empty($msgSuccess)): ?><div class="alert alert-success"><?= e($msgSuccess) ?></div><?php endif; ?>
<?php $msgError = $error ?? \Core\Session::getFlash('error'); if (!empty($msgError)): ?><div class="alert alert-error"><?= e($msgError) ?></div><?php endif; ?>

<div class="form-panel">
    <div class="form-panel-title">Thêm kinh nghiệm</div>
    <form action="/admin/experiences/create" method="POST" data-loading="true">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group">
                <label for="company" class="form-label">Công ty <span class="required">*</span></label>
                <input type="text" id="company" name="company" class="form-control" required placeholder="Tên công ty...">
            </div>
            <div class="form-group">
                <label for="position" class="form-label">Vị trí <span class="required">*</span></label>
                <input type="text" id="position" name="position" class="form-control" required placeholder="Web Developer...">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="start_date" class="form-label">Ngày bắt đầu <span class="required">*</span></label>
                <input type="date" id="start_date" name="start_date" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="end_date" class="form-label">Ngày kết thúc (để trống = hiện tại)</label>
                <input type="date" id="end_date" name="end_date" class="form-control">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="exp_loc" class="form-label">Địa điểm</label>
                <input type="text" id="exp_loc" name="location" class="form-control" placeholder="TP. Hồ Chí Minh">
            </div>
        </div>
        <div class="form-group">
            <label for="exp_desc" class="form-label">Mô tả công việc</label>
            <textarea id="exp_desc" name="description" class="form-control" rows="3" placeholder="Mô tả nhiệm vụ, thành tích..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Thêm</button>
    </form>
</div>

<div class="table-panel">
    <div class="table-panel-header"><span class="table-panel-title">Danh sách kinh nghiệm (<?= count($experiences) ?>)</span></div>
    <?php if (empty($experiences)): ?>
    <div class="empty-state"><p>Chưa có kinh nghiệm làm việc.</p></div>
    <?php else: ?>
    <div class="table-responsive">
    <table class="table">
        <thead><tr><th>#</th><th>Công ty</th><th>Vị trí</th><th>Thời gian</th><th>Địa điểm</th><th>Thao tác</th></tr></thead>
        <tbody>
        <?php foreach ($experiences as $exp): ?>
        <tr>
            <td><?= $exp['id'] ?></td>
            <td class="font-bold"><?= e($exp['company']) ?></td>
            <td><?= e($exp['position']) ?></td>
            <td class="text-sm"><?= Experience::formatPeriod($exp) ?></td>
            <td class="text-sm"><?= e($exp['location'] ?? '—') ?></td>
            <td>
                <div style="display:flex;gap:var(--space-2);">
                    <button type="button" class="btn btn-ghost btn-sm"
                            onclick="openEditExp(<?= htmlspecialchars(json_encode($exp, JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)">Sửa</button>
                    <form action="/admin/experiences/<?= $exp['id'] ?>/delete" method="POST">
                        <?= csrf_field() ?>
                        <button type="button" class="btn btn-danger btn-sm"
                                data-confirm="Xóa kinh nghiệm tại '<?= e($exp['company']) ?>'?">Xóa</button>
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

<div class="modal-overlay" id="editExpModal" role="dialog" aria-modal="true">
    <div class="modal" style="max-width:600px">
        <button class="modal-close" onclick="document.getElementById('editExpModal').classList.remove('active')" aria-label="Đóng">✕</button>
        <h3 style="margin-bottom:var(--space-5)">Chỉnh sửa kinh nghiệm</h3>
        <form id="editExpForm" method="POST" data-loading="true">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Công ty</label><input type="text" id="ee_company" name="company" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Vị trí</label><input type="text" id="ee_pos" name="position" class="form-control" required></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Ngày bắt đầu</label><input type="date" id="ee_sdate" name="start_date" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Ngày kết thúc</label><input type="date" id="ee_edate" name="end_date" class="form-control"></div>
            </div>
            <div class="form-group"><label class="form-label">Địa điểm</label><input type="text" id="ee_eloc" name="location" class="form-control"></div>
            <div class="form-group"><label class="form-label">Mô tả</label><textarea id="ee_edesc" name="description" class="form-control" rows="3"></textarea></div>
            <div style="display:flex;gap:var(--space-3);justify-content:flex-end;">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('editExpModal').classList.remove('active')">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>
</div>

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
function openEditExp(exp) {
    document.getElementById('ee_company').value = exp.company;
    document.getElementById('ee_pos').value     = exp.position;
    document.getElementById('ee_sdate').value   = exp.start_date;
    document.getElementById('ee_edate').value   = exp.end_date || '';
    document.getElementById('ee_eloc').value    = exp.location || '';
    document.getElementById('ee_edesc').value   = exp.description || '';
    document.getElementById('editExpForm').action = '/admin/experiences/' + exp.id + '/update';
    document.getElementById('editExpModal').classList.add('active');
}
</script>

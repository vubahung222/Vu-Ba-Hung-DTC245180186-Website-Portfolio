<div class="page-header">
    <div>
        <h1 class="page-title">🎓 Học vấn</h1>
        <p class="page-subtitle">Quản lý thông tin học vấn</p>
    </div>
</div>

<?php $msgSuccess = $success ?? \Core\Session::getFlash('success'); if (!empty($msgSuccess)): ?><div class="alert alert-success"><?= e($msgSuccess) ?></div><?php endif; ?>
<?php $msgError = $error ?? \Core\Session::getFlash('error'); if (!empty($msgError)): ?><div class="alert alert-error"><?= e($msgError) ?></div><?php endif; ?>

<!-- Add Form -->
<div class="form-panel">
    <div class="form-panel-title">➕ Thêm mục học vấn</div>
    <form action="/admin/education/create" method="POST" data-loading="true">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group">
                <label for="institution" class="form-label">Trường/Tổ chức <span class="required">*</span></label>
                <input type="text" id="institution" name="institution" class="form-control" required placeholder="Tên trường...">
            </div>
            <div class="form-group">
                <label for="degree" class="form-label">Bằng cấp <span class="required">*</span></label>
                <input type="text" id="degree" name="degree" class="form-control" required placeholder="Cử nhân, Kỹ sư...">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="field" class="form-label">Chuyên ngành</label>
                <input type="text" id="field" name="field_of_study" class="form-control" placeholder="Công nghệ Thông tin...">
            </div>
            <div class="form-group">
                <label for="gpa" class="form-label">GPA</label>
                <input type="text" id="gpa" name="gpa" class="form-control" placeholder="3.5/4.0">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="start_year" class="form-label">Năm bắt đầu <span class="required">*</span></label>
                <input type="number" id="start_year" name="start_year" class="form-control" required
                       min="1990" max="<?= date('Y') + 1 ?>" placeholder="<?= date('Y') - 4 ?>">
            </div>
            <div class="form-group">
                <label for="end_year" class="form-label">Năm kết thúc (để trống = đang học)</label>
                <input type="number" id="end_year" name="end_year" class="form-control"
                       min="1990" max="<?= date('Y') + 10 ?>" placeholder="<?= date('Y') ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="edu_desc" class="form-label">Mô tả</label>
            <textarea id="edu_desc" name="description" class="form-control" rows="3" placeholder="Mô tả khóa học, thành tích..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">➕ Thêm</button>
    </form>
</div>

<!-- Table -->
<div class="table-panel">
    <div class="table-panel-header"><span class="table-panel-title">📋 Danh sách học vấn (<?= count($education) ?>)</span></div>
    <?php if (empty($education)): ?>
    <div class="empty-state"><div class="empty-state-icon">🎓</div><p>Chưa có thông tin học vấn.</p></div>
    <?php else: ?>
    <div class="table-responsive">
    <table class="table">
        <thead><tr><th>#</th><th>Trường</th><th>Bằng cấp</th><th>Chuyên ngành</th><th>Thời gian</th><th>GPA</th><th>Thao tác</th></tr></thead>
        <tbody>
        <?php foreach ($education as $edu): ?>
        <tr>
            <td><?= $edu['id'] ?></td>
            <td class="font-bold"><?= e($edu['institution']) ?></td>
            <td><?= e($edu['degree']) ?></td>
            <td><?= e($edu['field_of_study'] ?? '—') ?></td>
            <td class="text-sm"><?= e($edu['start_year']) ?> – <?= $edu['end_year'] ? e($edu['end_year']) : 'Hiện tại' ?></td>
            <td><?= $edu['gpa'] ? e($edu['gpa']) : '—' ?></td>
            <td>
                <div style="display:flex;gap:var(--space-2);">
                    <button type="button" class="btn btn-ghost btn-sm"
                            onclick="openEditEdu(<?= htmlspecialchars(json_encode($edu, JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)">✏️</button>
                    <form action="/admin/education/<?= $edu['id'] ?>/delete" method="POST">
                        <?= csrf_field() ?>
                        <button type="button" class="btn btn-danger btn-sm"
                                data-confirm="Xóa mục học vấn '<?= e($edu['institution']) ?>'?">🗑️</button>
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

<!-- Edit Modal -->
<div class="modal-overlay" id="editEduModal" role="dialog" aria-modal="true" aria-labelledby="editEduTitle">
    <div class="modal" style="max-width:600px">
        <button class="modal-close" onclick="document.getElementById('editEduModal').classList.remove('active')" aria-label="Đóng">✕</button>
        <h3 id="editEduTitle" style="margin-bottom:var(--space-5)">✏️ Chỉnh sửa học vấn</h3>
        <form id="editEduForm" method="POST" data-loading="true">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Trường</label><input type="text" id="ee_inst" name="institution" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Bằng cấp</label><input type="text" id="ee_degree" name="degree" class="form-control" required></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Chuyên ngành</label><input type="text" id="ee_field" name="field_of_study" class="form-control"></div>
                <div class="form-group"><label class="form-label">GPA</label><input type="text" id="ee_gpa" name="gpa" class="form-control"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Năm bắt đầu</label><input type="number" id="ee_start" name="start_year" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Năm kết thúc</label><input type="number" id="ee_end" name="end_year" class="form-control"></div>
            </div>
            <div class="form-group"><label class="form-label">Mô tả</label><textarea id="ee_desc" name="description" class="form-control" rows="3"></textarea></div>
            <div style="display:flex;gap:var(--space-3);justify-content:flex-end;">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('editEduModal').classList.remove('active')">Hủy</button>
                <button type="submit" class="btn btn-primary">💾 Lưu</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal-overlay" id="confirmModal" role="dialog" aria-modal="true">
    <div class="modal confirm-modal">
        <h3>⚠️ Xác nhận xóa</h3>
        <p id="confirmMessage" class="text-muted mt-4"></p>
        <div class="confirm-actions">
            <button class="btn btn-ghost" id="confirmNo">Hủy</button>
            <button class="btn btn-danger" id="confirmYes">Xóa</button>
        </div>
    </div>
</div>

<script>
function openEditEdu(edu) {
    document.getElementById('ee_inst').value   = edu.institution;
    document.getElementById('ee_degree').value = edu.degree;
    document.getElementById('ee_field').value  = edu.field_of_study || '';
    document.getElementById('ee_gpa').value    = edu.gpa || '';
    document.getElementById('ee_start').value  = edu.start_year;
    document.getElementById('ee_end').value    = edu.end_year || '';
    document.getElementById('ee_desc').value   = edu.description || '';
    document.getElementById('editEduForm').action = '/admin/education/' + edu.id + '/update';
    document.getElementById('editEduModal').classList.add('active');
}
</script>

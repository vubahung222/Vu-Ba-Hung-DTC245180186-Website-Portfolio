<div class="page-header">
    <div>
        <h1 class="page-title">⚡ Kỹ năng</h1>
        <p class="page-subtitle">Quản lý danh sách kỹ năng và công nghệ</p>
    </div>
</div>

<?php $msgSuccess = $success ?? \Core\Session::getFlash('success'); if (!empty($msgSuccess)): ?><div class="alert alert-success"><?= e($msgSuccess) ?></div><?php endif; ?>
<?php $msgError = $error ?? \Core\Session::getFlash('error'); if (!empty($msgError)): ?><div class="alert alert-error"><?= e($msgError) ?></div><?php endif; ?>

<!-- Add Skill Form -->
<div class="form-panel">
    <div class="form-panel-title">➕ Thêm kỹ năng mới</div>
    <form action="/admin/skills/create" method="POST" data-loading="true">
        <?= csrf_field() ?>
        <div class="form-row" style="grid-template-columns: 2fr 1fr 1fr auto; align-items: end;">
            <div class="form-group" style="margin-bottom:0">
                <label for="skill_name" class="form-label">Tên kỹ năng <span class="required">*</span></label>
                <input type="text" id="skill_name" name="name" class="form-control"
                       required placeholder="Ví dụ: PHP, Docker, MySQL...">
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label for="skill_cat" class="form-label">Danh mục</label>
                <select id="skill_cat" name="category" class="form-control">
                    <option value="frontend">Frontend</option>
                    <option value="backend">Backend</option>
                    <option value="devops">DevOps</option>
                    <option value="tool">Công cụ</option>
                    <option value="language">Ngôn ngữ</option>
                    <option value="other">Khác</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label for="skill_level" class="form-label">
                    Thành thạo: <span id="skill_level_display">70%</span>
                </label>
                <input type="range" id="skill_level" name="level"
                       min="0" max="100" value="70">
            </div>
            <div style="padding-bottom: 0; display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn-primary">Thêm</button>
            </div>
        </div>
    </form>
</div>

<!-- Skills Table -->
<div class="table-panel">
    <div class="table-panel-header">
        <span class="table-panel-title">📋 Danh sách kỹ năng (<?= count($skills) ?>)</span>
    </div>
    <?php if (empty($skills)): ?>
    <div class="empty-state"><div class="empty-state-icon">⚡</div><p>Chưa có kỹ năng nào.</p></div>
    <?php else: ?>
    <div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Tên kỹ năng</th>
                <th>Danh mục</th>
                <th>Thành thạo</th>
                <th>Thứ tự</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($skills as $skill): ?>
        <tr>
            <td><?= $skill['id'] ?></td>
            <td class="font-bold"><?= e($skill['name']) ?></td>
            <td><span class="badge badge-primary"><?= e($skill['category']) ?></span></td>
            <td>
                <div style="display:flex;align-items:center;gap:var(--space-2);">
                    <div class="skill-bar" style="width:120px">
                        <div class="skill-bar-fill" style="width:<?= (int)$skill['level'] ?>%"></div>
                    </div>
                    <span class="text-sm"><?= (int)$skill['level'] ?>%</span>
                </div>
            </td>
            <td><?= (int)$skill['sort_order'] ?></td>
            <td>
                <div style="display:flex;gap:var(--space-2);">
                    <!-- Edit modal trigger -->
                    <button type="button" class="btn btn-ghost btn-sm"
                            onclick="openEditSkill(<?= htmlspecialchars(json_encode($skill), ENT_QUOTES) ?>)"
                            aria-label="Chỉnh sửa <?= e($skill['name']) ?>">✏️</button>
                    <!-- Delete -->
                    <form action="/admin/skills/<?= $skill['id'] ?>/delete" method="POST" id="del-skill-<?= $skill['id'] ?>">
                        <?= csrf_field() ?>
                        <button type="button" class="btn btn-danger btn-sm"
                                data-confirm="Xóa kỹ năng '<?= e($skill['name']) ?>'?"
                                aria-label="Xóa <?= e($skill['name']) ?>">🗑️</button>
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
<div class="modal-overlay" id="editSkillModal" role="dialog" aria-modal="true" aria-labelledby="editSkillTitle">
    <div class="modal">
        <button class="modal-close" onclick="document.getElementById('editSkillModal').classList.remove('active')" aria-label="Đóng">✕</button>
        <h3 id="editSkillTitle" style="margin-bottom:var(--space-5)">✏️ Chỉnh sửa kỹ năng</h3>
        <form id="editSkillForm" method="POST" data-loading="true">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="edit_name" class="form-label">Tên kỹ năng</label>
                <input type="text" id="edit_name" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="edit_cat" class="form-label">Danh mục</label>
                <select id="edit_cat" name="category" class="form-control">
                    <option value="frontend">Frontend</option>
                    <option value="backend">Backend</option>
                    <option value="devops">DevOps</option>
                    <option value="tool">Công cụ</option>
                    <option value="language">Ngôn ngữ</option>
                    <option value="other">Khác</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_level" class="form-label">Thành thạo: <span id="edit_level_display">70%</span></label>
                <input type="range" id="edit_level" name="level" min="0" max="100" value="70">
            </div>
            <div class="form-group">
                <label for="edit_sort" class="form-label">Thứ tự hiển thị</label>
                <input type="number" id="edit_sort" name="sort_order" class="form-control" min="0" max="999">
            </div>
            <div style="display:flex;gap:var(--space-3);justify-content:flex-end;">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('editSkillModal').classList.remove('active')">Hủy</button>
                <button type="submit" class="btn btn-primary">💾 Lưu</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm delete modal -->
<div class="modal-overlay" id="confirmModal" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
    <div class="modal confirm-modal">
        <h3 id="confirmTitle">⚠️ Xác nhận xóa</h3>
        <p id="confirmMessage" class="text-muted mt-4"></p>
        <div class="confirm-actions">
            <button class="btn btn-ghost" id="confirmNo">Hủy</button>
            <button class="btn btn-danger" id="confirmYes">Xóa</button>
        </div>
    </div>
</div>

<script>
function openEditSkill(skill) {
    document.getElementById('edit_name').value  = skill.name;
    document.getElementById('edit_cat').value   = skill.category;
    document.getElementById('edit_level').value = skill.level;
    document.getElementById('edit_level_display').textContent = skill.level + '%';
    document.getElementById('edit_sort').value  = skill.sort_order;
    document.getElementById('editSkillForm').action = '/admin/skills/' + skill.id + '/update';
    document.getElementById('editSkillModal').classList.add('active');
}
// Range update
document.getElementById('edit_level')?.addEventListener('input', function() {
    document.getElementById('edit_level_display').textContent = this.value + '%';
});
document.getElementById('skill_level')?.addEventListener('input', function() {
    document.getElementById('skill_level_display').textContent = this.value + '%';
});
</script>

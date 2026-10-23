<?php
$isEdit  = !empty($project);
$pageTitle = $isEdit ? 'Chỉnh sửa dự án' : 'Thêm dự án mới';
?>
<div class="page-header">
    <div>
        <h1 class="page-title">🚀 <?= $pageTitle ?></h1>
    </div>
    <a href="/admin/projects" class="btn btn-ghost">← Quay lại</a>
</div>

<form action="<?= e($action) ?>" method="POST" enctype="multipart/form-data" data-loading="true">
    <?= csrf_field() ?>

    <div class="form-panel">
        <div class="form-panel-title">📝 Thông tin dự án</div>

        <div class="form-group">
            <label for="title" class="form-label">Tiêu đề <span class="required">*</span></label>
            <input type="text" id="title" name="title" class="form-control"
                   required value="<?= e($project['title'] ?? '') ?>"
                   placeholder="Tên dự án...">
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Mô tả <span class="required">*</span></label>
            <textarea id="description" name="description" class="form-control" rows="5" required
                      placeholder="Mô tả dự án, chức năng, mục tiêu..."><?= e($project['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="tech_stack" class="form-label">Công nghệ (cách nhau bằng dấu phẩy)</label>
            <input type="text" id="tech_stack" name="tech_stack" class="form-control"
                   value="<?= e($project['tech_stack'] ?? '') ?>"
                   placeholder="PHP, MySQL, Docker, Nginx">
            <span class="form-hint">Ví dụ: PHP,MySQL,Docker,Nginx</span>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="github_url" class="form-label">GitHub URL</label>
                <input type="url" id="github_url" name="github_url" class="form-control"
                       value="<?= e($project['github_url'] ?? '') ?>"
                       placeholder="https://github.com/...">
            </div>
            <div class="form-group">
                <label for="demo_url" class="form-label">Demo URL</label>
                <input type="url" id="demo_url" name="demo_url" class="form-control"
                       value="<?= e($project['demo_url'] ?? '') ?>"
                       placeholder="https://...">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="sort_order" class="form-label">Thứ tự hiển thị</label>
                <input type="number" id="sort_order" name="sort_order" class="form-control"
                       value="<?= (int)($project['sort_order'] ?? 100) ?>" min="0" max="9999">
            </div>
            <div class="form-group">
                <label class="form-label">Tùy chọn</label>
                <label style="display:flex;align-items:center;gap:var(--space-2);margin-top:10px;cursor:pointer;">
                    <input type="checkbox" name="is_featured" value="1"
                           <?= ($project['is_featured'] ?? 0) ? 'checked' : '' ?>>
                    <span>Đánh dấu nổi bật (hiển thị trên trang chủ)</span>
                </label>
            </div>
        </div>
    </div>

    <div class="form-panel">
        <div class="form-panel-title">🖼️ Ảnh minh họa</div>
        <?php if (!empty($project['image'])): ?>
        <div style="margin-bottom:var(--space-4);">
            <img src="<?= uploads_url($project['image']) ?>" alt="Ảnh hiện tại"
                 style="max-width:300px;border-radius:var(--radius);border:1px solid var(--color-border);">
            <p class="text-sm text-muted mt-4">Ảnh hiện tại. Tải ảnh mới để thay thế.</p>
        </div>
        <?php endif; ?>
        <input type="file" name="image" id="projectImage" class="form-control"
               accept="image/jpeg,image/png,image/gif,image/webp"
               style="max-width:400px;" aria-label="Chọn ảnh dự án">
        <span class="form-hint">JPG, PNG, GIF, WebP. Tối đa 5MB.</span>
    </div>

    <div style="display:flex;gap:var(--space-3);justify-content:flex-end;">
        <a href="/admin/projects" class="btn btn-ghost">Hủy</a>
        <button type="submit" class="btn btn-primary">
            <?= $isEdit ? '💾 Cập nhật dự án' : '➕ Thêm dự án' ?>
        </button>
    </div>
</form>

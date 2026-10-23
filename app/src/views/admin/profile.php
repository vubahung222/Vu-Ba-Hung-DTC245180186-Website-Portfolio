<?php
$initials = '';
if (!empty($profile['full_name'])) {
    $parts = explode(' ', $profile['full_name']);
    $initials = mb_strtoupper(mb_substr($parts[0], 0, 1, 'UTF-8') . mb_substr(end($parts), 0, 1, 'UTF-8'), 'UTF-8');
}
?>
<div class="page-header">
    <div>
        <h1 class="page-title">👤 Cập nhật hồ sơ</h1>
        <p class="page-subtitle">Thông tin hiển thị trên trang portfolio</p>
    </div>
</div>

<?php if ($success = \Core\Session::getFlash('success')): ?>
<div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error = \Core\Session::getFlash('error')): ?>
<div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<!-- Avatar Upload -->
<div class="form-panel">
    <div class="form-panel-title">📷 Ảnh đại diện</div>
    <div class="avatar-upload-area">
        <?php if (!empty($profile['avatar'])): ?>
        <img src="<?= uploads_url($profile['avatar']) ?>" id="avatarPreview"
             class="avatar-preview" alt="Ảnh đại diện">
        <?php else: ?>
        <div class="avatar-preview-placeholder" id="avatarPreview"><?= e($initials ?: '?') ?></div>
        <?php endif; ?>
        <div>
            <p class="font-bold mb-4">Thay đổi ảnh đại diện</p>
            <p class="text-sm text-muted mb-4">Định dạng: JPG, PNG, GIF, WebP. Tối đa 5MB.</p>
            <form action="/admin/profile/avatar" method="POST" enctype="multipart/form-data" data-loading="true">
                <?= csrf_field() ?>
                <div style="display:flex; gap:var(--space-3); align-items:center; flex-wrap:wrap;">
                    <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp"
                           class="form-control" style="max-width:280px;" aria-label="Chọn ảnh đại diện">
                    <button type="submit" class="btn btn-primary btn-sm">⬆️ Tải lên</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Profile Form -->
<form action="/admin/profile" method="POST" data-loading="true">
    <?= csrf_field() ?>

    <div class="form-panel">
        <div class="form-panel-title">📋 Thông tin cơ bản</div>
        <div class="form-row">
            <div class="form-group">
                <label for="full_name" class="form-label">Họ và tên <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" class="form-control"
                       value="<?= e($profile['full_name'] ?? '') ?>" required placeholder="[HỌ TÊN]">
            </div>
            <div class="form-group">
                <label for="title" class="form-label">Chức danh / Nghề nghiệp</label>
                <input type="text" id="title" name="title" class="form-control"
                       value="<?= e($profile['title'] ?? '') ?>" placeholder="Sinh viên CNTT">
            </div>
        </div>
        <div class="form-group">
            <label for="bio_short" class="form-label">Giới thiệu ngắn (hero)</label>
            <textarea id="bio_short" name="bio_short" class="form-control" rows="3"
                      placeholder="Câu giới thiệu ngắn gọn hiển thị trên trang chủ..."><?= e($profile['bio_short'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label for="bio_full" class="form-label">Giới thiệu đầy đủ (section Về tôi)</label>
            <textarea id="bio_full" name="bio_full" class="form-control" rows="8"
                      placeholder="Nội dung giới thiệu chi tiết..."><?= e($profile['bio_full'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:var(--space-2);cursor:pointer;">
                <input type="checkbox" name="is_available" value="1"
                       <?= ($profile['is_available'] ?? 0) ? 'checked' : '' ?>>
                <span>Hiển thị badge "Sẵn sàng nhận việc"</span>
            </label>
        </div>
    </div>

    <div class="form-panel">
        <div class="form-panel-title">📞 Thông tin liên hệ</div>
        <div class="form-row">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?= e($profile['email'] ?? '') ?>" placeholder="email@example.com">
            </div>
            <div class="form-group">
                <label for="phone" class="form-label">Số điện thoại</label>
                <input type="tel" id="phone" name="phone" class="form-control"
                       value="<?= e($profile['phone'] ?? '') ?>" placeholder="0900 000 000">
            </div>
        </div>
        <div class="form-group">
            <label for="location" class="form-label">Địa điểm</label>
            <input type="text" id="location" name="location" class="form-control"
                   value="<?= e($profile['location'] ?? '') ?>" placeholder="TP. Hồ Chí Minh, Việt Nam">
        </div>
    </div>

    <div class="form-panel">
        <div class="form-panel-title">🔗 Mạng xã hội</div>
        <div class="form-group">
            <label for="github_url" class="form-label">GitHub URL</label>
            <input type="url" id="github_url" name="github_url" class="form-control"
                   value="<?= e($profile['github_url'] ?? '') ?>" placeholder="https://github.com/username">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                <input type="url" id="linkedin_url" name="linkedin_url" class="form-control"
                       value="<?= e($profile['linkedin_url'] ?? '') ?>" placeholder="https://linkedin.com/in/...">
            </div>
            <div class="form-group">
                <label for="facebook_url" class="form-label">Facebook URL</label>
                <input type="url" id="facebook_url" name="facebook_url" class="form-control"
                       value="<?= e($profile['facebook_url'] ?? '') ?>" placeholder="https://facebook.com/...">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="website_url" class="form-label">Website cá nhân</label>
                <input type="url" id="website_url" name="website_url" class="form-control"
                       value="<?= e($profile['website_url'] ?? '') ?>" placeholder="https://example.com">
            </div>
            <div class="form-group">
                <label for="cv_url" class="form-label">Link tải CV</label>
                <input type="url" id="cv_url" name="cv_url" class="form-control"
                       value="<?= e($profile['cv_url'] ?? '') ?>" placeholder="https://drive.google.com/...">
            </div>
        </div>
    </div>

    <div style="display:flex; gap:var(--space-3); justify-content:flex-end;">
        <a href="/admin/dashboard" class="btn btn-ghost">Hủy</a>
        <button type="submit" class="btn btn-primary">💾 Lưu thay đổi</button>
    </div>
</form>

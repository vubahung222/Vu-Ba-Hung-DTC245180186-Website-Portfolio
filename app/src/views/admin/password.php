<div class="page-header">
    <div>
        <h1 class="page-title">🔒 Đổi mật khẩu</h1>
        <p class="page-subtitle">Cập nhật mật khẩu đăng nhập admin</p>
    </div>
</div>

<?php $msgSuccess = $success ?? \Core\Session::getFlash('success'); if (!empty($msgSuccess)): ?><div class="alert alert-success"><?= e($msgSuccess) ?></div><?php endif; ?>
<?php $msgError = $error ?? \Core\Session::getFlash('error'); if (!empty($msgError)): ?><div class="alert alert-error"><?= e($msgError) ?></div><?php endif; ?>

<form action="/admin/password" method="POST" style="max-width:480px;" data-loading="true">
    <?= csrf_field() ?>

    <div class="form-panel">
        <div class="form-panel-title">🔑 Mật khẩu</div>

        <div class="form-group">
            <label for="current_password" class="form-label">Mật khẩu hiện tại <span class="required">*</span></label>
            <input type="password" id="current_password" name="current_password"
                   class="form-control" required autocomplete="current-password"
                   placeholder="••••••••">
        </div>

        <div class="form-group">
            <label for="new_password" class="form-label">Mật khẩu mới <span class="required">*</span></label>
            <input type="password" id="new_password" name="new_password"
                   class="form-control" required autocomplete="new-password"
                   minlength="8" placeholder="Tối thiểu 8 ký tự">
            <span class="form-hint">Tối thiểu 8 ký tự. Nên có chữ hoa, số và ký tự đặc biệt.</span>
        </div>

        <div class="form-group">
            <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới <span class="required">*</span></label>
            <input type="password" id="confirm_password" name="confirm_password"
                   class="form-control" required autocomplete="new-password"
                   placeholder="Nhập lại mật khẩu mới">
        </div>

        <div style="display:flex;gap:var(--space-3);">
            <a href="/admin/dashboard" class="btn btn-ghost">Hủy</a>
            <button type="submit" class="btn btn-primary">🔐 Đổi mật khẩu</button>
        </div>
    </div>
</form>

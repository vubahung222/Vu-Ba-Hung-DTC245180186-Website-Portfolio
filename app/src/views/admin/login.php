<div class="login-card">
    <div class="login-logo">
        <h1>Đăng nhập Admin</h1>
        <p class="login-subtitle">Portfolio Management System</p>
    </div>

    <?php if ($error = \Core\Session::getFlash('error')): ?>
    <div class="alert alert-error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($notice = \Core\Session::getFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?= e($notice) ?></div>
    <?php endif; ?>

    <form action="/admin/login" method="POST" id="loginForm" data-loading="true" novalidate>
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="email" class="form-label">
                Email <span class="required" aria-hidden="true">*</span>
            </label>
            <input
                type="email"
                id="email"
                name="email"
                class="form-control"
                placeholder="admin@portfolio.local"
                value="admin@portfolio.local"
                required
                autocomplete="email"
                autofocus
                aria-required="true">
        </div>

        <div class="form-group">
            <label for="password" class="form-label">
                Mật khẩu <span class="required" aria-hidden="true">*</span>
            </label>
            <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                placeholder="••••••••"
                value="Admin@2025!"
                required
                autocomplete="current-password"
                aria-required="true">
        </div>

        <button type="submit" class="btn btn-primary w-full" id="loginBtn">
            Đăng nhập
        </button>
    </form>

    <div class="login-footer">
        <a href="/" style="color:var(--color-primary)">← Về trang chủ</a>
    </div>
</div>

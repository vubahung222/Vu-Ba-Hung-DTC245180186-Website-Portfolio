<?php
declare(strict_types=1);

use Core\{View, Session, Response, Csrf, Auth};

class AuthController
{
    public function loginForm(array $params = []): void
    {
        if (Auth::check()) {
            Response::redirect('/admin/dashboard');
        }

        View::renderLayout('admin', 'admin/login', [
            'title'  => 'Đăng nhập Admin',
            'error'  => Session::getFlash('error'),
            'notice' => Session::getFlash('notice'),
        ]);
    }

    public function login(array $params = []): void
    {
        Csrf::validateOrFail();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            Session::flash('error', 'Vui lòng nhập đầy đủ email và mật khẩu.');
            Response::redirect('/admin/login');
        }

        if (Auth::login($email, $password)) {
            Session::flash('notice', 'Đăng nhập thành công!');
            Response::redirect('/admin/dashboard');
        } else {
            Session::flash('error', 'Email hoặc mật khẩu không đúng.');
            Response::redirect('/admin/login');
        }
    }

    public function logout(array $params = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_csrf_token'])) {
            Csrf::validateOrFail();
        }
        Auth::logout();
    }
}

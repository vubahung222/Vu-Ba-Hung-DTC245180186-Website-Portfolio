<?php
declare(strict_types=1);

use Core\{View, Session, Response, Csrf, Auth, Request, Validator};

class AdminController
{
    private function requireLogin(): void
    {
        Auth::requireAuth();
    }

    // ── Dashboard ──────────────────────────────
    public function dashboard(array $params = []): void
    {
        $this->requireLogin();
        View::renderLayout('admin', 'admin/dashboard', [
            'title'          => 'Dashboard - Admin',
            'user'           => Auth::user(),
            'notice'         => Session::getFlash('notice'),
            'stats'          => [
                'projects'    => Project::count(),
                'skills'      => Skill::count(),
                'education'   => Education::count(),
                'experiences' => Experience::count(),
                'messages'    => ContactMessage::count(),
                'unread'      => ContactMessage::countUnread(),
            ],
        ]);
    }

    // ── Profile ────────────────────────────────
    public function profileEdit(array $params = []): void
    {
        $this->requireLogin();
        View::renderLayout('admin', 'admin/profile', [
            'title'   => 'Hồ sơ - Admin',
            'user'    => Auth::user(),
            'profile' => Profile::get(),
            'success' => Session::getFlash('success'),
            'error'   => Session::getFlash('error'),
        ]);
    }

    public function profileUpdate(array $params = []): void
    {
        $this->requireLogin();
        Csrf::validateOrFail();

        $data = Request::all();
        $allowed = ['full_name','title','bio_short','bio_full','email','phone','location',
                    'github_url','linkedin_url','facebook_url','website_url','cv_url','is_available'];
        $update = array_intersect_key($data, array_flip($allowed));
        $update['is_available'] = isset($data['is_available']) ? 1 : 0;

        $profile = Profile::get();
        if ($profile && Profile::update($profile['id'], $update)) {
            Session::flash('success', 'Cập nhật hồ sơ thành công!');
        } else {
            Session::flash('error', 'Có lỗi khi cập nhật hồ sơ.');
        }
        Response::redirect('/admin/profile');
    }

    public function avatarUpload(array $params = []): void
    {
        $this->requireLogin();
        Csrf::validateOrFail();

        $file = Request::file('avatar');
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Lỗi upload file. Vui lòng thử lại.');
            Response::redirect('/admin/profile');
        }

        // Kiểm tra MIME thực tế (không tin header)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mime, $allowed)) {
            Session::flash('error', 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WebP).');
            Response::redirect('/admin/profile');
        }

        $ext = match($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        };

        // Kiểm tra kích thước (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            Session::flash('error', 'File quá lớn. Tối đa 5MB.');
            Response::redirect('/admin/profile');
        }

        // Tên file server-side (không dùng tên người dùng)
        $filename = 'avatar_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = UPLOADS_PATH . '/avatars/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            Session::flash('error', 'Không thể lưu file. Kiểm tra quyền thư mục uploads/avatars.');
            Response::redirect('/admin/profile');
        }

        $profile = Profile::get();
        if ($profile) {
            // Xóa ảnh cũ nếu có
            if (!empty($profile['avatar'])) {
                $oldPath = UPLOADS_PATH . '/' . ltrim($profile['avatar'], '/');
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            Profile::updateAvatar($profile['id'], 'avatars/' . $filename);
        }

        Session::flash('success', 'Cập nhật ảnh đại diện thành công!');
        Response::redirect('/admin/profile');
    }

    // ── Skills ─────────────────────────────────
    public function skillsList(array $params = []): void
    {
        $this->requireLogin();
        View::renderLayout('admin', 'admin/skills', [
            'title'   => 'Kỹ năng - Admin',
            'user'    => Auth::user(),
            'skills'  => Skill::all(),
            'success' => Session::getFlash('success'),
            'error'   => Session::getFlash('error'),
        ]);
    }

    public function skillCreate(array $params = []): void
    {
        $this->requireLogin();
        Csrf::validateOrFail();

        $data = Request::all();
        $v = Validator::make($data, [
            'name'     => 'required|max:100',
            'category' => 'required|max:50',
            'level'    => 'required|numeric|between:0,100',
        ]);

        if ($v->fails()) {
            Session::flash('error', array_values($v->errors())[0][0]);
            Response::redirect('/admin/skills');
        }

        Skill::create($data);
        Session::flash('success', 'Thêm kỹ năng thành công!');
        Response::redirect('/admin/skills');
    }

    public function skillUpdate(array $params = []): void
    {
        $this->requireLogin();
        Csrf::validateOrFail();

        $id = (int)($params['id'] ?? 0);
        $data = Request::all();
        Skill::update($id, $data);
        Session::flash('success', 'Cập nhật kỹ năng thành công!');
        Response::redirect('/admin/skills');
    }

    public function skillDelete(array $params = []): void
    {
        $this->requireLogin();
        Csrf::validateOrFail();

        $id = (int)($params['id'] ?? 0);
        if (Skill::delete($id)) {
            Session::flash('success', 'Đã xóa kỹ năng.');
        } else {
            Session::flash('error', 'Không thể xóa kỹ năng.');
        }
        Response::redirect('/admin/skills');
    }

    // ── Projects ───────────────────────────────
    public function projectsList(array $params = []): void
    {
        $this->requireLogin();
        View::renderLayout('admin', 'admin/projects', [
            'title'    => 'Dự án - Admin',
            'user'     => Auth::user(),
            'projects' => Project::all(),
            'success'  => Session::getFlash('success'),
            'error'    => Session::getFlash('error'),
        ]);
    }

    public function projectCreate(array $params = []): void
    {
        $this->requireLogin();
        View::renderLayout('admin', 'admin/project-form', [
            'title'   => 'Thêm dự án - Admin',
            'user'    => Auth::user(),
            'project' => null,
            'action'  => '/admin/projects/store',
        ]);
    }

    public function projectStore(array $params = []): void
    {
        $this->requireLogin();
        Csrf::validateOrFail();

        $data = Request::all();
        $v = Validator::make($data, [
            'title'       => 'required|max:200',
            'description' => 'required|min:10',
        ]);

        if ($v->fails()) {
            Session::flash('error', array_values($v->errors())[0][0]);
            Response::redirect('/admin/projects/create');
        }

        $id = Project::create($data);

        // Upload ảnh nếu có
        $file = Request::file('image');
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $this->handleProjectImage($id, $file);
        }

        Session::flash('success', 'Thêm dự án thành công!');
        Response::redirect('/admin/projects');
    }

    public function projectEdit(array $params = []): void
    {
        $this->requireLogin();
        $id = (int)($params['id'] ?? 0);
        $project = Project::find($id);
        if (!$project) {
            Session::flash('error', 'Không tìm thấy dự án.');
            Response::redirect('/admin/projects');
        }

        View::renderLayout('admin', 'admin/project-form', [
            'title'   => 'Chỉnh sửa dự án - Admin',
            'user'    => Auth::user(),
            'project' => $project,
            'action'  => "/admin/projects/{$id}/update",
        ]);
    }

    public function projectUpdate(array $params = []): void
    {
        $this->requireLogin();
        Csrf::validateOrFail();

        $id = (int)($params['id'] ?? 0);
        $data = Request::all();

        // Giữ ảnh cũ nếu không upload mới
        $existing = Project::find($id);
        $data['image'] = $existing['image'] ?? null;

        $file = Request::file('image');
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $imgPath = $this->handleProjectImage($id, $file, false);
            if ($imgPath) $data['image'] = $imgPath;
        }

        Project::update($id, $data);
        Session::flash('success', 'Cập nhật dự án thành công!');
        Response::redirect('/admin/projects');
    }

    public function projectDelete(array $params = []): void
    {
        $this->requireLogin();
        Csrf::validateOrFail();

        $id = (int)($params['id'] ?? 0);
        if (Project::delete($id)) {
            Session::flash('success', 'Đã xóa dự án.');
        } else {
            Session::flash('error', 'Không thể xóa dự án.');
        }
        Response::redirect('/admin/projects');
    }

    private function handleProjectImage(int $projectId, array $file, bool $updateDb = true): ?string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mime, $allowed)) return null;
        if ($file['size'] > 5 * 1024 * 1024) return null;

        $ext = match($mime) {
            'image/jpeg' => 'jpg', 'image/png' => 'png',
            'image/gif' => 'gif', 'image/webp' => 'webp',
        };
        $filename = "project_{$projectId}_" . time() . '.' . $ext;
        $dest = UPLOADS_PATH . '/avatars/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $path = 'avatars/' . $filename;
            if ($updateDb) Project::updateImage($projectId, $path);
            return $path;
        }
        return null;
    }

    // ── Education ──────────────────────────────
    public function educationList(array $params = []): void
    {
        $this->requireLogin();
        View::renderLayout('admin', 'admin/education', [
            'title'     => 'Học vấn - Admin',
            'user'      => Auth::user(),
            'education' => Education::all(),
            'success'   => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    public function educationCreate(array $params = []): void
    {
        $this->requireLogin();
        Csrf::validateOrFail();
        Education::create(Request::all());
        Session::flash('success', 'Thêm học vấn thành công!');
        Response::redirect('/admin/education');
    }

    public function educationUpdate(array $params = []): void
    {
        $this->requireLogin();
        Csrf::validateOrFail();
        $id = (int)($params['id'] ?? 0);
        Education::update($id, Request::all());
        Session::flash('success', 'Cập nhật học vấn thành công!');
        Response::redirect('/admin/education');
    }

    public function educationDelete(array $params = []): void
    {
        $this->requireLogin();
        Csrf::validateOrFail();
        $id = (int)($params['id'] ?? 0);
        Education::delete($id);
        Session::flash('success', 'Đã xóa mục học vấn.');
        Response::redirect('/admin/education');
    }

    // ── Experience ─────────────────────────────
    public function experienceList(array $params = []): void
    {
        $this->requireLogin();
        View::renderLayout('admin', 'admin/experiences', [
            'title'       => 'Kinh nghiệm - Admin',
            'user'        => Auth::user(),
            'experiences' => Experience::all(),
            'success'     => Session::getFlash('success'),
            'error'       => Session::getFlash('error'),
        ]);
    }

    public function experienceCreate(array $params = []): void
    {
        $this->requireLogin();
        Csrf::validateOrFail();
        Experience::create(Request::all());
        Session::flash('success', 'Thêm kinh nghiệm thành công!');
        Response::redirect('/admin/experiences');
    }

    public function experienceUpdate(array $params = []): void
    {
        $this->requireLogin();
        Csrf::validateOrFail();
        $id = (int)($params['id'] ?? 0);
        Experience::update($id, Request::all());
        Session::flash('success', 'Cập nhật kinh nghiệm thành công!');
        Response::redirect('/admin/experiences');
    }

    public function experienceDelete(array $params = []): void
    {
        $this->requireLogin();
        Csrf::validateOrFail();
        $id = (int)($params['id'] ?? 0);
        Experience::delete($id);
        Session::flash('success', 'Đã xóa mục kinh nghiệm.');
        Response::redirect('/admin/experiences');
    }

    // ── Messages ───────────────────────────────
    public function messagesList(array $params = []): void
    {
        $this->requireLogin();
        View::renderLayout('admin', 'admin/messages', [
            'title'    => 'Tin nhắn - Admin',
            'user'     => Auth::user(),
            'messages' => ContactMessage::all(),
            'success'  => Session::getFlash('success'),
            'error'    => Session::getFlash('error'),
        ]);
    }

    public function messageRead(array $params = []): void
    {
        $this->requireLogin();
        Csrf::validateOrFail();
        $id = (int)($params['id'] ?? 0);
        ContactMessage::markRead($id);
        Session::flash('success', 'Đã đánh dấu đã đọc.');
        Response::redirect('/admin/messages');
    }

    public function messageDelete(array $params = []): void
    {
        $this->requireLogin();
        Csrf::validateOrFail();
        $id = (int)($params['id'] ?? 0);
        if (ContactMessage::delete($id)) {
            Session::flash('success', 'Đã xóa tin nhắn.');
        } else {
            Session::flash('error', 'Không thể xóa tin nhắn.');
        }
        Response::redirect('/admin/messages');
    }

    // ── Change Password ────────────────────────
    public function passwordForm(array $params = []): void
    {
        $this->requireLogin();
        View::renderLayout('admin', 'admin/password', [
            'title'   => 'Đổi mật khẩu - Admin',
            'user'    => Auth::user(),
            'success' => Session::getFlash('success'),
            'error'   => Session::getFlash('error'),
        ]);
    }

    public function passwordChange(array $params = []): void
    {
        $this->requireLogin();
        Csrf::validateOrFail();

        $user        = Auth::user();
        $currentPwd  = $_POST['current_password'] ?? '';
        $newPwd      = $_POST['new_password'] ?? '';
        $confirmPwd  = $_POST['confirm_password'] ?? '';

        $adminUser = AdminUser::find($user['id']);
        if (!$adminUser || !password_verify($currentPwd, $adminUser['password_hash'])) {
            Session::flash('error', 'Mật khẩu hiện tại không đúng.');
            Response::redirect('/admin/password');
        }

        if (strlen($newPwd) < 8) {
            Session::flash('error', 'Mật khẩu mới tối thiểu 8 ký tự.');
            Response::redirect('/admin/password');
        }

        if ($newPwd !== $confirmPwd) {
            Session::flash('error', 'Xác nhận mật khẩu không khớp.');
            Response::redirect('/admin/password');
        }

        AdminUser::updatePassword($user['id'], $newPwd);
        Session::flash('success', 'Đổi mật khẩu thành công!');
        Response::redirect('/admin/password');
    }
}

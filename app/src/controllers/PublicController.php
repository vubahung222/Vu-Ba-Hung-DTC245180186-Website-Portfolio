<?php
declare(strict_types=1);

use Core\{View, Session, Response, Csrf, Validator, Request};

class PublicController
{
    public function home(array $params = []): void
    {
        try {
            $profile = Profile::get();
            $skills = Skill::byCategory();
            $projects = Project::featured();
            $education = Education::all();
            $experiences = Experience::all();

            View::renderLayout('public', 'public/home', [
                'title'       => ($profile['full_name'] ?? '[HỌ TÊN]') . ' - Portfolio',
                'profile'     => $profile,
                'skills'      => $skills,
                'projects'    => $projects,
                'education'   => $education,
                'experiences' => $experiences,
            ]);
        } catch (\Exception $e) {
            error_log("Home error: " . $e->getMessage());
            View::renderLayout('public', 'errors/500', ['title' => 'Lỗi hệ thống']);
        }
    }

    public function projects(array $params = []): void
    {
        try {
            $profile  = Profile::get();
            $projects = Project::all();

            View::renderLayout('public', 'public/projects', [
                'title'    => 'Dự án - ' . ($profile['full_name'] ?? 'Portfolio'),
                'profile'  => $profile,
                'projects' => $projects,
            ]);
        } catch (\Exception $e) {
            error_log("Projects error: " . $e->getMessage());
            View::renderLayout('public', 'errors/500', ['title' => 'Lỗi hệ thống']);
        }
    }

    public function projectDetail(array $params = []): void
    {
        $id = (int)($params['id'] ?? 0);
        $project = Project::find($id);

        if (!$project) {
            http_response_code(404);
            View::renderLayout('public', 'errors/404', ['title' => 'Không tìm thấy dự án']);
            return;
        }

        $profile = Profile::get();
        View::renderLayout('public', 'public/project-detail', [
            'title'   => $project['title'] . ' - Portfolio',
            'profile' => $profile,
            'project' => $project,
            'techs'   => Project::getTechStack($project),
        ]);
    }

    public function contact(array $params = []): void
    {
        $profile = Profile::get();
        View::renderLayout('public', 'public/contact', [
            'title'    => 'Liên hệ - ' . ($profile['full_name'] ?? 'Portfolio'),
            'profile'  => $profile,
            'success'  => Session::getFlash('contact_success'),
            'error'    => Session::getFlash('contact_error'),
        ]);
    }

    public function contactSubmit(array $params = []): void
    {
        Csrf::validateOrFail();

        $data = Request::all();
        $v = Validator::make($data, [
            'sender_name'  => 'required|max:150',
            'sender_email' => 'required|email|max:255',
            'subject'      => 'required|max:300',
            'message'      => 'required|min:10|max:3000',
        ]);

        if ($v->fails()) {
            foreach ($v->errors() as $field => $errs) {
                Session::flash("old_{$field}", $data[$field] ?? '');
            }
            Session::flash('contact_error', array_values($v->errors())[0][0]);
            Response::redirect('/lien-he');
        }

        try {
            ContactMessage::create([
                'sender_name'  => trim($data['sender_name']),
                'sender_email' => trim($data['sender_email']),
                'subject'      => trim($data['subject']),
                'message'      => trim($data['message']),
                'ip_address'   => Request::ip(),
            ]);
            Session::flash('contact_success', 'Cảm ơn bạn đã liên hệ! Tôi sẽ phản hồi sớm nhất có thể.');
        } catch (\Exception $e) {
            error_log("Contact submit error: " . $e->getMessage());
            Session::flash('contact_error', 'Đã xảy ra lỗi khi gửi tin nhắn. Vui lòng thử lại.');
        }

        Response::redirect('/lien-he');
    }

    public function health(array $params = []): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        $dbOk = false;
        try {
            Core\Database::fetchOne("SELECT 1");
            $dbOk = true;
        } catch (\Exception) {}

        $status = $dbOk ? 'healthy' : 'degraded';
        http_response_code($dbOk ? 200 : 503);
        echo json_encode([
            'status'    => $status,
            'timestamp' => date('c'),
            'database'  => $dbOk ? 'up' : 'down',
        ], JSON_UNESCAPED_UNICODE);
    }
}

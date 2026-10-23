<?php
/**
 * Script cập nhật và khôi phục toàn bộ dữ liệu Tiếng Việt chuẩn UTF-8 trong MySQL
 * Chạy trực tiếp bên trong PHP-FPM container để tránh lỗi Windows Code Page / PowerShell OEM.
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use Core\Database as DB;

echo "=== ĐANG KHÔI PHỤC DỮ LIỆU TIẾNG VIỆT UTF-8 CHUẨN ===\n";

try {
    $pdo = DB::getConnection();
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("SET CHARACTER SET utf8mb4");

    // 1. Cập nhật bảng profile
    $profileStmt = $pdo->prepare("
        UPDATE `profile` SET
            `full_name` = :full_name,
            `title` = :title,
            `bio_short` = :bio_short,
            `bio_full` = :bio_full,
            `email` = :email,
            `phone` = :phone,
            `location` = :location,
            `github_url` = :github_url
        WHERE id = 1
    ");

    $bio_full = "Tôi là Vũ Bá Hùng, sinh viên Kỹ thuật Phần mềm khóa K23C tại Trường Đại học Công nghệ Thông tin và Truyền thông - Đại học Thái Nguyên (ICTU). Tôi có niềm đam mê sâu sắc với phát triển ứng dụng web hiện đại và triển khai, quản trị hệ thống bằng Docker, Nginx, Prometheus, Grafana và Loki.\n\nMục tiêu của tôi là xây dựng các giải pháp phần mềm chất lượng cao, bảo mật, tối ưu hiệu năng và dễ dàng mở rộng. Tôi luôn tích cực học hỏi các công nghệ mới và áp dụng thực tiễn vào các dự án.";

    $profileStmt->execute([
        ':full_name'  => 'Vũ Bá Hùng',
        ':title'      => 'Kỹ sư Phần mềm | KTPM K23C',
        ':bio_short'  => 'Xin chào! Tôi là Vũ Bá Hùng, sinh viên ngành Kỹ thuật Phần mềm (KTPM K23C) tại Trường ĐH Công nghệ Thông tin & Truyền thông - Đại học Thái Nguyên. Đam mê lập trình web, DevOps và quản trị hệ thống.',
        ':bio_full'   => $bio_full,
        ':email'      => 'vubahung222@gmail.com',
        ':phone'      => '0900 000 000',
        ':location'   => 'Thái Nguyên, Việt Nam',
        ':github_url' => 'https://github.com/vubahung222/Vu-Ba-Hung-DTC245180186-Website-Portfolio'
    ]);
    echo "[OK] Cập nhật profile hoàn tất.\n";

    // 2. Cập nhật bảng education
    $eduStmt = $pdo->prepare("
        UPDATE `education` SET
            `institution` = :institution,
            `degree` = :degree,
            `field_of_study` = :field_of_study,
            `description` = :description
        WHERE id = 1
    ");
    $eduStmt->execute([
        ':institution'    => 'Trường Đại học Công nghệ Thông tin và Truyền thông - Đại học Thái Nguyên',
        ':degree'         => 'Cử nhân / Kỹ sư',
        ':field_of_study' => 'Kỹ thuật Phần mềm (KTPM K23C)',
        ':description'    => 'Sinh viên KTPM K23C, MSSV: DTC245180186. Các môn học chính: Lập trình Web, Cơ sở Dữ liệu, Mạng Máy tính, Triển khai và Quản trị Hệ thống Phần mềm.'
    ]);
    echo "[OK] Cập nhật education hoàn tất.\n";

    // 3. Cập nhật bảng experiences
    $expStmt = $pdo->prepare("
        UPDATE `experiences` SET
            `company` = :company,
            `position` = :position,
            `description` = :description,
            `location` = :location
        WHERE id = 1
    ");
    $expStmt->execute([
        ':company'     => 'Công ty ABC (Thực tập)',
        ':position'    => 'Thực tập sinh Web Developer',
        ':description' => 'Tham gia phát triển tính năng cho hệ thống quản lý nội bộ. Sử dụng PHP, MySQL và giao diện với Bootstrap.',
        ':location'    => 'Thái Nguyên, Việt Nam'
    ]);
    echo "[OK] Cập nhật experiences hoàn tất.\n";

    // 4. Cập nhật bảng projects
    $projects = [
        1 => [
            'title'       => 'Website Portfolio Cá Nhân',
            'description' => 'Trang web giới thiệu bản thân với đầy đủ thông tin về kỹ năng, dự án và kinh nghiệm. Hệ thống admin quản lý nội dung, giám sát bằng Prometheus và Grafana, thu thập log bằng Loki.'
        ],
        2 => [
            'title'       => 'Hệ Thống Quản Lý Sinh Viên',
            'description' => 'Ứng dụng web quản lý thông tin sinh viên cho trường đại học. Hỗ trợ đăng ký môn học, xem điểm và quản lý hồ sơ cá nhân.'
        ],
        3 => [
            'title'       => 'Ứng Dụng To-Do List',
            'description' => 'Ứng dụng quản lý công việc đơn giản với tính năng tạo, chỉnh sửa, xóa và đánh dấu hoàn thành. Dữ liệu lưu cục bộ bằng localStorage.'
        ]
    ];
    $projStmt = $pdo->prepare("UPDATE `projects` SET `title` = :title, `description` = :desc WHERE id = :id");
    foreach ($projects as $id => $p) {
        $projStmt->execute([':title' => $p['title'], ':desc' => $p['description'], ':id' => $id]);
    }
    echo "[OK] Cập nhật projects hoàn tất.\n";

    // 5. Kiểm tra lại dữ liệu vừa cập nhật
    $check = $pdo->query("SELECT full_name, bio_full FROM `profile` WHERE id = 1")->fetch();
    echo "\n=== KẾT QUẢ KIỂM TRA LẠI DỮ LIỆU ===\n";
    echo "Họ và tên: " . $check['full_name'] . "\n";
    echo "Bio (100 ký tự đầu): " . mb_substr($check['bio_full'], 0, 100, 'UTF-8') . "...\n";
    echo "=== HOÀN TẤT THÀNH CÔNG ===\n";

} catch (\Exception $e) {
    echo "[ERROR] Lỗi khi cập nhật DB: " . $e->getMessage() . "\n";
    exit(1);
}

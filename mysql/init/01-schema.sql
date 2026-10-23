-- =============================================
-- PORTFOLIO DATABASE INITIALIZATION
-- Chạy tự động khi MySQL container khởi tạo lần đầu
-- =============================================

-- Đảm bảo đúng encoding
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Dùng database đã tạo từ env
USE portfolio_db;

-- Bảng 1: Hồ sơ cá nhân
CREATE TABLE IF NOT EXISTS `profile` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name` VARCHAR(100) NOT NULL DEFAULT '[HỌ TÊN]' COMMENT 'Họ và tên đầy đủ',
    `title` VARCHAR(150) NOT NULL DEFAULT 'Sinh viên Công nghệ Thông tin' COMMENT 'Chức danh / nghề nghiệp',
    `bio_short` TEXT COMMENT 'Giới thiệu ngắn (hero)',
    `bio_full` LONGTEXT COMMENT 'Giới thiệu đầy đủ (section About)',
    `avatar` VARCHAR(255) DEFAULT NULL COMMENT 'Đường dẫn ảnh đại diện',
    `email` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(30) DEFAULT NULL,
    `location` VARCHAR(150) DEFAULT NULL,
    `github_url` VARCHAR(500) DEFAULT NULL,
    `linkedin_url` VARCHAR(500) DEFAULT NULL,
    `facebook_url` VARCHAR(500) DEFAULT NULL,
    `website_url` VARCHAR(500) DEFAULT NULL,
    `cv_url` VARCHAR(500) DEFAULT NULL COMMENT 'Link tải CV',
    `is_available` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Sẵn sàng nhận việc',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 2: Kỹ năng
CREATE TABLE IF NOT EXISTS `skills` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `category` VARCHAR(50) NOT NULL DEFAULT 'other' COMMENT 'frontend | backend | devops | tool | language | other',
    `level` TINYINT UNSIGNED NOT NULL DEFAULT 70 COMMENT 'Phần trăm thành thạo 0-100',
    `icon` VARCHAR(100) DEFAULT NULL COMMENT 'Tên icon hoặc SVG slug',
    `sort_order` SMALLINT NOT NULL DEFAULT 100,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 3: Dự án
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NOT NULL,
    `tech_stack` VARCHAR(500) DEFAULT NULL COMMENT 'JSON array hoặc chuỗi phẳng, VD: PHP,MySQL,Docker',
    `image` VARCHAR(255) DEFAULT NULL,
    `github_url` VARCHAR(500) DEFAULT NULL,
    `demo_url` VARCHAR(500) DEFAULT NULL,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` SMALLINT NOT NULL DEFAULT 100,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 4: Học vấn
CREATE TABLE IF NOT EXISTS `education` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution` VARCHAR(200) NOT NULL,
    `degree` VARCHAR(200) NOT NULL,
    `field_of_study` VARCHAR(200) DEFAULT NULL,
    `start_year` YEAR NOT NULL,
    `end_year` YEAR DEFAULT NULL COMMENT 'NULL = đang học',
    `description` TEXT DEFAULT NULL,
    `gpa` VARCHAR(20) DEFAULT NULL,
    `sort_order` SMALLINT NOT NULL DEFAULT 100,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 5: Kinh nghiệm làm việc
CREATE TABLE IF NOT EXISTS `experiences` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `company` VARCHAR(200) NOT NULL,
    `position` VARCHAR(200) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE DEFAULT NULL COMMENT 'NULL = hiện tại',
    `description` TEXT DEFAULT NULL,
    `location` VARCHAR(150) DEFAULT NULL,
    `sort_order` SMALLINT NOT NULL DEFAULT 100,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 6: Tin nhắn liên hệ
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `sender_name` VARCHAR(150) NOT NULL,
    `sender_email` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(300) NOT NULL,
    `message` TEXT NOT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `read_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_is_read` (`is_read`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 7: Admin users
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100) NOT NULL DEFAULT 'Admin',
    `last_login` TIMESTAMP NULL DEFAULT NULL,
    `login_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `locked_until` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 8: Sessions (CSRF tokens, session store)
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(128) NOT NULL,
    `data` MEDIUMBLOB,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SEED DATA (Dữ liệu mẫu placeholder)
-- =============================================

-- Hồ sơ cá nhân placeholder
INSERT INTO `profile` (
    `full_name`, `title`, `bio_short`, `bio_full`,
    `email`, `phone`, `location`,
    `github_url`, `linkedin_url`, `facebook_url`
) VALUES (
    '[HỌ TÊN]',
    'Sinh viên Công nghệ Thông tin',
    'Xin chào! Tôi là sinh viên đam mê lập trình và công nghệ. Tôi thích xây dựng các ứng dụng web hữu ích và học hỏi các công nghệ mới mỗi ngày.',
    'Tôi là sinh viên Công nghệ Thông tin với niềm đam mê mãnh liệt với lập trình web và DevOps. Trong suốt quá trình học tập, tôi đã tích lũy kinh nghiệm với nhiều công nghệ hiện đại từ phát triển frontend, backend đến triển khai hệ thống bằng Docker và giám sát bằng Prometheus/Grafana.\n\nTôi luôn hướng đến việc viết code sạch, có cấu trúc tốt và bảo mật. Ngoài việc học, tôi thích đóng góp cho các dự án mã nguồn mở và chia sẻ kiến thức với cộng đồng.',
    'sinhvien@example.com',
    '0900 000 000',
    'Thành phố Hồ Chí Minh, Việt Nam',
    '[GITHUB_URL]',
    NULL,
    NULL
);

-- Kỹ năng mẫu
INSERT INTO `skills` (`name`, `category`, `level`, `icon`, `sort_order`) VALUES
('HTML5', 'frontend', 90, 'html5', 10),
('CSS3 / Sass', 'frontend', 85, 'css3', 20),
('JavaScript (ES6+)', 'frontend', 80, 'javascript', 30),
('PHP 8', 'backend', 80, 'php', 40),
('MySQL', 'backend', 75, 'mysql', 50),
('Docker', 'devops', 70, 'docker', 60),
('Nginx', 'devops', 65, 'nginx', 70),
('Linux / Bash', 'tool', 70, 'linux', 80),
('Git / GitHub', 'tool', 85, 'git', 90),
('Prometheus / Grafana', 'devops', 60, 'grafana', 100);

-- Dự án mẫu
INSERT INTO `projects` (`title`, `description`, `tech_stack`, `is_featured`, `sort_order`) VALUES
(
    'Website Portfolio Cá Nhân',
    'Trang web giới thiệu bản thân với đầy đủ thông tin về kỹ năng, dự án và kinh nghiệm. Hệ thống admin quản lý nội dung, giám sát bằng Prometheus và Grafana, thu thập log bằng Loki.',
    'PHP,MySQL,Docker,Nginx,Prometheus,Grafana,Loki',
    1,
    10
),
(
    'Hệ Thống Quản Lý Sinh Viên',
    'Ứng dụng web quản lý thông tin sinh viên cho trường đại học. Hỗ trợ đăng ký môn học, xem điểm và quản lý hồ sơ cá nhân.',
    'PHP,Laravel,MySQL,Bootstrap,jQuery',
    1,
    20
),
(
    'Ứng Dụng To-Do List',
    'Ứng dụng quản lý công việc đơn giản với tính năng tạo, chỉnh sửa, xóa và đánh dấu hoàn thành. Dữ liệu lưu cục bộ bằng localStorage.',
    'HTML,CSS,JavaScript',
    0,
    30
);

-- Học vấn mẫu
INSERT INTO `education` (`institution`, `degree`, `field_of_study`, `start_year`, `end_year`, `description`) VALUES
(
    '[TRƯỜNG/KHOA]',
    'Cử nhân',
    'Công nghệ Thông tin',
    2022,
    NULL,
    'Đang theo học chương trình Công nghệ Thông tin. Các môn học chính: Lập trình Web, Cơ sở Dữ liệu, Mạng Máy tính, Triển khai và Quản trị Hệ thống.'
);

-- Kinh nghiệm mẫu
INSERT INTO `experiences` (`company`, `position`, `start_date`, `end_date`, `description`, `location`) VALUES
(
    'Công ty ABC (Thực tập)',
    'Thực tập sinh Web Developer',
    '2024-07-01',
    '2024-09-30',
    'Tham gia phát triển tính năng cho hệ thống quản lý nội bộ. Sử dụng PHP, MySQL và giao diện với Bootstrap.',
    'TP. Hồ Chí Minh'
);

-- =============================================
-- TÀI KHOẢN ADMIN (MẬT KHẨU SẼ ĐƯỢC THAY QUA SCRIPT)
-- =============================================
-- Mật khẩu sẽ được hash và thêm bởi PHP seed script
-- Xem: scripts/create-admin.php

-- =============================================
-- TÀI KHOẢN MySQL EXPORTER
-- =============================================
-- Tài khoản này được tạo bởi 02-create-exporter.sql

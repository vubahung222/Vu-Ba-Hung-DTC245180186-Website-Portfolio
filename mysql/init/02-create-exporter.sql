-- =============================================
-- TẠO TÀI KHOẢN MySQL EXPORTER (Prometheus)
-- Quyền tối thiểu để giám sát
-- Lưu ý: Mật khẩu phải khớp với monitoring/mysql-exporter.cnf
-- =============================================

-- Tạo user exporter với quyền tối thiểu
-- Mật khẩu 'exporter_password_change_me' phải trùng với file monitoring/mysql-exporter.cnf
CREATE USER IF NOT EXISTS 'exporter'@'%' IDENTIFIED BY 'exporter_password_change_me';

-- Quyền cần thiết cho mysqld_exporter
GRANT PROCESS, REPLICATION CLIENT, SELECT ON *.* TO 'exporter'@'%';
GRANT SELECT ON performance_schema.* TO 'exporter'@'%';

-- Quyền đọc performance_schema tables
GRANT SELECT ON performance_schema.replication_group_members TO 'exporter'@'%';
GRANT SELECT ON performance_schema.replication_group_member_stats TO 'exporter'@'%';
GRANT SELECT ON performance_schema.replication_applier_status_by_worker TO 'exporter'@'%';

FLUSH PRIVILEGES;

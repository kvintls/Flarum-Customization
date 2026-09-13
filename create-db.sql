-- 为 Flarum 论坛创建独立数据库和用户（复用 halo-mysql，与 Halo 博客数据隔离）
CREATE DATABASE IF NOT EXISTS flarum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'flarum'@'%' IDENTIFIED WITH mysql_native_password BY 'QhnPvBrWzwxzaVhm4sfbFmN6';
GRANT ALL PRIVILEGES ON flarum.* TO 'flarum'@'%';
FLUSH PRIVILEGES;

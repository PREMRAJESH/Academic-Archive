-- ============================================================
-- Nexus: Research Paper & Book Publication Management System
-- Database: nexus_db
-- Run this in phpMyAdmin or MySQL CLI before launching the app
-- ============================================================

CREATE DATABASE IF NOT EXISTS nexus_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nexus_db;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150)        NOT NULL,
    email       VARCHAR(200)        NOT NULL UNIQUE,
    password    VARCHAR(255)        NOT NULL,
    role        ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at  DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: papers
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS papers (
    id          INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT(11) UNSIGNED    NOT NULL,
    title       VARCHAR(300)        NOT NULL,
    abstract    TEXT                NOT NULL,
    keywords    VARCHAR(500)        DEFAULT NULL,
    category    VARCHAR(100)        DEFAULT NULL,
    file_name   VARCHAR(255)        NOT NULL,
    file_path   VARCHAR(500)        NOT NULL,
    status      ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at  DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_papers_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Seed: Default Admin Account
-- Email   : admin@nexus.com
-- Password: admin123
-- (hashed using PHP password_hash with PASSWORD_BCRYPT)
-- --------------------------------------------------------
INSERT INTO users (name, email, password, role) VALUES
(
    'System Admin',
    'admin@nexus.com',
    '$2y$10$oBInbefwF8n.5saWDmQMPOdNQ.iI54m2klqVdfLsL6v2cG.Y33d',
    'admin'
);

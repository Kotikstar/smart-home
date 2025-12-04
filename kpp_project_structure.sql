-- Полная структура базы данных для проекта пропусков
CREATE DATABASE IF NOT EXISTS kpp;
USE kpp;

DROP TABLE IF EXISTS passes;
DROP TABLE IF EXISTS user_permissions;

CREATE TABLE passes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_name VARCHAR(100) NOT NULL,
    license_plate VARCHAR(20) NOT NULL,
    car_brand VARCHAR(100),
    comment TEXT,
    pass_type VARCHAR(20) NOT NULL,
    start_time DATETIME,
    end_time DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS webauthn_credentials;
CREATE TABLE webauthn_credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    credential_id VARBINARY(255) NOT NULL UNIQUE,
    public_key TEXT NOT NULL,
    sign_count INT UNSIGNED DEFAULT 0,
    algorithm INT DEFAULT NULL,
    transports VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE user_permissions (
    user_id INT PRIMARY KEY,
    is_admin TINYINT(1) DEFAULT 0,
    can_dashboard TINYINT(1) DEFAULT 0,
    can_fuel TINYINT(1) DEFAULT 0,
    can_cards TINYINT(1) DEFAULT 0,
    can_dispense TINYINT(1) DEFAULT 0,
    can_logs TINYINT(1) DEFAULT 0,
    can_diesel TINYINT(1) DEFAULT 0,
    can_passes TINYINT(1) DEFAULT 0,
    can_service TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Пример данных
INSERT INTO passes (owner_name, license_plate, pass_type)
VALUES 
('Иван Иванов', 'AAA123', 'permanent'),
('Петр Петров', 'BBB456', 'temporary');

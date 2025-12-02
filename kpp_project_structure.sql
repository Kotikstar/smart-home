-- Полная структура базы данных для проекта пропусков
CREATE DATABASE IF NOT EXISTS kpp;
USE kpp;

DROP TABLE IF EXISTS passes;

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

-- Пример данных
INSERT INTO passes (owner_name, license_plate, pass_type)
VALUES 
('Иван Иванов', 'AAA123', 'permanent'),
('Петр Петров', 'BBB456', 'temporary');

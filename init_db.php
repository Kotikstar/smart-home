<?php
$host = 'MySQL-8.4';
$user = 'root';
$password = '';
$dbname = 'trk_system_test_main';

// Подключение к MySQL
$conn = new mysqli($host, $user, $password);
if ($conn->connect_error) {
    die("❌ Ошибка подключения: " . $conn->connect_error);
}

// Создание базы данных
$conn->query("DROP DATABASE IF EXISTS `$dbname`");
$conn->query("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
$conn->select_db($dbname);

// SQL-команды
$queries = [

    // Таблица cards
    "CREATE TABLE cards (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100),
        identifier VARCHAR(100) UNIQUE,
        fuel_limit FLOAT DEFAULT 0,
        used FLOAT DEFAULT 0
    )",

    // Таблица diesel_prices
    "CREATE TABLE diesel_prices (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        date DATE NOT NULL UNIQUE,
        price DECIMAL(5,2) NOT NULL
    )",

    // Таблица fuel
    "CREATE TABLE fuel (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        amount FLOAT NOT NULL DEFAULT 0,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        alert_flag TINYINT(1) NOT NULL DEFAULT 0
    )",

    // Таблица logs
    "CREATE TABLE logs (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        card_id INT DEFAULT NULL,
        amount FLOAT DEFAULT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        type ENUM('dispense','refill') NOT NULL DEFAULT 'dispense',
        FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE SET NULL
    )",

    // Таблица maintenance
    "CREATE TABLE maintenance (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        filter_type ENUM('coarse','fine') NOT NULL,
        last_service DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        status ENUM('ready','in_service') NOT NULL DEFAULT 'ready',
        liters_since_service FLOAT NOT NULL DEFAULT 0
    )",

    // Таблица service
    "CREATE TABLE service (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        type ENUM('coarse','fine') NOT NULL,
        status ENUM('ok','in_service') NOT NULL DEFAULT 'ok',
        last_serviced DATETIME DEFAULT NULL,
        liters_at_service FLOAT DEFAULT 0,
        interval_liters FLOAT DEFAULT 1000
    )",

    // Таблица settings
    "CREATE TABLE settings (
        `key` VARCHAR(50) NOT NULL PRIMARY KEY,
        `value` TEXT
    )",

    // Начальные данные
    "INSERT INTO fuel (amount) VALUES (10000)",
    "INSERT INTO settings (`key`, `value`) VALUES ('low_fuel_alert_sent', '0')",
    "INSERT INTO service (type, status, last_serviced, liters_at_service, interval_liters) VALUES 
        ('coarse', 'ok', NOW(), 0, 1000),
        ('fine', 'ok', NOW(), 0, 1000)",
    "INSERT INTO maintenance (filter_type, status, last_service, liters_since_service) VALUES 
        ('coarse', 'ready', NOW(), 0),
        ('fine', 'ready', NOW(), 0)"
];

// Выполнение SQL
foreach ($queries as $q) {
    if (!$conn->query($q)) {
        echo "<p style='color:red;'>⚠️ Ошибка: " . $conn->error . "<br><code>$q</code></p>";
    }
}

echo "<h2 style='color:green;'>✅ База данных '$dbname' успешно создана и инициализирована.</h2>";
?>

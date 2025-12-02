<?php
$host = 'MySQL-8.4';
$user = 'root';
$password = '';
$dbname = 'trk_system_test_main';


$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}
?>

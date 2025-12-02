<?php
require 'db.php';

header('Content-Type: text/plain');

if (!isset($_GET['plate'])) {
    echo "0";
    exit;
}

$plate = preg_replace('/\s+/', '', $_GET['plate']); // Убираем пробелы

$stmt = $pdo->prepare("SELECT COUNT(*) FROM passes WHERE license_plate = ? AND (pass_type = 'permanent' OR (pass_type = 'temporary' AND end_time > NOW()))");
$stmt->execute([$plate]);
$exists = $stmt->fetchColumn();

echo $exists ? "1" : "0";


//http://web.local/api.php?plate=AEN485
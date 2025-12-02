<?php
require "db.php";

$query = isset($_GET["query"]) ? strtoupper(str_replace(" ", "", $_GET["query"])) : "";
$passes = $query ? $pdo->prepare("SELECT * FROM passes WHERE license_plate = ?") : null;
if ($passes) $passes->execute([$query]);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поиск пропуска</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2><i class="fa-solid fa-search"></i> Поиск пропуска</h2>
        <form method="get">
            <div class="input-group">
                <input type="text" class="form-control" name="query" placeholder="Введите номер" required>
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-search"></i> Искать</button>
            </div>
        </form>

        <?php if ($passes && $passes->rowCount() > 0): ?>
            <h3 class="mt-4">Результаты</h3>
            <ul class="list-group">
                <?php foreach ($passes->fetchAll() as $p): ?>
                    <li class="list-group-item">
                        <strong><?= htmlspecialchars($p["owner_name"]) ?></strong> (<?= htmlspecialchars($p["license_plate"]) ?>) - <?= $p["pass_type"] ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php elseif ($query): ?>
            <p class="mt-4 text-danger">Пропуск не найден</p>
        <?php endif; ?>
    </div>
</body>
</html>

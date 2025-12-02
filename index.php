<?php
require "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $owner_name = $_POST["owner_name"];
    $license_plate = strtoupper(str_replace(" ", "", $_POST["license_plate"]));
    $pass_type = $_POST["pass_type"];
    
    $car_brand = $comment = $start_time = $end_time = NULL;
    if ($pass_type == "temporary") {
        $car_brand = $_POST["car_brand"];
        $comment = $_POST["comment"];
        $start_time = $_POST["start_time"];
        $end_time = $_POST["end_time"];
    }

    $stmt = $pdo->prepare("INSERT INTO passes (owner_name, license_plate, car_brand, comment, pass_type, start_time, end_time) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$owner_name, $license_plate, $car_brand, $comment, $pass_type, $start_time, $end_time]);

    header("Location: index.php");
    exit;
}

$passes = $pdo->query("SELECT * FROM passes")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пропуска - Админка</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="container">
        <h2><i class="fa-solid fa-car"></i> Управление пропусками</h2>

        <form method="post">
            <div class="mb-3">
                <input type="text" class="form-control" name="owner_name" placeholder="Имя и фамилия" required>
            </div>
            <div class="mb-3">
                <input type="text" class="form-control" name="license_plate" placeholder="Госномер (AAA 000)" required>
            </div>
            <div class="mb-3">
                <select class="form-select" name="pass_type" id="pass_type">
                    <option value="permanent">Постоянный</option>
                    <option value="temporary">Временный</option>
                </select>
            </div>

            <div id="temp_fields" style="display: none;">
                <div class="mb-3">
                    <input type="text" class="form-control" name="car_brand" placeholder="Марка авто">
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="comment" placeholder="Комментарий">
                </div>
                <div class="mb-3">
                    <label>Начало:</label>
                    <input type="datetime-local" class="form-control" name="start_time">
                </div>
                <div class="mb-3">
                    <label>Конец:</label>
                    <input type="datetime-local" class="form-control" name="end_time">
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Добавить</button>
        </form>

        <h2 class="mt-4"><i class="fa-solid fa-list"></i> Список пропусков</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Владелец</th><th>Госномер</th><th>Тип</th><th>Детали</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($passes as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p["owner_name"]) ?></td>
                        <td><?= htmlspecialchars($p["license_plate"]) ?></td>
                        <td><?= $p["pass_type"] == "permanent" ? "Постоянный" : "Временный" ?></td>
                        <td>
                            <?php if ($p["pass_type"] == "temporary"): ?>
                                <?= $p["start_time"] ?> - <?= $p["end_time"] ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    document.getElementById("pass_type").addEventListener("change", function() {
        document.getElementById("temp_fields").style.display = this.value === "temporary" ? "block" : "none";
    });
    </script>
</body>
</html>

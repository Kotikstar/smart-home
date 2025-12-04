<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner_name = trim($_POST['owner_name'] ?? '');
    $license_plate = strtoupper(str_replace(' ', '', $_POST['license_plate'] ?? ''));
    $pass_type = $_POST['pass_type'] ?? 'permanent';

    $car_brand = $_POST['car_brand'] ?? null;
    $comment = $_POST['comment'] ?? null;
    $start_time = $_POST['start_time'] ?? null;
    $end_time = $_POST['end_time'] ?? null;

    $stmt = $pdo->prepare(
        'INSERT INTO passes (owner_name, license_plate, car_brand, comment, pass_type, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$owner_name, $license_plate, $car_brand, $comment, $pass_type, $start_time, $end_time]);

    header('Location: passes.php');
    exit;
}

$passes = $pdo->query('SELECT * FROM passes ORDER BY id DESC')->fetchAll();
?>
<?php
define('REQUIRED_PERMISSION', 'passes');
include 'header.php';
?>
<main class="max-w-6xl mx-auto px-4 py-8 space-y-8">
  <section class="bg-gray-800 rounded-xl shadow p-6">
    <h2 class="text-2xl font-bold mb-4">Управление пропусками</h2>
    <form method="post" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <label class="block">
          <span class="text-sm text-gray-300">Имя и фамилия</span>
          <input type="text" name="owner_name" required class="mt-1 w-full p-3 rounded bg-gray-700 border border-gray-600" placeholder="Иван Иванов">
        </label>
        <label class="block">
          <span class="text-sm text-gray-300">Госномер</span>
          <input type="text" name="license_plate" required class="mt-1 w-full p-3 rounded bg-gray-700 border border-gray-600" placeholder="AAA000">
        </label>
      </div>

      <label class="block">
        <span class="text-sm text-gray-300">Тип пропуска</span>
        <select name="pass_type" id="pass_type" class="mt-1 w-full p-3 rounded bg-gray-700 border border-gray-600">
          <option value="permanent">Постоянный</option>
          <option value="temporary">Временный</option>
        </select>
      </label>

      <div id="temp_fields" class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
        <label class="block">
          <span class="text-sm text-gray-300">Марка авто</span>
          <input type="text" name="car_brand" class="mt-1 w-full p-3 rounded bg-gray-700 border border-gray-600" placeholder="Audi">
        </label>
        <label class="block">
          <span class="text-sm text-gray-300">Комментарий</span>
          <input type="text" name="comment" class="mt-1 w-full p-3 rounded bg-gray-700 border border-gray-600" placeholder="Только будни">
        </label>
        <label class="block">
          <span class="text-sm text-gray-300">Начало действия</span>
          <input type="datetime-local" name="start_time" class="mt-1 w-full p-3 rounded bg-gray-700 border border-gray-600">
        </label>
        <label class="block">
          <span class="text-sm text-gray-300">Окончание действия</span>
          <input type="datetime-local" name="end_time" class="mt-1 w-full p-3 rounded bg-gray-700 border border-gray-600">
        </label>
      </div>

      <button type="submit" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded">Добавить пропуск</button>
    </form>
  </section>

  <section class="bg-gray-800 rounded-xl shadow p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-2xl font-bold">Список пропусков</h2>
      <span class="text-sm text-gray-400">Всего: <?= count($passes) ?></span>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-left text-sm">
        <thead class="bg-gray-700 text-gray-200">
          <tr>
            <th class="px-4 py-2">Владелец</th>
            <th class="px-4 py-2">Госномер</th>
            <th class="px-4 py-2">Тип</th>
            <th class="px-4 py-2">Детали</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
          <?php foreach ($passes as $p): ?>
            <tr class="hover:bg-gray-700/70">
              <td class="px-4 py-2"><?= htmlspecialchars($p['owner_name']) ?></td>
              <td class="px-4 py-2 font-mono tracking-wide"><?= htmlspecialchars($p['license_plate']) ?></td>
              <td class="px-4 py-2"><?= $p['pass_type'] === 'permanent' ? 'Постоянный' : 'Временный' ?></td>
              <td class="px-4 py-2 text-gray-300">
                <?php if ($p['pass_type'] === 'temporary'): ?>
                  <?= htmlspecialchars($p['start_time'] ?: '--') ?> — <?= htmlspecialchars($p['end_time'] ?: '--') ?>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
<script>
  const typeSelect = document.getElementById('pass_type');
  const tempFields = document.getElementById('temp_fields');
  typeSelect.addEventListener('change', () => {
    const isTemp = typeSelect.value === 'temporary';
    tempFields.classList.toggle('hidden', !isTemp);
  });
</script>
<?php include 'footer.php'; ?>

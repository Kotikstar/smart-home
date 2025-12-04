<?php
require 'db.php';

$query = isset($_GET['query']) ? strtoupper(str_replace(' ', '', $_GET['query'])) : '';
$passes = $query ? $pdo->prepare('SELECT * FROM passes WHERE license_plate = ?') : null;
if ($passes) {
    $passes->execute([$query]);
}
?>
<?php
define('REQUIRED_PERMISSION', 'passes');
include 'header.php';
?>
<main class="max-w-4xl mx-auto px-4 py-8 space-y-6">
  <section class="bg-gray-800 rounded-xl shadow p-6">
    <h2 class="text-2xl font-bold mb-4">Поиск пропуска</h2>
    <form method="get" class="flex flex-col md:flex-row gap-4">
      <input type="text" name="query" value="<?= htmlspecialchars($query) ?>" required placeholder="Введите номер"
             class="flex-1 p-3 rounded bg-gray-700 border border-gray-600">
      <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded">Искать</button>
    </form>
  </section>

  <?php if ($passes && $passes->rowCount() > 0): ?>
    <section class="bg-gray-800 rounded-xl shadow p-6">
      <h3 class="text-xl font-semibold mb-3">Результаты</h3>
      <ul class="space-y-3">
        <?php foreach ($passes->fetchAll() as $p): ?>
          <li class="bg-gray-700 px-4 py-3 rounded flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
              <p class="font-semibold text-lg"><?= htmlspecialchars($p['owner_name']) ?></p>
              <p class="text-gray-300">Тип: <?= $p['pass_type'] === 'permanent' ? 'Постоянный' : 'Временный' ?></p>
            </div>
            <div class="text-right mt-2 sm:mt-0">
              <p class="font-mono text-xl tracking-wide"><?= htmlspecialchars($p['license_plate']) ?></p>
              <?php if ($p['pass_type'] === 'temporary'): ?>
                <p class="text-gray-300 text-sm"><?= htmlspecialchars($p['start_time'] ?: '--') ?> — <?= htmlspecialchars($p['end_time'] ?: '--') ?></p>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>
  <?php elseif ($query): ?>
    <section class="bg-red-900/50 border border-red-700 rounded-xl shadow p-6">
      <p class="text-red-200 font-semibold">Пропуск не найден</p>
    </section>
  <?php endif; ?>
</main>
<?php include 'footer.php'; ?>

<?php
require 'db.php';

$total_passes = (int)$pdo->query('SELECT COUNT(*) FROM passes')->fetchColumn();
$permanent_passes = (int)$pdo->query("SELECT COUNT(*) FROM passes WHERE pass_type = 'permanent'")->fetchColumn();
$active_temporary = (int)$pdo->query("SELECT COUNT(*) FROM passes WHERE pass_type = 'temporary' AND end_time > NOW()")->fetchColumn();
$recent_passes = $pdo->query('SELECT * FROM passes ORDER BY id DESC LIMIT 5')->fetchAll();
?>
<?php include 'header.php'; ?>
<main class="max-w-7xl mx-auto px-4 py-10 space-y-10">
  <section class="bg-gradient-to-r from-blue-700 to-purple-700 rounded-2xl p-8 shadow-xl text-white">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
      <div class="space-y-3">
        <p class="text-sm uppercase tracking-wide text-blue-100">Единый портал</p>
        <h1 class="text-3xl md:text-4xl font-extrabold">Топливо, карты и пропуска в одном месте</h1>
        <p class="text-blue-100 max-w-3xl">
          Следите за остатками топлива, лимитами карт, выдачей, обслуживанием фильтров и статусами пропусков с одного экрана.
          Используйте карточки ниже, чтобы перейти к управлению или выполнить быстрые действия.
        </p>
      </div>
      <div class="bg-white/10 backdrop-blur rounded-xl p-6 text-center space-y-2 shadow-lg">
        <p class="text-sm text-blue-100">Всего пропусков</p>
        <p class="text-4xl font-black"><?php echo $total_passes; ?></p>
        <div class="text-sm text-blue-100">Постоянные: <?php echo $permanent_passes; ?> · Временные активные: <?php echo $active_temporary; ?></div>
      </div>
    </div>
    <div class="mt-6 flex flex-wrap gap-3">
      <a href="passes.php" class="bg-white text-gray-900 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100">Управление пропусками</a>
      <a href="search.php" class="bg-white/20 px-4 py-2 rounded-lg font-semibold hover:bg-white/30">Поиск пропуска</a>
      <a href="dashboard.php" class="bg-white/20 px-4 py-2 rounded-lg font-semibold hover:bg-white/30">Панель ТРК</a>
      <a href="fuel.php" class="bg-white/20 px-4 py-2 rounded-lg font-semibold hover:bg-white/30">Топливный склад</a>
      <a href="cards.php" class="bg-white/20 px-4 py-2 rounded-lg font-semibold hover:bg-white/30">Карты и лимиты</a>
    </div>
  </section>

  <section>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
      <div class="bg-gray-800 rounded-xl p-5 shadow flex flex-col gap-2">
        <p class="text-sm text-gray-400">Остаток топлива</p>
        <p id="fuelAmount" class="text-3xl font-bold">-- л</p>
        <a href="fuel.php" class="text-blue-400 text-sm hover:underline">Пополнить или списать</a>
      </div>
      <div class="bg-gray-800 rounded-xl p-5 shadow flex flex-col gap-2">
        <p class="text-sm text-gray-400">Активных карт</p>
        <p id="cardCount" class="text-3xl font-bold">--</p>
        <a href="cards.php" class="text-blue-400 text-sm hover:underline">Управление картами</a>
      </div>
      <div class="bg-gray-800 rounded-xl p-5 shadow flex flex-col gap-2">
        <p class="text-sm text-gray-400">Всего выдано</p>
        <p id="fuelDispensed" class="text-3xl font-bold">-- л</p>
        <a href="dispense.php" class="text-blue-400 text-sm hover:underline">Выдать по карте</a>
      </div>
      <div class="bg-gray-800 rounded-xl p-5 shadow flex flex-col gap-2">
        <p class="text-sm text-gray-400">Всего пополнено</p>
        <p id="fuelRefilled" class="text-3xl font-bold">-- л</p>
        <a href="logs.php" class="text-blue-400 text-sm hover:underline">Посмотреть журнал</a>
      </div>
      <div class="bg-gray-800 rounded-xl p-5 shadow flex flex-col gap-2">
        <p class="text-sm text-gray-400">Самая активная карта</p>
        <p id="topCard" class="text-lg font-semibold leading-tight">--</p>
        <a href="cards.php" class="text-blue-400 text-sm hover:underline">Открыть список карт</a>
      </div>
    </div>
  </section>

  <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-gray-800 rounded-xl p-6 shadow space-y-4">
      <div>
        <h2 class="text-xl font-bold">Быстрая проверка пропуска</h2>
        <p class="text-gray-400 text-sm">Введите госномер без пробелов, чтобы убедиться в наличии действующего пропуска.</p>
      </div>
      <form id="quickCheck" class="space-y-3">
        <input type="text" name="plate" required class="w-full p-3 rounded bg-gray-700 border border-gray-600" placeholder="AA000AA">
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 font-semibold px-4 py-3 rounded">Проверить</button>
      </form>
      <div id="checkResult" class="text-sm"></div>
    </div>

    <div class="bg-gray-800 rounded-xl p-6 shadow space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold">Последние пропуска</h2>
        <a href="passes.php" class="text-blue-400 text-sm hover:underline">Все пропуска</a>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-700 text-gray-200">
            <tr>
              <th class="px-3 py-2 text-left">Владелец</th>
              <th class="px-3 py-2 text-left">Госномер</th>
              <th class="px-3 py-2 text-left">Тип</th>
              <th class="px-3 py-2 text-left">Интервал</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-700">
            <?php foreach ($recent_passes as $p): ?>
              <tr>
                <td class="px-3 py-2"><?= htmlspecialchars($p['owner_name']) ?></td>
                <td class="px-3 py-2 font-mono tracking-wide"><?= htmlspecialchars($p['license_plate']) ?></td>
                <td class="px-3 py-2"><?php echo $p['pass_type'] === 'permanent' ? 'Постоянный' : 'Временный'; ?></td>
                <td class="px-3 py-2 text-gray-300">
                  <?php if ($p['pass_type'] === 'temporary'): ?>
                    <?php echo htmlspecialchars($p['start_time'] ?: '--'); ?> — <?php echo htmlspecialchars($p['end_time'] ?: '--'); ?>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (count($recent_passes) === 0): ?>
              <tr><td colspan="4" class="px-3 py-3 text-center text-gray-400">Пока нет записей</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <section class="bg-gray-800 rounded-xl p-6 shadow space-y-4">
    <h2 class="text-xl font-bold">Быстрые разделы</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <a href="diesel_price.php" class="block bg-gray-700 hover:bg-gray-600 rounded-lg p-4">
        <p class="font-semibold">Цены на дизель</p>
        <p class="text-gray-400 text-sm">Актуальное значение и история цен.</p>
      </a>
      <a href="dispense.php" class="block bg-gray-700 hover:bg-gray-600 rounded-lg p-4">
        <p class="font-semibold">Выдача топлива</p>
        <p class="text-gray-400 text-sm">Выдача по карте с проверками лимитов.</p>
      </a>
      <a href="logs.php" class="block bg-gray-700 hover:bg-gray-600 rounded-lg p-4">
        <p class="font-semibold">Журнал операций</p>
        <p class="text-gray-400 text-sm">История выдач и пополнений.</p>
      </a>
      <a href="fuel.php" class="block bg-gray-700 hover:bg-gray-600 rounded-lg p-4">
        <p class="font-semibold">Склад топлива</p>
        <p class="text-gray-400 text-sm">Пополнения и корректировки остатков.</p>
      </a>
      <a href="cards.php" class="block bg-gray-700 hover:bg-gray-600 rounded-lg p-4">
        <p class="font-semibold">Топливные карты</p>
        <p class="text-gray-400 text-sm">Создание, пополнение и удаление карт.</p>
      </a>
      <a href="dashboard.php" class="block bg-gray-700 hover:bg-gray-600 rounded-lg p-4">
        <p class="font-semibold">Панель ТРК</p>
        <p class="text-gray-400 text-sm">Графики, сервис фильтров и сводки.</p>
      </a>
    </div>
  </section>

  <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-gray-800 rounded-xl p-6 shadow space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold">Обслуживание фильтров</h2>
        <a href="dashboard.php" class="text-blue-400 text-sm hover:underline">Открыть панель</a>
      </div>
      <div id="serviceCards" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <p class="text-gray-400">Загрузка данных...</p>
      </div>
    </div>

    <div class="bg-gray-800 rounded-xl p-6 shadow space-y-3">
      <h2 class="text-xl font-bold">Последняя цена на дизель</h2>
      <p class="text-sm text-gray-400">Быстрый просмотр без перехода в раздел графика.</p>
      <div class="bg-gray-700 rounded-lg p-4 flex items-center justify-between">
        <div>
          <p class="text-2xl font-bold" id="dieselPrice">--</p>
          <p class="text-gray-300" id="dieselDate">--</p>
        </div>
        <a href="diesel_price.php" class="text-blue-400 text-sm hover:underline">Подробнее</a>
      </div>
    </div>
  </section>
</main>

<script>
fetch('api.php?resource=fuel')
  .then(r => r.json())
  .then(d => document.getElementById('fuelAmount').innerText = (d.amount ?? '--') + ' л');

fetch('api.php?resource=cards')
  .then(r => r.json())
  .then(d => document.getElementById('cardCount').innerText = Array.isArray(d) ? d.length : '--');

fetch('api.php?resource=stats')
  .then(r => r.json())
  .then(d => {
    document.getElementById('fuelDispensed').innerText = (d.dispensed ?? '--') + ' л';
    document.getElementById('fuelRefilled').innerText = (d.refilled ?? '--') + ' л';
    document.getElementById('topCard').innerText = d.top_card || 'Нет данных';
  });

fetch('api.php?resource=service')
  .then(r => r.json())
  .then(data => {
    const container = document.getElementById('serviceCards');
    container.innerHTML = '';
    Object.entries(data).forEach(([type, info]) => {
      const name = type === 'coarse' ? 'Грубая очистка' : 'Тонкая очистка';
      const overLimit = info.elapsed >= info.interval;
      const badge = info.status === 'in_service'
        ? '<span class="text-yellow-200 text-sm">В обслуживании</span>'
        : overLimit
          ? '<span class="text-red-200 text-sm">Требует обслуживания</span>'
          : '<span class="text-green-200 text-sm">В норме</span>';
      const card = document.createElement('div');
      card.className = 'bg-gray-700 rounded-lg p-4 flex flex-col gap-2';
      card.innerHTML = `
        <div class="flex items-center justify-between">
          <p class="font-semibold">${name}</p>
          ${badge}
        </div>
        <p class="text-sm text-gray-300">Последнее обслуживание: ${info.last_service || '--'}</p>
        <p class="text-sm text-gray-300">Прошло: ${info.elapsed} л из ${info.interval} л</p>
      `;
      container.appendChild(card);
    });
  })
  .catch(() => {
    const container = document.getElementById('serviceCards');
    container.innerHTML = '<p class="text-red-300">Не удалось загрузить данные об обслуживании</p>';
  });

fetch('api.php?resource=diesel_prices')
  .then(r => r.json())
  .then(data => {
    const latest = Array.isArray(data) && data.length ? data[data.length - 1] : null;
    document.getElementById('dieselPrice').innerText = latest ? `${latest.price} EUR/л` : 'Нет данных';
    document.getElementById('dieselDate').innerText = latest ? latest.date : '--';
  });

const form = document.getElementById('quickCheck');
const resultBox = document.getElementById('checkResult');
form.addEventListener('submit', (e) => {
  e.preventDefault();
  const formData = new FormData(form);
  const plate = (formData.get('plate') || '').toString().replace(/\s+/g, '');
  if (!plate) return;
  resultBox.textContent = 'Проверяем...';
  fetch(`api.php?plate=${encodeURIComponent(plate)}`)
    .then(r => r.text())
    .then(text => {
      const hasPass = text.trim() === '1';
      resultBox.innerHTML = hasPass
        ? '<span class="text-green-400 font-semibold">Есть действующий пропуск</span>'
        : '<span class="text-red-400 font-semibold">Пропуск не найден</span>';
    })
    .catch(() => {
      resultBox.innerHTML = '<span class="text-red-400">Ошибка проверки</span>';
    });
});
</script>
<?php include 'footer.php'; ?>

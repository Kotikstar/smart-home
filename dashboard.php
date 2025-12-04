<?php
define('REQUIRED_PERMISSION', 'dashboard');
include 'header.php';
?>
<div class="max-w-6xl mx-auto px-4 py-6">
  <h2 class="text-2xl font-bold text-white mb-6">Панель управления</h2>

  <!-- Основные карточки -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="bg-blue-600 text-white p-6 rounded-xl shadow">
      <h3 class="text-lg font-semibold">Остаток топлива</h3>
      <p class="text-3xl mt-2" id="fuelAmount">-- л</p>
    </div>
    <div class="bg-green-600 text-white p-6 rounded-xl shadow">
      <h3 class="text-lg font-semibold">Активных карт</h3>
      <p class="text-3xl mt-2" id="cardCount">--</p>
    </div>
    <div class="bg-yellow-600 text-white p-6 rounded-xl shadow">
      <h3 class="text-lg font-semibold">Всего выдано</h3>
      <p class="text-3xl mt-2" id="fuelDispensed">-- л</p>
    </div>
    <div class="bg-purple-600 text-white p-6 rounded-xl shadow">
      <h3 class="text-lg font-semibold">Всего пополнено</h3>
      <p class="text-3xl mt-2" id="fuelRefilled">-- л</p>
    </div>
    <div class="bg-indigo-600 text-white p-6 rounded-xl shadow md:col-span-2 lg:col-span-1">
      <h3 class="text-lg font-semibold">Самая активная карта</h3>
      <p class="text-xl mt-2" id="topCard">--</p>
    </div>
    <div class="bg-red-600 text-white p-6 rounded-xl shadow">
      <h3 class="text-lg font-semibold">Статус насоса</h3>
      <p class="text-3xl mt-2" id="pumpStatus">—</p>
    </div>
    <div class="bg-pink-600 text-white p-6 rounded-xl shadow">
      <h3 class="text-lg font-semibold">Температура топлива</h3>
      <p class="text-3xl mt-2" id="fuelTemp">-- °C</p>
    </div>
  </div>

  <!-- Обслуживание фильтров -->
  <div class="mt-10 bg-gray-800 p-6 rounded-xl shadow text-white">
    <h3 class="text-lg font-semibold mb-4">Обслуживание фильтров</h3>
    <div class="flex flex-col md:flex-row gap-6" id="serviceStatus">
      <!-- Карточки фильтров добавляются через JS -->
    </div>
  </div>

  <!-- График -->
  <div class="mt-10 bg-gray-800 p-6 rounded-xl shadow text-white">
    <h3 class="text-lg font-semibold mb-4">График выдачи и пополнения за 7 дней</h3>
    <canvas id="fuelChart" height="100"></canvas>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
fetch('/api.php?resource=fuel').then(r => r.json()).then(d => document.getElementById('fuelAmount').innerText = d.amount + " л");
fetch('/api.php?resource=cards').then(r => r.json()).then(d => document.getElementById('cardCount').innerText = d.length);
fetch('/api.php?resource=stats')
  .then(r => r.json())
  .then(d => {
    document.getElementById('fuelDispensed').innerText = d.dispensed + " л";
    document.getElementById('fuelRefilled').innerText = d.refilled + " л";
    document.getElementById('topCard').innerText = d.top_card || "Нет данных";
  });
fetch('/api.php?resource=chart_data')
  .then(r => r.json())
  .then(data => {
    const ctx = document.getElementById('fuelChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: data.labels,
        datasets: [
          { label: 'Выдано', data: data.dispense, backgroundColor: 'rgba(59, 130, 246, 0.7)' },
          { label: 'Пополнено', data: data.refill, backgroundColor: 'rgba(16, 185, 129, 0.7)' }
        ]
      },
      options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
  });

function loadServiceData() {
  fetch('/api.php?resource=service')
    .then(r => r.json())
    .then(data => {
      const container = document.getElementById('serviceStatus');
      container.innerHTML = '';
      for (const [type, info] of Object.entries(data)) {
        const name = type === 'coarse' ? 'Грубая очистка' : 'Тонкая очистка';
        const statusText = info.status === 'in_service' ? '🛠 Обслуживается' : '✅ В норме';
        const overLimit = info.elapsed >= info.interval;
        const cardColor = info.status === 'in_service' ? 'bg-red-600' : (overLimit ? 'bg-yellow-700' : 'bg-emerald-600');
        const warningText = overLimit && info.status !== 'in_service' ? `<p class="mt-2 text-red-300 font-semibold">❗ Требуется обслуживание!</p>` : '';

        const html = `
          <div class="${cardColor} rounded-xl p-4 flex flex-col flex-1">
            <h4 class="text-xl font-bold mb-2">${name}</h4>
            <p>${statusText}</p>
            <p>Последнее обслуживание: <span>${info.last_service || '--'}</span></p>
            <p>Прошло: <span>${info.elapsed || '--'}</span> л</p>
            <p>Интервал: <span>${info.interval || '--'}</span> л</p>
            ${warningText}
            <div class="mt-3 flex gap-2">
              <button class="start-btn bg-yellow-500 px-3 py-1 rounded" data-type="${type}">Начать</button>
              <button class="end-btn bg-white text-black px-3 py-1 rounded" data-type="${type}">Завершить</button>
            </div>
            <div class="mt-2">
              <label class="block text-sm mt-3">Интервал (л):</label>
              <input type="number" class="interval-input bg-gray-100 text-black px-2 py-1 rounded w-24" value="${info.interval}" />
              <button class="save-interval ml-2 bg-blue-500 px-3 py-1 rounded" data-type="${type}">Сохранить</button>
            </div>
          </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
      }
      bindServiceButtons();
    });
}

function bindServiceButtons() {
  document.querySelectorAll('.start-btn').forEach(btn => {
    btn.onclick = () => fetch(`/api.php?resource=service&action=start&type=${btn.dataset.type}`).then(() => loadServiceData());
  });
  document.querySelectorAll('.end-btn').forEach(btn => {
    btn.onclick = () => fetch(`/api.php?resource=service&action=end&type=${btn.dataset.type}`).then(() => loadServiceData());
  });
  document.querySelectorAll('.save-interval').forEach(btn => {
    btn.onclick = () => {
      const type = btn.dataset.type;
      const input = btn.parentElement.querySelector('.interval-input');
      const val = input.value;
      fetch(`/api.php?resource=service&action=set_interval&type=${type}&liters=${val}`).then(() => loadServiceData());
    };
  });
}

loadServiceData();
</script>

<?php include 'footer.php'; ?>

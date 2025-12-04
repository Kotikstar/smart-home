<?php
define('REQUIRED_PERMISSION', 'logs');
include 'header.php';
?>
<div class="max-w-6xl mx-auto px-4 py-6 text-white">
  <h2 class="text-2xl font-bold mb-4">Журнал выдачи топлива</h2>

  <div class="bg-gray-800 p-4 rounded-lg mb-4 space-y-2">
    <label class="block">
      Фильтр по ID карты:
      <input type="text" id="filterId" placeholder="например, CARD123"
             class="mt-1 w-full p-2 rounded bg-gray-700 border border-gray-600">
    </label>
    <label class="block">
      Фильтр по дате (от):
      <input type="date" id="filterFrom"
             class="mt-1 w-full p-2 rounded bg-gray-700 border border-gray-600">
    </label>
    <label class="block">
      Фильтр по дате (до):
      <input type="date" id="filterTo"
             class="mt-1 w-full p-2 rounded bg-gray-700 border border-gray-600">
    </label>
    <button onclick="loadLogs()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-2">
      Применить фильтр
    </button>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-left text-sm bg-gray-800 rounded-lg overflow-hidden">
      <thead class="bg-gray-700 text-white">
        <tr>
          <th class="py-3 px-4">Дата</th>
          <th class="py-3 px-4">Тип</th>
          <th class="py-3 px-4">Имя</th>
          <th class="py-3 px-4">ID карты</th>
          <th class="py-3 px-4">Объём (л)</th>
        </tr>
      </thead>
      <tbody id="logTable" class="divide-y divide-gray-600"></tbody>
    </table>
  </div>
</div>

<script>
function loadLogs() {
  const id = document.getElementById('filterId').value;
  const from = document.getElementById('filterFrom').value;
  const to = document.getElementById('filterTo').value;
  const params = new URLSearchParams({resource: 'logs'});
  if (id) params.append('identifier', id);
  if (from) params.append('from', from);
  if (to) params.append('to', to);

  fetch('/api.php?' + params.toString())
    .then(res => res.json())
    .then(logs => {
      const table = document.getElementById('logTable');
      table.innerHTML = '';
      logs.forEach(log => {
        const row = `<tr class="hover:bg-gray-700">
          <td class="py-2 px-4">${log.created_at}</td>
          <td class="py-2 px-4">${log.type === 'refill' ? 'Пополнение' : 'Выдача'}</td>
          <td class="py-2 px-4">${log.name || '-'}</td>
          <td class="py-2 px-4">${log.identifier || '-'}</td>
          <td class="py-2 px-4">${log.amount}</td>
        </tr>`;
        table.innerHTML += row;
      });
    });
}

loadLogs();
</script>
<?php include 'footer.php'; ?>

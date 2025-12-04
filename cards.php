<?php
define('REQUIRED_PERMISSION', 'cards');
include 'header.php';
?>
<div class="max-w-6xl mx-auto px-4 py-6">
  <h2 class="text-2xl font-bold text-white mb-6">Топливные карты</h2>

  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
    <input type="text" id="nameInput" placeholder="Имя" class="p-2 rounded bg-gray-700 text-white">
    <input type="text" id="identifierInput" placeholder="ID карты" class="p-2 rounded bg-gray-700 text-white">
    <input type="number" id="limitInput" placeholder="Лимит (л)" class="p-2 rounded bg-gray-700 text-white">
    <button id="addCardBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded px-4 py-2">Добавить карту</button>
  </div>

  <div class="overflow-x-auto rounded-xl">
    <table class="w-full table-auto text-white bg-gray-800 rounded">
      <thead class="bg-gray-700">
        <tr>
          <th class="p-2">ID</th>
          <th class="p-2">Имя</th>
          <th class="p-2">Идентификатор</th>
          <th class="p-2">Лимит</th>
          <th class="p-2">Использовано</th>
          <th class="p-2">Остаток</th>
          <th class="p-2">Пополнить</th>
          <th class="p-2">Удалить</th>
        </tr>
      </thead>
      <tbody id="cardTable" class="text-center"></tbody>
    </table>
  </div>
</div>

<script>
function loadCards() {
  fetch('/api.php?resource=cards')
    .then(res => res.json())
    .then(data => {
      const table = document.getElementById('cardTable');
      table.innerHTML = '';
      data.forEach(card => {
        const used = parseFloat(card.used || 0);
        const limit = parseFloat(card.fuel_limit || 0);
        const remaining = (limit - used).toFixed(2);

        const row = document.createElement('tr');
        row.innerHTML = `
          <td class="p-2">${card.id}</td>
          <td class="p-2">${card.name}</td>
          <td class="p-2">${card.identifier}</td>
          <td class="p-2">${limit}</td>
          <td class="p-2">${used}</td>
          <td class="p-2">${remaining}</td>
          <td class="p-2">
            <input type="number" id="topup-${card.id}" placeholder="л" class="w-20 p-1 text-black rounded mb-1">
            <button onclick="topupCard(${card.id})" class="bg-yellow-600 hover:bg-yellow-700 text-white px-2 py-1 rounded text-sm">+</button>
          </td>
          <td class="p-2">
            <button onclick="deleteCard(${card.id})" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">Удалить</button>
          </td>
        `;
        table.appendChild(row);
      });
    });
}

function deleteCard(id) {
  if (!confirm("Удалить карту?")) return;
  fetch(`/api.php?resource=cards&action=delete&id=${id}`)
    .then(res => res.json())
    .then(() => loadCards());
}

function topupCard(id) {
  const input = document.getElementById('topup-' + id);
  const value = parseFloat(input.value);
  if (isNaN(value) || value <= 0) return alert("Введите корректное значение");

  // FIX: правильный action — refill, не refill_card
  fetch(`/api.php?resource=cards&action=refill&id=${id}&amount=${value}`)
    .then(res => res.json())
    .then(() => loadCards());
}

document.getElementById('addCardBtn').addEventListener('click', () => {
  const name = document.getElementById('nameInput').value.trim();
  const identifier = document.getElementById('identifierInput').value.trim();
  const limit = parseFloat(document.getElementById('limitInput').value || 0);

  if (!name || !identifier) return alert("Имя и ID обязательны");

  fetch(`/api.php?resource=cards&action=add&name=${encodeURIComponent(name)}&identifier=${encodeURIComponent(identifier)}&limit=${limit}`)
    .then(res => res.json())
    .then(() => {
      document.getElementById('nameInput').value = '';
      document.getElementById('identifierInput').value = '';
      document.getElementById('limitInput').value = '';
      loadCards();
    });
});

loadCards();
</script>
<?php include 'footer.php'; ?>

<?php
define('REQUIRED_PERMISSION', 'dispense');
include 'header.php';
?>
<div class="max-w-2xl mx-auto px-4 py-6 text-white">
  <h2 class="text-2xl font-bold mb-4">Выдача топлива</h2>
  <form id="dispenseForm" class="bg-gray-800 p-6 rounded-lg shadow space-y-4">
    <div>
      <label for="identifier" class="block mb-1">ID карты:</label>
      <input type="text" id="identifier" name="identifier" required class="w-full p-2 rounded bg-gray-700 border border-gray-600">
    </div>
    <div>
      <label for="amount" class="block mb-1">Количество (л):</label>
      <input type="number" id="amount" name="amount" step="0.1" required class="w-full p-2 rounded bg-gray-700 border border-gray-600">
    </div>
    <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Выдать</button>
    <p id="result" class="mt-4 text-lg font-semibold"></p>
  </form>
</div>

<script>
document.getElementById('dispenseForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const identifier = document.getElementById('identifier').value.trim();
  const amount = parseFloat(document.getElementById('amount').value);

  if (!identifier || isNaN(amount) || amount <= 0) {
    const resEl = document.getElementById('result');
    resEl.innerText = "❌ Неверные данные.";
    resEl.className = "text-red-400";
    return;
  }

  fetch(`/api.php?resource=dispense&action=issue&identifier=${encodeURIComponent(identifier)}&amount=${amount}`)
    .then(res => res.json())
    .then(data => {
      const resEl = document.getElementById('result');
      if (data.success) {
        resEl.innerText = "✅ Успешно выдано " + amount + " л.";
        resEl.className = "text-green-400";
      } else {
        resEl.innerText = "❌ Ошибка: " + (data.message || "Неизвестная ошибка");
        resEl.className = "text-red-400";
      }
    })
    .catch(err => {
      const resEl = document.getElementById('result');
      resEl.innerText = "❌ Ошибка соединения с API.";
      resEl.className = "text-red-400";
    });
});
</script>
<?php include 'footer.php'; ?>

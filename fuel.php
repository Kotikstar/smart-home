<?php
define('REQUIRED_PERMISSION', 'fuel');
include 'header.php';
?>
<div class="max-w-3xl mx-auto px-4 py-6 text-white">
  <h2 class="text-2xl font-bold mb-4">Управление топливом</h2>
  <form method="get" action="/api.php" class="bg-gray-800 p-6 rounded-lg shadow">
    <input type="hidden" name="resource" value="fuel">
    <input type="hidden" name="action" value="update">
    <div class="mb-4">
      <label for="delta" class="block mb-2">Изменить количество (л):</label>
      <input type="number" step="0.1" name="delta" id="delta" required
        class="w-full p-2 rounded bg-gray-700 text-white border border-gray-600">
    </div>
    <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Сохранить</button>
  </form>
</div>
<?php include 'footer.php'; ?>

<?php include 'header.php'; ?>
<div class="max-w-4xl mx-auto px-4 py-6 text-white">
  <h2 class="text-2xl font-bold mb-6">📈 График цены на дизель</h2>

  <div class="bg-gray-800 p-6 rounded-lg shadow mb-6">
    <canvas id="dieselChart" height="100"></canvas>
  </div>

  <div id="latestPrice" class="text-lg bg-gray-700 p-4 rounded-lg shadow">
    <span class="font-semibold">Последняя цена:</span>
    <span id="latestDate">--.--</span> —
    <span id="latestValue">--</span> EUR/l
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
fetch('/api.php?resource=diesel_prices')
  .then(r => r.json())
  .then(data => {
    if (!Array.isArray(data) || data.length === 0) {
      document.getElementById('latestDate').innerText = '--';
      document.getElementById('latestValue').innerText = 'нет данных';
      return;
    }

    const labels = data.map(row => row.date);
    const prices = data.map(row => row.price);

    const ctx = document.getElementById('dieselChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'EUR / литр',
          data: prices,
          borderColor: 'rgb(59, 130, 246)',
          backgroundColor: 'rgba(59, 130, 246, 0.2)',
          tension: 0.2
        }]
      },
      options: {
        scales: {
          y: { beginAtZero: false }
        }
      }
    });

    const latest = data[data.length - 1];
    document.getElementById('latestDate').innerText = latest.date;
    document.getElementById('latestValue').innerText = latest.price;
  });
</script>
<?php include 'footer.php'; ?>

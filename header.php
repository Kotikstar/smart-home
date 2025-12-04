<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Смарт-дом: топливо и пропуска</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      color-scheme: dark;
    }
    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      background: radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.12), transparent 25%),
                  radial-gradient(circle at 80% 10%, rgba(16, 185, 129, 0.12), transparent 22%),
                  radial-gradient(circle at 50% 70%, rgba(14, 165, 233, 0.1), transparent 30%),
                  linear-gradient(180deg, #0b1220 0%, #05080f 55%, #05070d 100%);
      min-height: 100vh;
      overflow-x: hidden;
    }
    .cyber-grid {
      position: fixed;
      inset: 0;
      pointer-events: none;
      background-image:
        linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
      background-size: 120px 120px;
      mask-image: radial-gradient(circle at center, black 0%, transparent 75%);
      z-index: 0;
    }
    .glass-nav {
      backdrop-filter: blur(14px);
      background: rgba(17, 24, 39, 0.75);
      border: 1px solid rgba(255, 255, 255, 0.08);
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.45);
    }
    .nav-link {
      position: relative;
      padding: 0.35rem 0.8rem;
      border-radius: 9999px;
      transition: color 0.2s ease, transform 0.2s ease;
    }
    .nav-link::after {
      content: '';
      position: absolute;
      inset: 0;
      border-radius: 9999px;
      border: 1px solid rgba(59, 130, 246, 0.12);
      opacity: 0;
      transition: opacity 0.2s ease, transform 0.2s ease;
    }
    .nav-link:hover {
      color: #93c5fd;
      transform: translateY(-1px);
    }
    .nav-link:hover::after {
      opacity: 1;
      transform: scale(1.04);
    }
    .glow-cta {
      position: relative;
      overflow: hidden;
      isolation: isolate;
    }
    .glow-cta::before {
      content: '';
      position: absolute;
      inset: -120%;
      background: conic-gradient(from 90deg at 50% 50%, #0ea5e9, #22d3ee, #10b981, #6366f1, #0ea5e9);
      animation: rotate 12s linear infinite;
      opacity: 0.35;
      z-index: 0;
    }
    .glow-cta span {
      position: relative;
      z-index: 1;
    }
    @keyframes rotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
  </style>
</head>
<body class="text-white">
  <div class="cyber-grid"></div>
  <nav class="glass-nav p-4 sticky top-0 z-20">
    <div class="max-w-7xl mx-auto flex flex-wrap items-center gap-3 text-sm">
      <a href="all.php" class="nav-link font-semibold">Главная</a>
      <a href="dashboard.php" class="nav-link">Панель</a>
      <a href="fuel.php" class="nav-link">Топливо</a>
      <a href="cards.php" class="nav-link">Карты</a>
      <a href="dispense.php" class="nav-link">Выдача</a>
      <a href="logs.php" class="nav-link">Логи</a>
      <a href="diesel_price.php" class="nav-link">Цены на дизель</a>
      <a href="passes.php" class="nav-link">Пропуска</a>
      <a href="search.php" class="nav-link">Поиск пропуска</a>
    </div>
  </nav>

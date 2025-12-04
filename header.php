<?php
require_once __DIR__ . '/auth.php';
ensureSession();
$allowAnonymous = defined('ALLOW_ANON') && ALLOW_ANON === true;
requireAuthPage($allowAnonymous);
?>
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
      background: radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.14), transparent 25%),
                  radial-gradient(circle at 80% 10%, rgba(16, 185, 129, 0.14), transparent 22%),
                  radial-gradient(circle at 50% 70%, rgba(14, 165, 233, 0.12), transparent 30%),
                  radial-gradient(circle at 10% 80%, rgba(236, 72, 153, 0.08), transparent 28%),
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
    .floating-particles::before,
    .floating-particles::after {
      content: '';
      position: fixed;
      inset: 0;
      pointer-events: none;
      background: radial-gradient(circle at 10% 20%, rgba(34, 211, 238, 0.14), transparent 20%),
                  radial-gradient(circle at 80% 40%, rgba(59, 130, 246, 0.18), transparent 22%),
                  radial-gradient(circle at 30% 75%, rgba(16, 185, 129, 0.12), transparent 24%);
      mix-blend-mode: screen;
      filter: blur(60px);
      animation: drift 22s ease-in-out infinite alternate;
      opacity: 0.7;
    }
    .floating-particles::after {
      animation-direction: alternate-reverse;
      animation-duration: 28s;
      opacity: 0.6;
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
    .neon-border {
      position: relative;
      overflow: hidden;
      border-radius: 16px;
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.12);
    }
    .neon-border::before {
      content: '';
      position: absolute;
      inset: -120%;
      background: conic-gradient(from 0deg, rgba(59,130,246,0.4), rgba(16,185,129,0.35), rgba(14,165,233,0.45), rgba(99,102,241,0.35), rgba(59,130,246,0.4));
      animation: rotate 18s linear infinite;
      opacity: 0.4;
      filter: blur(18px);
      transform-origin: center;
    }
    .neon-border::after {
      content: '';
      position: absolute;
      inset: 1px;
      border-radius: 14px;
      background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02));
      backdrop-filter: blur(12px);
      z-index: 1;
    }
    .modal-enter {
      opacity: 0;
      transform: translateY(10px) scale(0.98);
      transition: opacity 0.35s ease, transform 0.35s ease;
    }
    .modal-enter.active {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
    .glow-ring {
      position: absolute;
      inset: -20%;
      border-radius: 32px;
      background: radial-gradient(circle at 30% 30%, rgba(34, 211, 238, 0.3), transparent 40%),
                  radial-gradient(circle at 70% 70%, rgba(99, 102, 241, 0.25), transparent 42%);
      filter: blur(32px);
      opacity: 0.45;
      animation: pulse 6s ease-in-out infinite alternate;
    }
    @keyframes rotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    @keyframes drift {
      0% { transform: translate3d(-10px, -10px, 0) scale(1); }
      100% { transform: translate3d(12px, 12px, 0) scale(1.05); }
    }
    @keyframes pulse {
      0% { opacity: 0.35; transform: scale(0.98); }
      100% { opacity: 0.75; transform: scale(1.04); }
    }
  </style>
</head>
<body class="text-white">
  <div class="cyber-grid"></div>
  <div class="floating-particles"></div>
  <nav class="glass-nav p-4 sticky top-0 z-30">
    <div class="max-w-7xl mx-auto flex flex-wrap items-center gap-3 text-sm">
      <a href="all.php" class="nav-link font-semibold">Главная</a>
      <a href="dashboard.php" class="nav-link requires-auth" data-requires-auth="true">Панель</a>
      <a href="fuel.php" class="nav-link requires-auth" data-requires-auth="true">Топливо</a>
      <a href="cards.php" class="nav-link requires-auth" data-requires-auth="true">Карты</a>
      <a href="dispense.php" class="nav-link requires-auth" data-requires-auth="true">Выдача</a>
      <a href="logs.php" class="nav-link requires-auth" data-requires-auth="true">Логи</a>
      <a href="diesel_price.php" class="nav-link requires-auth" data-requires-auth="true">Цены на дизель</a>
      <a href="passes.php" class="nav-link requires-auth" data-requires-auth="true">Пропуска</a>
      <a href="search.php" class="nav-link requires-auth" data-requires-auth="true">Поиск пропуска</a>
      <div class="ml-auto flex items-center gap-2" id="auth-cta">
        <div id="user-pill" class="hidden items-center gap-2 rounded-full border border-emerald-300/40 bg-emerald-400/10 px-3 py-1 text-xs font-semibold text-emerald-100 shadow-lg shadow-emerald-500/10 backdrop-blur">
          <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span id="user-pill-name"><?php echo htmlspecialchars(currentUsername() ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <button id="open-login" class="nav-link bg-white/5 px-4 py-2 text-xs uppercase tracking-[0.2em] border border-white/10 shadow-lg shadow-cyan-500/10 <?php echo currentUserId() ? 'hidden' : ''; ?>">Вход</button>
        <button id="logout-btn" class="nav-link bg-gradient-to-r from-emerald-500/80 to-cyan-500/80 px-4 py-2 text-xs uppercase tracking-[0.2em] shadow-lg shadow-emerald-500/20 <?php echo currentUserId() ? '' : 'hidden'; ?>">Выход</button>
      </div>
    </div>
  </nav>

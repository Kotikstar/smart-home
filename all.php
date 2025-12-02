<?php
$TARGET_URLS = [
  'trk'           => 'https://example.com/trk/',
  'service-book'  => 'https://example.com/service-book/',
  'anpr'          => 'https://example.com/anpr/',
  'smart-home'    => 'https://example.com/smart-home/'
];

$ALIASES = [
  'service'   => 'service-book',
  'sb'        => 'service-book',
  'fuel'      => 'trk',
  'trk'       => 'trk',
  'lpr'       => 'anpr',
  'plates'    => 'anpr',
  'recognition'=> 'anpr',
  'smart'     => 'smart-home',
  'home'      => 'smart-home',
  'smarthome' => 'smart-home'
];

function norm_key(?string $key, array $ALIASES): ?string {
  if (!$key) return null;
  $key = strtolower(trim($key, "/# "));
  return $ALIASES[$key] ?? $key;
}

function extract_intent(array $ALIASES): ?string {
  if (isset($_GET['to'])) {
    $to = norm_key($_GET['to'], $ALIASES);
    if ($to) return $to;
  }
  $uri = $_SERVER['REQUEST_URI'] ?? '';
  $path = parse_url($uri, PHP_URL_PATH) ?: '/';
  $segments = array_values(array_filter(explode('/', $path)));
  if (!empty($segments)) {
    $last = end($segments);
    $to = norm_key($last, $ALIASES);
    if ($to) return $to;
  }
  return null;
}

$intent = extract_intent($ALIASES);
if ($intent && isset($TARGET_URLS[$intent])) {
  header('Cache-Control: no-store');
  header('Location: ' . $TARGET_URLS[$intent], true, 302);
  exit;
}
?>
<!doctype html>
<html lang="ru" class="h-full">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>TRK & Service Book — Redirect</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .bg-app {
      background-image:
        radial-gradient(1100px 700px at 20% 10%, rgba(59,130,246,.15), transparent),
        radial-gradient(900px 600px at 80% 90%, rgba(16,185,129,.15), transparent);
      background-color: rgb(15,23,42);
    }
  </style>
  <script>
    const TARGETS = <?php echo json_encode($TARGET_URLS, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?>;
    const ALIASES = <?php echo json_encode($ALIASES, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?>;
    function normKey(k){
      if(!k) return null; k = k.toLowerCase().replace(/^\/+|\/+$/g,'').replace(/^#/, '').trim();
      return ALIASES[k] ?? k;
    }
    document.addEventListener('DOMContentLoaded', () => {
      lucide.createIcons();
      if (location.hash) {
        const key = normKey(location.hash);
        if (key && TARGETS[key]) location.replace(TARGETS[key]);
      }
    });
  </script>
</head>
<body class="h-full bg-app text-slate-100">
  <main class="min-h-full flex items-center justify-center p-6">
    <div class="w-full max-w-5xl">
      <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md shadow-2xl p-8">
        <header class="mb-8 text-center">
          <h1 class="text-4xl font-semibold tracking-tight">Проекты</h1>
          <p class="text-slate-300 mt-2">Выберите нужный модуль ниже или используйте ?to=trk / ?to=service-book</p>
        </header>

        <!-- Первая строка -->
        <section class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <article class="rounded-2xl border border-white/10 bg-slate-900/40 p-8 hover:border-blue-500/40 transition">
            <div class="flex items-center gap-3 mb-3">
              <i data-lucide="fuel" class="w-7 h-7 text-blue-400"></i>
              <h2 class="text-2xl font-semibold">TRK — мини‑АЗС</h2>
            </div>
            <p class="text-slate-300/90 mb-6 text-lg">Учёт топлива, фильтры, графики, Gotify‑уведомления.</p>
            <a href="<?php echo htmlspecialchars($TARGET_URLS['trk']); ?>" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white shadow hover:brightness-110 active:translate-y-px">
              <i data-lucide="arrow-right" class="w-5 h-5"></i> Открыть TRK
            </a>
          </article>

          <article class="rounded-2xl border border-white/10 bg-slate-900/40 p-8 hover:border-emerald-500/40 transition">
            <div class="flex items-center gap-3 mb-3">
              <i data-lucide="wrench" class="w-7 h-7 text-emerald-400"></i>
              <h2 class="text-2xl font-semibold">Service Book — журнал ТО</h2>
            </div>
            <p class="text-slate-300/90 mb-6 text-lg">История работ, пробег, запчасти, стоимость обслуживания.</p>
            <a href="<?php echo htmlspecialchars($TARGET_URLS['service-book']); ?>" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 font-semibold text-white shadow hover:brightness-110 active:translate-y-px">
              <i data-lucide="arrow-right" class="w-5 h-5"></i> Открыть Service Book
            </a>
          </article>
        </section>

        <!-- Вторая строка -->
        <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <article class="rounded-2xl border border-white/10 bg-slate-900/40 p-8 hover:border-cyan-500/40 transition">
            <div class="flex items-center gap-3 mb-3">
              <i data-lucide="scan" class="w-7 h-7 text-cyan-400"></i>
              <h2 class="text-2xl font-semibold">ANPR — въезд по номеру</h2>
            </div>
            <p class="text-slate-300/90 mb-6 text-lg">Распознавание номеров, контроль доступа и ведение логов.</p>
            <a href="<?php echo htmlspecialchars($TARGET_URLS['anpr']); ?>" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-3 font-semibold text-white shadow hover:brightness-110 active:translate-y-px">
              <i data-lucide="arrow-right" class="w-5 h-5"></i> Открыть ANPR
            </a>
          </article>

          <article class="rounded-2xl border border-white/10 bg-slate-900/40 p-8 hover:border-amber-500/40 transition">
            <div class="flex items-center gap-3 mb-3">
              <i data-lucide="house" class="w-7 h-7 text-amber-400"></i>
              <h2 class="text-2xl font-semibold">Smart Home — умный дом</h2>
            </div>
            <p class="text-slate-300/90 mb-6 text-lg">Сцены, датчики, автоматизации, интеграции с TRK.</p>
            <a href="<?php echo htmlspecialchars($TARGET_URLS['smart-home']); ?>" class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-5 py-3 font-semibold text-white shadow hover:brightness-110 active:translate-y-px">
              <i data-lucide="arrow-right" class="w-5 h-5"></i> Открыть Smart Home
            </a>
          </article>
        </section>

        <footer class="mt-10 text-center text-sm text-slate-400">
          © 2025 · Лэндинг-редирект TRK / Service Book / ANPR / Smart Home
        </footer>
      </div>
    </div>
  </main>
</body>
</html>

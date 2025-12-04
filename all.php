<?php
$TARGET_URLS = [
  'home'       => 'all.php',
  'dashboard'  => 'dashboard.php',
  'trk'        => 'dashboard.php',
  'fuel'       => 'fuel.php',
  'cards'      => 'cards.php',
  'dispense'   => 'dispense.php',
  'logs'       => 'logs.php',
  'diesel'     => 'diesel_price.php',
  'passes'     => 'passes.php',
  'search'     => 'search.php'
];

$ALIASES = [
  'home'        => 'home',
  'main'        => 'home',
  'trk'         => 'dashboard',
  'panel'       => 'dashboard',
  'dashboard'   => 'dashboard',
  'fuel'        => 'fuel',
  'cards'       => 'cards',
  'card'        => 'cards',
  'dispense'    => 'dispense',
  'give'        => 'dispense',
  'issue'       => 'dispense',
  'logs'        => 'logs',
  'journal'     => 'logs',
  'diesel'      => 'diesel',
  'price'       => 'diesel',
  'passes'      => 'passes',
  'pass'        => 'passes',
  'permit'      => 'passes',
  'search'      => 'search',
  'plate'       => 'search',
  'plates'      => 'search',
  'lpr'         => 'search'
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
<?php include 'header.php'; ?>
<main class="relative max-w-6xl mx-auto px-4 py-12 space-y-10 z-10">
  <div class="absolute -left-10 -top-16 h-48 w-48 rounded-full bg-cyan-500/20 blur-3xl animate-pulse"></div>
  <div class="absolute right-0 top-24 h-56 w-56 rounded-full bg-emerald-500/15 blur-3xl animate-[pulse_10s_ease-in-out_infinite]"></div>

  <section class="relative overflow-hidden rounded-3xl border border-white/10 bg-white/5 p-10 shadow-2xl">
    <div class="absolute inset-0 opacity-60" aria-hidden="true">
      <div class="absolute left-10 top-6 h-28 w-28 rounded-full bg-blue-500/30 blur-3xl mix-blend-screen animate-pulse"></div>
      <div class="absolute right-6 -bottom-6 h-40 w-40 rounded-full bg-cyan-400/20 blur-3xl mix-blend-screen animate-[ping_8s_linear_infinite]"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.18),_transparent_45%)]"></div>
    </div>
    <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
      <div class="space-y-3 max-w-3xl">
        <p class="text-xs uppercase tracking-[0.35em] text-blue-200/80">Единый портал</p>
        <h1 class="text-3xl md:text-4xl font-bold leading-tight">Хай-тек панель для топлива, пропусков и умного дома</h1>
        <p class="text-gray-200/90 text-lg">Используйте быстрые алиасы (?to=trk, #passes, /logs) или переходите по разделам ниже. Анимации и стеклянные панели создают ощущение единой кибер-панели.</p>
        <div class="flex flex-wrap gap-3">
          <a href="dashboard.php" class="glow-cta inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-sky-500 via-cyan-400 to-emerald-500 px-5 py-3 font-semibold shadow-lg shadow-cyan-500/30">
            <span>Панель TRK</span>
          </a>
          <a href="passes.php" class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/10 px-5 py-3 font-semibold backdrop-blur hover:border-emerald-300/40 transition">
            Пропуска
          </a>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3 text-sm text-gray-200">
        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 shadow-lg shadow-emerald-500/10">
          <p class="text-xs uppercase tracking-wide text-emerald-200/80">Статус</p>
          <p class="text-lg font-semibold">Онлайн</p>
          <p class="text-xs text-gray-400">Мониторинг TRK</p>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 shadow-lg shadow-sky-500/10">
          <p class="text-xs uppercase tracking-wide text-sky-200/80">Сервис</p>
          <p class="text-lg font-semibold">В реальном времени</p>
          <p class="text-xs text-gray-400">Обновление данных</p>
        </div>
        <div class="col-span-2 rounded-2xl border border-white/10 bg-gradient-to-r from-white/5 via-white/10 to-white/5 px-4 py-3 shadow-lg shadow-cyan-500/10">
          <p class="text-xs uppercase tracking-wide text-blue-200/80">Маршрутизация</p>
          <p class="text-sm text-gray-200">/all.php?to=trk • /#passes • /logs</p>
        </div>
      </div>
    </div>
  </section>

  <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 shadow-xl transition transform hover:-translate-y-1 hover:shadow-cyan-500/20">
      <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/5 to-blue-500/0 opacity-0 transition group-hover:opacity-100"></div>
      <div class="flex items-center gap-3 mb-4 relative">
        <div class="h-11 w-11 rounded-xl bg-blue-500/20 text-blue-200 flex items-center justify-center font-semibold ring-1 ring-white/15">TRK</div>
        <div>
          <h2 class="text-xl font-semibold">Учёт топлива</h2>
          <p class="text-sm text-gray-400">Запасы, цены, выдача, статистика</p>
        </div>
      </div>
      <div class="flex flex-wrap gap-3 relative">
        <a href="dashboard.php" class="rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 px-4 py-2 text-sm font-semibold shadow-md shadow-sky-500/25 transition hover:brightness-110">Панель</a>
        <a href="fuel.php" class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold hover:border-cyan-300/40 transition">Топливо</a>
        <a href="cards.php" class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold hover:border-cyan-300/40 transition">Карты</a>
        <a href="dispense.php" class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold hover:border-cyan-300/40 transition">Выдача</a>
        <a href="logs.php" class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold hover:border-cyan-300/40 transition">Логи</a>
        <a href="diesel_price.php" class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold hover:border-cyan-300/40 transition">Цены</a>
      </div>
    </article>

    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 shadow-xl transition transform hover:-translate-y-1 hover:shadow-emerald-500/20">
      <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/8 to-cyan-500/0 opacity-0 transition group-hover:opacity-100"></div>
      <div class="flex items-center gap-3 mb-4 relative">
        <div class="h-11 w-11 rounded-xl bg-emerald-500/20 text-emerald-200 flex items-center justify-center font-semibold ring-1 ring-white/15">ANPR</div>
        <div>
          <h2 class="text-xl font-semibold">Пропуска и поиск номера</h2>
          <p class="text-sm text-gray-400">Постоянные и временные пропуска</p>
        </div>
      </div>
      <div class="flex flex-wrap gap-3 relative">
        <a href="passes.php" class="rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-2 text-sm font-semibold shadow-md shadow-emerald-500/25 transition hover:brightness-110">Создать пропуск</a>
        <a href="search.php" class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold hover:border-emerald-300/40 transition">Поиск пропуска</a>
        <a href="api.php?plate=test" class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold hover:border-emerald-300/40 transition">API проверка</a>
      </div>
    </article>
  </section>

  <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="rounded-2xl border border-white/10 bg-white/5 p-5 space-y-3 shadow-lg shadow-cyan-500/10">
      <p class="text-xs uppercase tracking-[0.25em] text-gray-400">Быстрые ссылки</p>
      <div class="flex flex-wrap gap-2 text-sm">
        <a class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 hover:border-cyan-300/40 transition" href="dashboard.php">/dashboard</a>
        <a class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 hover:border-cyan-300/40 transition" href="fuel.php">/fuel</a>
        <a class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 hover:border-cyan-300/40 transition" href="cards.php">/cards</a>
        <a class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 hover:border-cyan-300/40 transition" href="dispense.php">/dispense</a>
        <a class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 hover:border-cyan-300/40 transition" href="logs.php">/logs</a>
        <a class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 hover:border-cyan-300/40 transition" href="diesel_price.php">/diesel</a>
        <a class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 hover:border-cyan-300/40 transition" href="passes.php">/passes</a>
        <a class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 hover:border-cyan-300/40 transition" href="search.php">/search</a>
      </div>
    </div>
    <div class="rounded-2xl border border-white/10 bg-white/5 p-5 space-y-3 shadow-lg shadow-blue-500/10">
      <p class="text-xs uppercase tracking-[0.25em] text-gray-400">URL / # Алиасы</p>
      <p class="text-gray-200 text-sm">Используйте ?to=trk, /passes или #logs — страница сама перенаправит в нужный раздел.</p>
      <div class="text-xs text-gray-400 space-y-1">
        <p><span class="font-semibold text-gray-200">TRK:</span> trk, panel, dashboard</p>
        <p><span class="font-semibold text-gray-200">Пропуска:</span> passes, pass, permit</p>
        <p><span class="font-semibold text-gray-200">Логи:</span> logs, journal</p>
        <p><span class="font-semibold text-gray-200">Цены:</span> diesel, price</p>
        <p><span class="font-semibold text-gray-200">Поиск:</span> search, plate, lpr</p>
      </div>
    </div>
    <div class="rounded-2xl border border-white/10 bg-white/5 p-5 space-y-3 shadow-lg shadow-emerald-500/10">
      <p class="text-xs uppercase tracking-[0.25em] text-gray-400">API</p>
      <p class="text-gray-200 text-sm">Проверка пропуска: <code class="bg-black/40 px-2 py-1 rounded text-xs border border-white/10">/api.php?plate=AAA000</code></p>
      <p class="text-gray-200 text-sm">Ресурсы TRK: <code class="bg-black/40 px-2 py-1 rounded text-xs border border-white/10">/api.php?resource=fuel</code></p>
      <p class="text-gray-400 text-xs">Работает с GET; для автоматизации используйте алиасы ресурсов.</p>
    </div>
  </section>
</main>
<script>
  const TARGETS = <?php echo json_encode($TARGET_URLS, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?>;
  const ALIASES = <?php echo json_encode($ALIASES, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?>;
  function normKey(k){
    if(!k) return null;
    k = k.toLowerCase().replace(/^\/+|\/+$/g,'').replace(/^#/, '').trim();
    return ALIASES[k] ?? k;
  }
  document.addEventListener('DOMContentLoaded', () => {
    if (location.hash) {
      const key = normKey(location.hash);
      const target = key && TARGETS[key];
      if (target) location.replace(target);
    }
  });
</script>
<?php include 'footer.php'; ?>

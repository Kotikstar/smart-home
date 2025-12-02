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
<main class="max-w-6xl mx-auto px-4 py-10 space-y-8">
  <section class="bg-gradient-to-r from-blue-600/30 via-emerald-500/20 to-cyan-500/20 border border-white/10 rounded-2xl p-8 shadow-lg">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div class="space-y-2">
        <p class="text-sm uppercase tracking-[0.2em] text-blue-200/80">Единый портал</p>
        <h1 class="text-3xl md:text-4xl font-bold leading-tight">Топливо, пропуска и умный дом — в одном месте</h1>
        <p class="text-gray-200/90 max-w-3xl">Выбирайте нужный раздел, используйте быстрые алиасы (?to=trk, #passes, /logs) или переходите по ссылкам ниже. Все страницы используют общее меню и стили.</p>
      </div>
      <div class="flex gap-3">
        <a href="dashboard.php" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-3 font-semibold shadow hover:brightness-110">
          Панель TRK
        </a>
        <a href="passes.php" class="inline-flex items-center gap-2 rounded-xl bg-gray-100 text-gray-900 px-4 py-3 font-semibold shadow hover:brightness-110">
          Пропуска
        </a>
      </div>
    </div>
  </section>

  <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <article class="rounded-2xl bg-gray-800 border border-gray-700 p-6 shadow">
      <div class="flex items-center gap-3 mb-3">
        <div class="h-10 w-10 rounded-full bg-blue-600/20 text-blue-200 flex items-center justify-center font-semibold">TRK</div>
        <h2 class="text-xl font-semibold">Учёт топлива</h2>
      </div>
      <p class="text-gray-300 mb-4">Запасы дизеля, цены, выдача и пополнения, журнал операций и статистика.</p>
      <div class="flex flex-wrap gap-3">
        <a href="dashboard.php" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold hover:brightness-110">Панель</a>
        <a href="fuel.php" class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold hover:bg-gray-600">Топливо</a>
        <a href="cards.php" class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold hover:bg-gray-600">Карты</a>
        <a href="dispense.php" class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold hover:bg-gray-600">Выдача</a>
        <a href="logs.php" class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold hover:bg-gray-600">Логи</a>
        <a href="diesel_price.php" class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold hover:bg-gray-600">Цены</a>
      </div>
    </article>

    <article class="rounded-2xl bg-gray-800 border border-gray-700 p-6 shadow">
      <div class="flex items-center gap-3 mb-3">
        <div class="h-10 w-10 rounded-full bg-emerald-500/20 text-emerald-200 flex items-center justify-center font-semibold">ANPR</div>
        <h2 class="text-xl font-semibold">Пропуска и поиск номера</h2>
      </div>
      <p class="text-gray-300 mb-4">Управление постоянными и временными пропусками, быстрый поиск активного пропуска по номеру.</p>
      <div class="flex flex-wrap gap-3">
        <a href="passes.php" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold hover:brightness-110">Создать пропуск</a>
        <a href="search.php" class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold hover:bg-gray-600">Поиск пропуска</a>
        <a href="api.php?plate=test" class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold hover:bg-gray-600">API проверка</a>
      </div>
    </article>
  </section>

  <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="rounded-2xl bg-gray-800 border border-gray-700 p-5 space-y-2 shadow">
      <p class="text-sm uppercase tracking-[0.2em] text-gray-400">Быстрые ссылки</p>
      <div class="flex flex-wrap gap-2 text-sm">
        <a class="px-3 py-2 rounded-lg bg-gray-700 hover:bg-gray-600" href="dashboard.php">/dashboard</a>
        <a class="px-3 py-2 rounded-lg bg-gray-700 hover:bg-gray-600" href="fuel.php">/fuel</a>
        <a class="px-3 py-2 rounded-lg bg-gray-700 hover:bg-gray-600" href="cards.php">/cards</a>
        <a class="px-3 py-2 rounded-lg bg-gray-700 hover:bg-gray-600" href="dispense.php">/dispense</a>
        <a class="px-3 py-2 rounded-lg bg-gray-700 hover:bg-gray-600" href="logs.php">/logs</a>
        <a class="px-3 py-2 rounded-lg bg-gray-700 hover:bg-gray-600" href="diesel_price.php">/diesel</a>
        <a class="px-3 py-2 rounded-lg bg-gray-700 hover:bg-gray-600" href="passes.php">/passes</a>
        <a class="px-3 py-2 rounded-lg bg-gray-700 hover:bg-gray-600" href="search.php">/search</a>
      </div>
    </div>
    <div class="rounded-2xl bg-gray-800 border border-gray-700 p-5 space-y-2 shadow">
      <p class="text-sm uppercase tracking-[0.2em] text-gray-400">URL / # Алиасы</p>
      <p class="text-gray-300 text-sm">Используйте ?to=trk, /passes или #logs — страница сама перенаправит в нужный раздел.</p>
      <div class="text-xs text-gray-400 space-y-1">
        <p><span class="font-semibold text-gray-200">TRK:</span> trk, panel, dashboard</p>
        <p><span class="font-semibold text-gray-200">Пропуска:</span> passes, pass, permit</p>
        <p><span class="font-semibold text-gray-200">Логи:</span> logs, journal</p>
        <p><span class="font-semibold text-gray-200">Цены:</span> diesel, price</p>
        <p><span class="font-semibold text-gray-200">Поиск:</span> search, plate, lpr</p>
      </div>
    </div>
    <div class="rounded-2xl bg-gray-800 border border-gray-700 p-5 space-y-2 shadow">
      <p class="text-sm uppercase tracking-[0.2em] text-gray-400">API</p>
      <p class="text-gray-300 text-sm">Проверка пропуска: <code class="bg-gray-900 px-2 py-1 rounded text-xs">/api.php?plate=AAA000</code></p>
      <p class="text-gray-300 text-sm">Ресурсы TRK: <code class="bg-gray-900 px-2 py-1 rounded text-xs">/api.php?resource=fuel</code></p>
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

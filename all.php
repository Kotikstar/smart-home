<?php
require_once __DIR__ . '/auth.php';
ensureSession();
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
  <div class="absolute -right-20 bottom-10 h-64 w-64 rounded-full bg-fuchsia-500/10 blur-3xl animate-[pulse_14s_ease-in-out_infinite]"></div>

  <section class="relative overflow-hidden rounded-3xl border border-white/10 bg-white/5 p-10 shadow-2xl neon-border">
    <div class="glow-ring" aria-hidden="true"></div>
    <div class="absolute inset-0 opacity-60" aria-hidden="true">
      <div class="absolute left-10 top-6 h-28 w-28 rounded-full bg-blue-500/30 blur-3xl mix-blend-screen animate-pulse"></div>
      <div class="absolute right-6 -bottom-6 h-40 w-40 rounded-full bg-cyan-400/20 blur-3xl mix-blend-screen animate-[ping_8s_linear_infinite]"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.18),_transparent_45%)]"></div>
    </div>
    <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
      <div class="space-y-3 max-w-3xl">
        <p class="text-xs uppercase tracking-[0.35em] text-blue-200/80">Единый портал</p>
        <h1 class="text-3xl md:text-4xl font-bold leading-tight">Хай-тек панель для топлива, пропусков и умного дома</h1>
        <p class="text-gray-200/90 text-lg">Используйте быстрые алиасы (?to=trk, #passes, /logs) или переходите по разделам ниже. Анимации, стекло и ореолы создают ощущение единой кибер-панели.</p>
        <div class="flex flex-wrap gap-3">
          <a href="dashboard.php" class="glow-cta inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-sky-500 via-cyan-400 to-emerald-500 px-5 py-3 font-semibold shadow-lg shadow-cyan-500/30">
            <span>Панель TRK</span>
          </a>
          <a href="passes.php" class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/10 px-5 py-3 font-semibold backdrop-blur hover:border-emerald-300/40 transition">
            Пропуска
          </a>
          <button id="open-login-hero" class="inline-flex items-center gap-2 rounded-2xl border border-white/20 bg-black/30 px-5 py-3 font-semibold backdrop-blur hover:border-cyan-300/60 transition shadow-lg shadow-cyan-500/20">Войти</button>
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
    <div class="rounded-2xl border border-white/10 bg-white/5 p-5 space-y-3 shadow-lg shadow-cyan-500/10 backdrop-blur-xl">
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
    <div class="rounded-2xl border border-white/10 bg-white/5 p-5 space-y-3 shadow-lg shadow-blue-500/10 backdrop-blur-xl">
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
    <div class="rounded-2xl border border-white/10 bg-white/5 p-5 space-y-3 shadow-lg shadow-emerald-500/10 backdrop-blur-xl">
      <p class="text-xs uppercase tracking-[0.25em] text-gray-400">API</p>
      <p class="text-gray-200 text-sm">Проверка пропуска: <code class="bg-black/40 px-2 py-1 rounded text-xs border border-white/10">/api.php?plate=AAA000</code></p>
      <p class="text-gray-200 text-sm">Ресурсы TRK: <code class="bg-black/40 px-2 py-1 rounded text-xs border border-white/10">/api.php?resource=fuel</code></p>
      <p class="text-gray-400 text-xs">Работает с GET; для автоматизации используйте алиасы ресурсов.</p>
    </div>
  </section>
</main>

<div id="login-modal" class="fixed inset-0 bg-black/70 backdrop-blur-md flex items-center justify-center opacity-0 pointer-events-none transition duration-300 z-50">
  <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(14,165,233,0.2),transparent_30%),radial-gradient(circle_at_80%_30%,rgba(16,185,129,0.2),transparent_35%)] blur-3xl"></div>
  <div class="relative w-full max-w-lg p-[1px] rounded-[26px] bg-gradient-to-br from-cyan-400/40 via-fuchsia-500/30 to-emerald-500/40 shadow-2xl shadow-cyan-500/30">
    <div class="relative rounded-[24px] bg-slate-900/80 backdrop-blur-2xl border border-white/10 p-8 modal-enter">
      <div class="absolute -top-10 right-6 h-24 w-24 rounded-full bg-cyan-500/30 blur-3xl mix-blend-screen animate-[pulse_9s_ease-in-out_infinite]"></div>
      <div class="absolute -bottom-12 -left-12 h-32 w-32 rounded-full bg-fuchsia-500/25 blur-3xl mix-blend-screen animate-[pulse_12s_ease-in-out_infinite]"></div>
      <div class="relative space-y-6">
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-cyan-200/80">Доступ</p>
            <h2 class="text-2xl font-semibold">Вход в панель</h2>
            <p class="text-sm text-gray-300">Войдите, чтобы управлять TRK, пропусками и журналами.</p>
          </div>
          <button id="close-login" class="h-9 w-9 rounded-full bg-white/5 border border-white/10 flex items-center justify-center hover:border-cyan-300/50 transition">
            <span class="sr-only">Закрыть</span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5 text-gray-200"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 6l12 12M6 18L18 6" /></svg>
          </button>
        </div>
        <form class="space-y-4 relative z-10" id="passkey-form">
          <div class="space-y-2">
            <label class="text-sm text-gray-300" for="login-username">Логин / имя оператора</label>
            <div class="relative">
              <input id="login-username" type="text" autocomplete="username" placeholder="operator" class="w-full rounded-xl border border-white/15 bg-white/5 px-4 py-3 text-sm placeholder:text-gray-500 focus:border-cyan-300/60 focus:outline-none focus:ring-0 backdrop-blur">
              <div class="pointer-events-none absolute inset-0 rounded-xl border border-white/5 blur-xl"></div>
            </div>
          </div>
          <div class="rounded-xl border border-white/10 bg-white/5 p-3 text-xs text-gray-300 leading-relaxed shadow-inner shadow-cyan-500/10">
            Passkey заменяет пароль: сначала создайте ключ на этом устройстве, затем входите одним касанием. Данные хранятся на стороне браузера, сервер проверяет подпись через WebAuthn.
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <button type="button" id="register-passkey" class="relative overflow-hidden rounded-xl bg-gradient-to-r from-fuchsia-500 via-cyan-500 to-emerald-500 px-4 py-3 font-semibold shadow-lg shadow-cyan-500/40 ring-1 ring-white/10">
              <span class="relative z-10">Создать passkey</span>
              <div class="absolute inset-0 bg-white/10 blur-xl opacity-0 hover:opacity-70 transition"></div>
            </button>
            <button type="button" id="login-passkey" class="rounded-xl border border-white/15 bg-black/30 px-4 py-3 font-semibold shadow-lg shadow-emerald-500/15 hover:border-emerald-300/50 transition">
              Войти по passkey
            </button>
          </div>
          <p id="login-status" class="text-sm text-gray-300 min-h-[1.5rem]"></p>
          <p class="text-xs text-gray-400">Поддерживаются браузеры с WebAuthn / passkeys. Если кнопки неактивны, обновите браузер или устройство.</p>
        </form>
      </div>
    </div>
  </div>
</div>
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

    const modal = document.getElementById('login-modal');
    const openers = [
      document.getElementById('open-login'),
      document.getElementById('open-login-hero')
    ].filter(Boolean);
    const closeBtn = document.getElementById('close-login');
    const modalCard = modal?.querySelector('.modal-enter');

    const openModal = () => {
      if (!modal) return;
      modal.classList.remove('pointer-events-none');
      requestAnimationFrame(() => {
        modal.classList.remove('opacity-0');
        modalCard?.classList.add('active');
      });
    };

    const closeModal = () => {
      if (!modal) return;
      modal.classList.add('opacity-0');
      modalCard?.classList.remove('active');
      setTimeout(() => modal.classList.add('pointer-events-none'), 250);
    };

    openers.forEach(btn => btn.addEventListener('click', openModal));
    closeBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e) => {
      if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeModal();
    });

    const usernameInput = document.getElementById('login-username');
    const statusEl = document.getElementById('login-status');
    const registerBtn = document.getElementById('register-passkey');
    const loginBtn = document.getElementById('login-passkey');
    const logoutBtn = document.getElementById('logout-btn');
    const userPill = document.getElementById('user-pill');
    const userPillName = document.getElementById('user-pill-name');
    const loginTrigger = document.getElementById('open-login');

    const b64ToArrayBuffer = (b64) => {
      const pad = '='.repeat((4 - (b64.length % 4)) % 4);
      const normalized = (b64 + pad).replace(/-/g, '+').replace(/_/g, '/');
      const str = atob(normalized);
      const buffer = new Uint8Array(str.length);
      for (let i = 0; i < str.length; i++) buffer[i] = str.charCodeAt(i);
      return buffer.buffer;
    };

    const bufferToB64 = (buf) => {
      const bytes = new Uint8Array(buf);
      let binary = '';
      bytes.forEach((b) => (binary += String.fromCharCode(b)));
      return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
    };

    const setStatus = (msg, tone = 'info') => {
      if (!statusEl) return;
      statusEl.textContent = msg;
      statusEl.classList.remove('text-emerald-300', 'text-amber-300', 'text-rose-300');
      const toneMap = { success: 'text-emerald-300', warn: 'text-amber-300', error: 'text-rose-300' };
      if (toneMap[tone]) statusEl.classList.add(toneMap[tone]);
    };

    const updateAuthUI = (payload) => {
      const authed = payload?.authenticated;
      if (authed) {
        userPill?.classList.remove('hidden');
        logoutBtn?.classList.remove('hidden');
        loginTrigger?.classList.add('hidden');
        if (payload.username) userPillName.textContent = payload.username;
      } else {
        userPill?.classList.add('hidden');
        logoutBtn?.classList.add('hidden');
        loginTrigger?.classList.remove('hidden');
      }
    };

    const fetchSession = async () => {
      try {
        const res = await fetch('webauthn.php?action=session');
        const data = await res.json();
        updateAuthUI(data);
        return data;
      } catch (e) {
        console.error(e);
      }
    };

    const callEndpoint = async (action, body = {}) => {
      const res = await fetch(`webauthn.php?action=${action}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
      });
      const data = await res.json();
      return { ok: res.ok, data };
    };

    const decodeCreationOptions = (options) => {
      return {
        ...options,
        challenge: b64ToArrayBuffer(options.challenge),
        user: { ...options.user, id: b64ToArrayBuffer(options.user.id) },
        excludeCredentials: (options.excludeCredentials || []).map((c) => ({ ...c, id: b64ToArrayBuffer(c.id) })),
      };
    };

    const decodeRequestOptions = (options) => {
      return {
        ...options,
        challenge: b64ToArrayBuffer(options.challenge),
        allowCredentials: (options.allowCredentials || []).map((c) => ({ ...c, id: b64ToArrayBuffer(c.id) })),
      };
    };

    const ensureSupport = () => {
      if (!window.PublicKeyCredential) {
        setStatus('Passkeys не поддерживаются этим браузером', 'error');
        return false;
      }
      return true;
    };

    const registerPasskey = async () => {
      if (!ensureSupport()) return;
      const username = usernameInput?.value.trim();
      if (!username) {
        setStatus('Укажите логин для создания ключа', 'warn');
        return;
      }
      setStatus('Готовим челлендж для регистрации...');
      registerBtn?.setAttribute('disabled', 'disabled');
      loginBtn?.setAttribute('disabled', 'disabled');
      try {
        const { ok, data } = await callEndpoint('start-registration', { username });
        if (!ok) throw new Error(data.error || 'Не удалось запросить регистрацию');
        const options = decodeCreationOptions(data.publicKey);
        const credential = await navigator.credentials.create({ publicKey: options });
        const attestation = credential.response;
        const authenticatorData = attestation.getAuthenticatorData ? attestation.getAuthenticatorData() : null;
        const publicKey = attestation.getPublicKey ? attestation.getPublicKey() : null;
        const payload = {
          id: credential.id,
          rawId: bufferToB64(credential.rawId),
          type: credential.type,
          response: {
            clientDataJSON: bufferToB64(attestation.clientDataJSON),
            authenticatorData: authenticatorData ? bufferToB64(authenticatorData) : null,
            attestationObject: attestation.attestationObject ? bufferToB64(attestation.attestationObject) : null,
            signature: null,
            userHandle: null,
          },
          publicKey: publicKey ? bufferToB64(publicKey) : null,
          publicKeyAlgorithm: attestation.getPublicKeyAlgorithm ? attestation.getPublicKeyAlgorithm() : null,
          transports: attestation.getTransports ? attestation.getTransports().join(',') : null,
        };
        const finish = await callEndpoint('finish-registration', { credential: payload });
        if (!finish.ok) throw new Error(finish.data.error || 'Не удалось завершить регистрацию');
        setStatus('Passkey создан, вход выполнен', 'success');
        updateAuthUI({ authenticated: true, username: finish.data.username });
        closeModal();
      } catch (err) {
        console.error(err);
        setStatus(err.message || 'Ошибка регистрации passkey', 'error');
      } finally {
        registerBtn?.removeAttribute('disabled');
        loginBtn?.removeAttribute('disabled');
      }
    };

    const loginPasskey = async () => {
      if (!ensureSupport()) return;
      const username = usernameInput?.value.trim();
      if (!username) {
        setStatus('Введите логин для входа', 'warn');
        return;
      }
      setStatus('Запрашиваем ключ и челлендж входа...');
      registerBtn?.setAttribute('disabled', 'disabled');
      loginBtn?.setAttribute('disabled', 'disabled');
      try {
        const { ok, data } = await callEndpoint('start-login', { username });
        if (!ok) throw new Error(data.error || 'Не удалось запросить вход');
        const options = decodeRequestOptions(data.publicKey);
        const assertion = await navigator.credentials.get({ publicKey: options });
        const auth = assertion.response;
        const payload = {
          id: assertion.id,
          rawId: bufferToB64(assertion.rawId),
          type: assertion.type,
          response: {
            clientDataJSON: bufferToB64(auth.clientDataJSON),
            authenticatorData: bufferToB64(auth.authenticatorData),
            signature: bufferToB64(auth.signature),
            userHandle: auth.userHandle ? bufferToB64(auth.userHandle) : null,
          },
        };
        const finish = await callEndpoint('finish-login', { credential: payload });
        if (!finish.ok) throw new Error(finish.data.error || 'Не удалось завершить вход');
        setStatus('Вход выполнен по passkey', 'success');
        updateAuthUI({ authenticated: true, username: finish.data.username });
        closeModal();
      } catch (err) {
        console.error(err);
        setStatus(err.message || 'Ошибка входа', 'error');
      } finally {
        registerBtn?.removeAttribute('disabled');
        loginBtn?.removeAttribute('disabled');
      }
    };

    const logout = async () => {
      try {
        await callEndpoint('logout');
        updateAuthUI({ authenticated: false });
      } catch (err) {
        console.error(err);
      }
    };

    registerBtn?.addEventListener('click', registerPasskey);
    loginBtn?.addEventListener('click', loginPasskey);
    logoutBtn?.addEventListener('click', logout);

    fetchSession();
  });
</script>
<?php include 'footer.php'; ?>

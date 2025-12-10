<?php
require_once __DIR__ . '/auth.php';
ensureSession();
if (!isAdmin()) {
  header('Location: all.php?denied=1');
  exit;
}
include 'header.php';
?>

<main class="relative max-w-6xl mx-auto px-4 py-10 space-y-10">
  <div class="absolute -left-24 -top-32 h-72 w-72 rounded-full bg-emerald-500/10 blur-3xl"></div>
  <div class="absolute right-0 top-10 h-64 w-64 rounded-full bg-cyan-500/10 blur-3xl"></div>

  <section class="relative overflow-hidden rounded-3xl border border-emerald-200/30 bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-cyan-500/10 p-8 shadow-2xl shadow-emerald-500/10">
    <div class="absolute inset-0 opacity-50" aria-hidden="true">
      <div class="absolute left-6 top-6 h-28 w-28 rounded-full bg-emerald-400/25 mix-blend-screen"></div>
      <div class="absolute right-6 -bottom-10 h-48 w-48 rounded-full bg-cyan-400/20 mix-blend-screen"></div>
    </div>
    <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div class="space-y-3 max-w-3xl">
        <p class="text-xs uppercase tracking-[0.35em] text-emerald-200/80">Панель администратора</p>
        <h1 class="text-3xl font-bold leading-tight">Выдача и отладка прав</h1>
        <p class="text-gray-200/90 text-sm md:text-base">Полностью переписанная матрица доступа: выбирайте пользователя, меняйте роли и права, отслеживайте дифф, сохраняйте и смотрите отладочные события.</p>
      </div>
      <div class="flex items-center gap-3">
        <span class="rounded-full border border-emerald-200/40 bg-emerald-400/15 px-3 py-1 text-xs font-semibold text-emerald-50 shadow-lg shadow-emerald-500/20">ADMIN</span>
        <button id="reload-users" class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold hover:border-emerald-200/60 transition">Обновить список</button>
      </div>
    </div>
  </section>

  <section class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-xl shadow-emerald-500/10 space-y-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <h2 class="text-xl font-semibold">Админские модули</h2>
        <p class="text-sm text-gray-200">«Топливо» и «Выдача» теперь открываются во всплывающих окнах без редиректов.</p>
      </div>
      <div class="flex gap-2">
        <button data-modal-target="fuel" class="rounded-lg border border-emerald-200/40 bg-emerald-500/15 px-4 py-2 text-sm font-semibold hover:border-emerald-200/70 transition">Открыть «Топливо»</button>
        <button data-modal-target="dispense" class="rounded-lg border border-cyan-200/40 bg-cyan-500/15 px-4 py-2 text-sm font-semibold hover:border-cyan-200/70 transition">Открыть «Выдача»</button>
      </div>
    </div>
    <div class="text-xs text-gray-300">Окно откроется внутри этой страницы, можно работать и параллельно выдавать права.</div>
  </section>

  <section class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-xl shadow-emerald-500/10">
    <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
      <div>
        <h2 class="text-xl font-semibold">Управление доступом</h2>
        <p class="text-sm text-gray-200">Выберите пользователя слева, справа отобразится его роль и чек-лист прав. Дифф фиксируется автоматически.</p>
      </div>
      <div class="flex items-center gap-2 text-sm">
        <span id="status-badge" class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-gray-200">Готово к работе</span>
        <button id="toggle-debug" class="rounded-lg border border-white/10 bg-white/5 px-3 py-1 text-xs">Показать отладку</button>
      </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-[320px,1fr] gap-4">
      <div class="space-y-3" id="user-list"></div>
      <div class="rounded-2xl border border-white/10 bg-black/10 p-4 space-y-4" id="user-details">
        <p class="text-gray-300 text-sm">Выберите пользователя, чтобы изменить права.</p>
      </div>
    </div>
  </section>

  <section id="debug-panel" class="hidden rounded-3xl border border-emerald-200/20 bg-emerald-500/5 p-4 text-sm space-y-3">
    <div class="flex items-center justify-between">
      <h3 class="font-semibold text-emerald-100">Отладка выдачи прав</h3>
      <button id="clear-debug" class="text-xs text-emerald-200 underline">Очистить</button>
    </div>
    <ul id="debug-log" class="space-y-2 max-h-60 overflow-y-auto pr-2"></ul>
  </section>
</main>

<div id="modal-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-40">
  <div class="absolute inset-0" data-close-modal></div>
  <div class="relative w-full max-w-5xl max-h-[80vh] bg-gray-900 rounded-2xl border border-white/10 shadow-2xl overflow-hidden modal-enter" id="modal-shell">
    <div class="absolute inset-0 glow-ring pointer-events-none"></div>
    <div class="flex items-center justify-between px-4 py-3 border-b border-white/10 relative z-10">
      <div>
        <p class="text-xs uppercase tracking-[0.2em] text-emerald-200" id="modal-title">Модуль</p>
        <p class="text-sm text-gray-300" id="modal-subtitle"></p>
      </div>
      <button data-close-modal class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs">Закрыть</button>
    </div>
    <div class="relative z-10">
      <iframe id="modal-frame" src="" class="w-full h-[70vh] border-0"></iframe>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const currentUserId = <?php echo (int) currentUserId(); ?>;
    const PERMISSION_CONFIG = [
      { key: 'dashboard', label: 'Панель', hint: 'Главный дашборд', badge: 'bg-blue-500/15 text-blue-100' },
      { key: 'fuel', label: 'Топливо', hint: 'Отчёты по топливу', badge: 'bg-amber-500/15 text-amber-100' },
      { key: 'cards', label: 'Карты', hint: 'Управление картами', badge: 'bg-emerald-500/15 text-emerald-100' },
      { key: 'dispense', label: 'Выдача', hint: 'Выдача топлива', badge: 'bg-cyan-500/15 text-cyan-100' },
      { key: 'logs', label: 'Логи', hint: 'Просмотр журналов', badge: 'bg-indigo-500/15 text-indigo-100' },
      { key: 'diesel', label: 'Цены', hint: 'Изменение стоимости', badge: 'bg-pink-500/15 text-pink-100' },
      { key: 'passes', label: 'Пропуска', hint: 'КПП', badge: 'bg-lime-500/15 text-lime-100' },
      { key: 'service', label: 'Сервис', hint: 'Сервисные операции', badge: 'bg-amber-300/15 text-amber-50' },
      { key: 'carbook', label: 'Car Book', hint: 'Учёт ТС', badge: 'bg-orange-500/15 text-orange-100' },
    ];

    const userList = document.getElementById('user-list');
    const userDetails = document.getElementById('user-details');
    const statusBadge = document.getElementById('status-badge');
    const reloadBtn = document.getElementById('reload-users');
    const debugPanel = document.getElementById('debug-panel');
    const toggleDebug = document.getElementById('toggle-debug');
    const debugLogEl = document.getElementById('debug-log');
    const clearDebug = document.getElementById('clear-debug');

    const modalBackdrop = document.getElementById('modal-backdrop');
    const modalFrame = document.getElementById('modal-frame');
    const modalTitle = document.getElementById('modal-title');
    const modalSubtitle = document.getElementById('modal-subtitle');

    const state = {
      users: new Map(),
      active: null,
    };

    const logDebug = (msg, payload = null) => {
      const time = new Date().toLocaleTimeString();
      const li = document.createElement('li');
      li.className = 'rounded-lg border border-emerald-200/20 bg-emerald-500/10 px-3 py-2 text-emerald-50';
      li.innerHTML = `<div class="flex items-center justify-between"><span class="text-xs font-semibold">${time}</span><span class="text-[11px] uppercase tracking-wide">TRACE</span></div><div class="mt-1 text-sm">${msg}</div>`;
      if (payload) {
        const pre = document.createElement('pre');
        pre.className = 'mt-2 text-[11px] text-emerald-100/90 whitespace-pre-wrap';
        pre.textContent = JSON.stringify(payload, null, 2);
        li.appendChild(pre);
      }
      debugLogEl?.prepend(li);
    };

    const setStatus = (text, tone = 'idle') => {
      const toneMap = {
        idle: 'border-white/10 bg-white/5 text-gray-200',
        info: 'border-blue-200/40 bg-blue-500/10 text-blue-100',
        warn: 'border-amber-200/40 bg-amber-500/10 text-amber-100',
        success: 'border-emerald-200/40 bg-emerald-500/10 text-emerald-100',
        error: 'border-rose-200/40 bg-rose-500/10 text-rose-100',
      };
      statusBadge.textContent = text;
      statusBadge.className = `rounded-full border px-3 py-1 text-xs ${toneMap[tone] || toneMap.idle}`;
    };

    const cloneUser = (user) => JSON.parse(JSON.stringify(user));

    const buildPermissionsGrid = (user) => {
      return PERMISSION_CONFIG.map(({ key, label, hint, badge }) => {
        const checked = user.permissions?.[key] ? 'checked' : '';
        return `
          <label class="flex items-start gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2">
            <input data-permission="${key}" type="checkbox" class="mt-1 h-4 w-4 rounded border-white/30 bg-white/10" ${checked}>
            <div>
              <p class="text-sm font-semibold flex items-center gap-2"><span class="px-2 py-0.5 rounded-full text-[11px] ${badge}">${label}</span></p>
              <p class="text-xs text-gray-300">${hint}</p>
            </div>
          </label>`;
      }).join('');
    };

    const computeDiff = (original, draft) => {
      const diff = { is_admin: draft.is_admin, permissions: {}, changed: false };
      if (original.is_admin !== draft.is_admin) diff.changed = true;
      PERMISSION_CONFIG.forEach(({ key }) => {
        const before = !!original.permissions[key];
        const after = !!draft.permissions[key];
        if (before !== after) diff.changed = true;
        diff.permissions[key] = after;
      });
      return diff;
    };

    const renderUserList = () => {
      const nodes = [];
      state.users.forEach((entry) => {
        const isActive = state.active === entry.id;
        const diff = computeDiff(entry.original, entry.draft);
        const dirty = diff.changed;
        nodes.push(`
          <button data-user-id="${entry.id}" class="w-full text-left rounded-xl border ${isActive ? 'border-emerald-300/60 bg-emerald-500/15' : 'border-white/10 bg-white/5'} px-4 py-3 hover:border-emerald-200/60 transition">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-semibold">${entry.username}</p>
                <p class="text-xs text-gray-400">ID ${entry.id}</p>
              </div>
              <div class="flex items-center gap-2 text-[11px]">
                <span class="rounded-full border px-2 py-0.5 ${entry.draft.is_admin ? 'border-amber-300/60 bg-amber-400/15 text-amber-50' : 'border-white/10 bg-white/5 text-gray-200'}">${entry.draft.is_admin ? 'Админ' : 'Оператор'}</span>
                ${dirty ? '<span class="rounded-full bg-amber-500/20 text-amber-50 px-2 py-0.5">✎</span>' : ''}
              </div>
            </div>
          </button>`);
      });
      userList.innerHTML = nodes.join('') || '<p class="text-sm text-gray-300">Пользователи не найдены</p>';
    };

    const renderDetails = () => {
      const active = state.users.get(state.active);
      if (!active) {
        userDetails.innerHTML = '<p class="text-gray-300 text-sm">Выберите пользователя, чтобы изменить права.</p>';
        return;
      }
      const diff = computeDiff(active.original, active.draft);
      const dirtyText = diff.changed ? 'Есть несохранённые изменения' : 'Изменений нет';
      const dirtyTone = diff.changed ? 'text-amber-200' : 'text-gray-300';
      userDetails.innerHTML = `
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <p class="text-lg font-semibold">${active.username}</p>
            <p class="text-xs text-gray-400">ID ${active.id}</p>
          </div>
          <label class="inline-flex items-center gap-2 text-sm font-semibold text-amber-100">
            <input data-admin-toggle type="checkbox" class="h-4 w-4 rounded border-amber-200/70 bg-amber-200/20" ${active.draft.is_admin ? 'checked' : ''}>
            <span>Сделать админом</span>
          </label>
        </div>
        <p class="text-xs ${dirtyTone}">${dirtyText}</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">${buildPermissionsGrid(active.draft)}</div>
        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
          <button class="rounded-lg border border-white/10 px-3 py-2 text-xs text-gray-200 hover:border-amber-200/60" id="reset-user">Сбросить</button>
          <div class="flex items-center gap-2">
            <button class="rounded-lg border border-white/10 px-3 py-2 text-xs text-gray-200 hover:border-emerald-200/60" id="refresh-user">Обновить</button>
            <button class="rounded-lg bg-gradient-to-r from-emerald-500/80 to-emerald-400/80 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 disabled:opacity-60" id="save-user" ${diff.changed ? '' : 'disabled'}>Сохранить</button>
          </div>
        </div>`;
    };

    const pickUser = (userId) => {
      state.active = userId;
      renderUserList();
      renderDetails();
    };

    const loadUsers = async () => {
      setStatus('Загружаем пользователей...', 'info');
      try {
        const res = await fetch('api.php?resource=users&action=list');
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Не удалось получить пользователей');
        state.users.clear();
        data.forEach((u) => {
          state.users.set(u.id, { id: u.id, username: u.username, original: cloneUser(u), draft: cloneUser(u) });
        });
        setStatus('Пользователи загружены', 'success');
        logDebug('Загружен список пользователей', data);
        renderUserList();
        if (data.length) {
          const firstId = state.active && state.users.has(state.active) ? state.active : data[0].id;
          pickUser(firstId);
        } else {
          state.active = null;
          renderDetails();
        }
      } catch (err) {
        setStatus(err.message || 'Ошибка загрузки', 'error');
        logDebug('Ошибка загрузки пользователей', { error: err.message });
      }
    };

    const syncDraftFromDetails = () => {
      const active = state.users.get(state.active);
      if (!active) return;
      const adminToggle = userDetails.querySelector('[data-admin-toggle]');
      active.draft.is_admin = adminToggle?.checked || false;
      PERMISSION_CONFIG.forEach(({ key }) => {
        const input = userDetails.querySelector(`input[data-permission="${key}"]`);
        active.draft.permissions[key] = input?.checked || false;
      });
      renderUserList();
      renderDetails();
    };

    const resetActive = () => {
      const active = state.users.get(state.active);
      if (!active) return;
      active.draft = cloneUser(active.original);
      renderUserList();
      renderDetails();
      logDebug('Сброшены изменения', { user_id: active.id });
    };

    const refreshActive = async () => {
      const active = state.users.get(state.active);
      if (!active) return;
      setStatus('Обновляем пользователя...', 'info');
      try {
        const res = await fetch('api.php?resource=users&action=list');
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Не удалось обновить');
        const fresh = data.find((u) => u.id === active.id);
        if (fresh) {
          state.users.set(fresh.id, { id: fresh.id, username: fresh.username, original: cloneUser(fresh), draft: cloneUser(fresh) });
          pickUser(fresh.id);
          logDebug('Перечитан пользователь', fresh);
        }
        setStatus('Готово', 'success');
      } catch (err) {
        setStatus(err.message || 'Ошибка обновления', 'error');
        logDebug('Ошибка обновления пользователя', { error: err.message });
      }
    };

    const saveActive = async () => {
      const active = state.users.get(state.active);
      if (!active) return;
      const diff = computeDiff(active.original, active.draft);
      if (!diff.changed) return;
      setStatus('Сохраняем...', 'info');
      logDebug('Отправляем права', { user_id: active.id, payload: diff });
      try {
        const res = await fetch('api.php?resource=users&action=update_permissions', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ user_id: active.id, permissions: { is_admin: diff.is_admin, ...diff.permissions } }),
        });
        const data = await res.json();
        if (!res.ok) {
          logDebug('Ошибка сохранения (ответ сервера)', { status: res.status, body: data });
          throw new Error(data.error || 'Не удалось сохранить');
        }
        const merged = {
          id: active.id,
          username: active.username,
          is_admin: !!data.saved?.is_admin,
          permissions: data.saved?.permissions || {},
        };
        active.original = cloneUser(merged);
        active.draft = cloneUser(merged);
        renderUserList();
        renderDetails();
        setStatus('Права обновлены', 'success');
        logDebug('Ответ API: сохранено', data);
        if (active.id === currentUserId) {
          await fetch('webauthn.php?action=session');
          logDebug('Сессия текущего пользователя обновлена');
        }
      } catch (err) {
        setStatus(err.message || 'Ошибка сохранения', 'error');
        logDebug('Ошибка сохранения', { error: err.message });
      }
    };

    userList?.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-user-id]');
      if (!btn) return;
      pickUser(Number(btn.dataset.userId));
    });

    userDetails?.addEventListener('change', (e) => {
      if (e.target.matches('input[type="checkbox"]')) {
        syncDraftFromDetails();
      }
    });

    userDetails?.addEventListener('click', (e) => {
      if (e.target.id === 'reset-user') {
        resetActive();
      }
      if (e.target.id === 'refresh-user') {
        refreshActive();
      }
      if (e.target.id === 'save-user') {
        saveActive();
      }
    });

    reloadBtn?.addEventListener('click', () => {
      state.active = null;
      loadUsers();
    });

    toggleDebug?.addEventListener('click', () => {
      const open = debugPanel.classList.toggle('hidden');
      toggleDebug.textContent = open ? 'Скрыть отладку' : 'Показать отладку';
    });

    clearDebug?.addEventListener('click', () => {
      debugLogEl.innerHTML = '';
    });

    document.querySelectorAll('[data-modal-target]')?.forEach((btn) => {
      btn.addEventListener('click', () => {
        const target = btn.dataset.modalTarget;
        const config = target === 'fuel'
          ? { title: 'Топливо', subtitle: 'Работаем без выхода из админки', src: 'fuel.php' }
          : { title: 'Выдача', subtitle: 'Выдача топлива в отдельном окне', src: 'dispense.php' };
        modalTitle.textContent = config.title;
        modalSubtitle.textContent = config.subtitle;
        modalFrame.src = config.src;
        modalBackdrop.classList.remove('hidden');
        setTimeout(() => modalBackdrop.querySelector('#modal-shell')?.classList.add('active'), 10);
      });
    });

    modalBackdrop?.addEventListener('click', (e) => {
      if (e.target.hasAttribute('data-close-modal')) {
        modalBackdrop.classList.add('hidden');
        modalFrame.src = '';
        modalBackdrop.querySelector('#modal-shell')?.classList.remove('active');
      }
    });

    document.querySelectorAll('[data-close-modal]')?.forEach((btn) => {
      btn.addEventListener('click', () => {
        modalBackdrop.classList.add('hidden');
        modalFrame.src = '';
        modalBackdrop.querySelector('#modal-shell')?.classList.remove('active');
      });
    });

    loadUsers();
  });
</script>

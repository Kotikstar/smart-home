<?php
require_once __DIR__ . '/auth.php';
ensureSession();
if (!isAdmin()) {
  header('Location: all.php?denied=1');
  exit;
}
include 'header.php';
?>

<main class="relative max-w-6xl mx-auto px-4 py-10 space-y-8">
  <div class="absolute -left-16 -top-16 h-48 w-48 rounded-full bg-amber-500/15"></div>
  <div class="absolute right-0 top-10 h-56 w-56 rounded-full bg-cyan-500/10"></div>

  <section class="relative overflow-hidden rounded-3xl border border-amber-200/30 bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-cyan-500/10 p-8 shadow-2xl shadow-amber-500/10">
    <div class="absolute inset-0 opacity-50" aria-hidden="true">
      <div class="absolute left-10 top-6 h-28 w-28 rounded-full bg-amber-400/25 mix-blend-screen"></div>
      <div class="absolute right-6 -bottom-6 h-40 w-40 rounded-full bg-cyan-400/16 mix-blend-screen"></div>
    </div>
    <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div class="space-y-3 max-w-3xl">
        <p class="text-xs uppercase tracking-[0.35em] text-amber-200/80">Администрирование</p>
        <h1 class="text-3xl font-bold leading-tight">Роли, права и контроль доступа</h1>
        <p class="text-gray-200/90 text-sm md:text-base">Редактируйте роли операторов, выдавайте права на разделы и отмечайте администраторов. Изменения подсвечиваются сразу, чтобы вы не потеряли контекст.</p>
      </div>
      <div class="flex items-center gap-3">
        <span class="rounded-full border border-amber-200/40 bg-amber-400/15 px-3 py-1 text-xs font-semibold text-amber-100 shadow-lg shadow-amber-500/20">ADMIN</span>
        <button id="reload-users" class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold hover:border-amber-200/60 transition">Обновить список</button>
      </div>
    </div>
  </section>

  <section class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-xl shadow-amber-500/10 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-semibold">Админ-доступ к разделам</h2>
        <p class="text-sm text-gray-200">Критичные панели доступны только из этого блока.</p>
      </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-200">
      <a href="fuel.php" class="rounded-2xl border border-white/10 bg-gradient-to-r from-amber-400/15 to-orange-500/10 px-5 py-4 shadow-lg shadow-amber-500/20 transition hover:border-amber-200/60">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-xs uppercase tracking-wide text-amber-200/80">Топливо</p>
            <p class="text-lg font-semibold text-white">Отчёты и контроль топлива</p>
            <p class="text-xs text-gray-300">Доступно только здесь</p>
          </div>
          <span aria-hidden="true" class="text-2xl">⛽</span>
        </div>
      </a>
      <a href="dispense.php" class="rounded-2xl border border-white/10 bg-gradient-to-r from-cyan-400/15 to-blue-500/10 px-5 py-4 shadow-lg shadow-cyan-500/20 transition hover:border-cyan-200/60">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-xs uppercase tracking-wide text-cyan-200/80">Выдача</p>
            <p class="text-lg font-semibold text-white">Управление выдачей топлива</p>
            <p class="text-xs text-gray-300">Только для админов</p>
          </div>
          <span aria-hidden="true" class="text-2xl">🚚</span>
        </div>
      </a>
    </div>
  </section>

  <section class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-xl shadow-amber-500/10 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-semibold">Управление правами</h2>
        <p class="text-sm text-gray-200">Выбирайте нужные разделы или назначайте пользователя администратором.</p>
      </div>
      <p id="admin-status" class="text-sm text-gray-300 min-h-[1.25rem]"></p>
    </div>
    <div id="users-table-body" class="grid grid-cols-1 md:grid-cols-2 gap-3"></div>
  </section>
</main>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const usersTable = document.getElementById('users-table-body');
    const adminStatus = document.getElementById('admin-status');
    const reloadBtn = document.getElementById('reload-users');
    const currentUserId = <?php echo (int) currentUserId(); ?>;

    const PERMISSION_CONFIG = [
      { key: 'dashboard', label: 'Панель', hint: 'Доступ к основному дашборду', badge: 'bg-blue-500/15 text-blue-100' },
      { key: 'fuel', label: 'Топливо', hint: 'Отчёты по топливу', badge: 'bg-amber-500/15 text-amber-100' },
      { key: 'cards', label: 'Карты', hint: 'Управление картами', badge: 'bg-emerald-500/15 text-emerald-100' },
      { key: 'dispense', label: 'Выдача', hint: 'Контроль выдачи', badge: 'bg-cyan-500/15 text-cyan-100' },
      { key: 'logs', label: 'Логи', hint: 'Просмотр журналов', badge: 'bg-indigo-500/15 text-indigo-100' },
      { key: 'diesel', label: 'Цены', hint: 'Изменение стоимости', badge: 'bg-pink-500/15 text-pink-100' },
      { key: 'passes', label: 'Пропуска', hint: 'Пропуска на КПП', badge: 'bg-lime-500/15 text-lime-100' },
      { key: 'service', label: 'Сервис', hint: 'Сервисные операции', badge: 'bg-amber-300/15 text-amber-50' },
      { key: 'carbook', label: 'Car Book', hint: 'Учёт ТС', badge: 'bg-orange-500/15 text-orange-100' },
    ];

    const userStore = new Map();

    const cloneUser = (user) => JSON.parse(JSON.stringify(user));

    const setStatus = (msg, tone = 'info') => {
      if (!adminStatus) return;
      adminStatus.textContent = msg;
      adminStatus.className = 'text-sm min-h-[1.25rem]';
      const toneMap = { success: 'text-emerald-200', warn: 'text-amber-200', error: 'text-rose-200', info: 'text-gray-300' };
      adminStatus.classList.add(toneMap[tone] || toneMap.info);
    };

    const hasChanges = (entry) => {
      if (!entry) return false;
      if (entry.current.is_admin !== entry.original.is_admin) return true;
      return PERMISSION_CONFIG.some(({ key }) => (entry.current.permissions?.[key] || false) !== (entry.original.permissions?.[key] || false));
    };

    const renderUsers = () => {
      if (!usersTable) return;
      const cards = [];
      userStore.forEach((entry) => {
        const user = entry.current;
        const dirty = hasChanges(entry);
        const permCheckboxes = PERMISSION_CONFIG.map(({ key, label, hint, badge }) => {
          const checked = user.permissions?.[key] ? 'checked' : '';
          return `
            <label class="flex items-start gap-2 rounded-xl border border-white/10 bg-black/15 px-3 py-2">
              <input data-permission="${key}" type="checkbox" class="mt-1 h-4 w-4 rounded border-white/30 bg-white/10" ${checked}>
              <div>
                <p class="text-sm font-semibold flex items-center gap-2"><span class="px-2 py-0.5 rounded-full text-[11px] ${badge}">${label}</span></p>
                <p class="text-xs text-gray-300">${hint}</p>
              </div>
            </label>
          `;
        }).join('');

        const adminChecked = user.is_admin ? 'checked' : '';
        const statusText = dirty ? 'Есть несохранённые изменения' : 'Без изменений';
        const statusTone = dirty ? 'text-amber-200' : 'text-gray-300';

        cards.push(`
          <div class="rounded-2xl border border-white/10 bg-white/5 p-5 space-y-4 shadow-lg shadow-amber-500/10" data-user-card data-user-id="${user.id}" data-dirty="${dirty ? '1' : '0'}">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="font-semibold text-lg">${user.username}</p>
                <p class="text-xs text-gray-400">ID ${user.id}</p>
              </div>
              <div class="flex items-center gap-2">
                <span class="rounded-full border ${user.is_admin ? 'border-amber-300/60 bg-amber-400/15 text-amber-50' : 'border-white/10 bg-white/5 text-gray-200'} px-3 py-1 text-[11px] font-semibold">${user.is_admin ? 'Админ' : 'Оператор'}</span>
              </div>
            </div>
            <p class="text-xs ${statusTone}" data-card-status>${statusText}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">${permCheckboxes}</div>
            <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
              <label class="inline-flex items-center gap-2 text-sm font-semibold text-amber-100">
                <input data-admin-toggle type="checkbox" class="h-4 w-4 rounded border-amber-200/70 bg-amber-200/20" ${adminChecked}>
                <span>Сделать админом</span>
              </label>
              <div class="flex items-center gap-2">
                <button class="reset-permissions rounded-lg border border-white/10 px-3 py-2 text-xs text-gray-200 hover:border-amber-200/60" data-user-id="${user.id}">Сбросить</button>
                <button class="save-permissions rounded-lg bg-gradient-to-r from-amber-500/80 to-amber-400/80 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-amber-500/30 disabled:opacity-60" data-user-id="${user.id}" ${dirty ? '' : 'disabled'}>Сохранить</button>
              </div>
            </div>
          </div>
        `);
      });
      usersTable.innerHTML = cards.join('');
    };

    const syncEntryFromCard = (card) => {
      const userId = Number(card?.dataset.userId || 0);
      if (!userId || !userStore.has(userId)) return;
      const entry = userStore.get(userId);
      const updated = cloneUser(entry.current);
      updated.is_admin = card.querySelector('[data-admin-toggle]')?.checked || false;
      PERMISSION_CONFIG.forEach(({ key }) => {
        updated.permissions[key] = card.querySelector(`input[data-permission="${key}"]`)?.checked || false;
      });
      entry.current = updated;
      updateCardState(card, entry);
    };

    const updateCardState = (card, entry) => {
      const dirty = hasChanges(entry);
      card.dataset.dirty = dirty ? '1' : '0';
      const status = card.querySelector('[data-card-status]');
      const saveBtn = card.querySelector('.save-permissions');
      const roleBadge = card.querySelector('span.rounded-full');
      if (status) {
        status.textContent = dirty ? 'Есть несохранённые изменения' : 'Без изменений';
        status.className = `text-xs ${dirty ? 'text-amber-200' : 'text-gray-300'}`;
      }
      if (saveBtn) {
        if (dirty) {
          saveBtn.removeAttribute('disabled');
        } else {
          saveBtn.setAttribute('disabled', 'disabled');
        }
      }
      if (roleBadge) {
        roleBadge.textContent = entry.current.is_admin ? 'Админ' : 'Оператор';
        roleBadge.className = `rounded-full border px-3 py-1 text-[11px] font-semibold ${entry.current.is_admin ? 'border-amber-300/60 bg-amber-400/15 text-amber-50' : 'border-white/10 bg-white/5 text-gray-200'}`;
      }
    };

    const resetCard = (card) => {
      const userId = Number(card?.dataset.userId || 0);
      if (!userId || !userStore.has(userId)) return;
      const entry = userStore.get(userId);
      entry.current = cloneUser(entry.original);
      PERMISSION_CONFIG.forEach(({ key }) => {
        const input = card.querySelector(`input[data-permission="${key}"]`);
        if (input) input.checked = !!entry.current.permissions[key];
      });
      const adminToggle = card.querySelector('[data-admin-toggle]');
      if (adminToggle) adminToggle.checked = !!entry.current.is_admin;
      updateCardState(card, entry);
    };

    const loadUsers = async () => {
      setStatus('Загружаем пользователей...');
      try {
        const res = await fetch('api.php?resource=users&action=list');
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Не удалось получить пользователей');
        userStore.clear();
        data.forEach((user) => {
          userStore.set(user.id, { original: cloneUser(user), current: cloneUser(user) });
        });
        renderUsers();
        setStatus('Отметьте права и сохраните изменения.');
      } catch (err) {
        setStatus(err.message || 'Ошибка загрузки пользователей', 'error');
      }
    };

    usersTable?.addEventListener('change', (e) => {
      const card = e.target.closest('[data-user-card]');
      if (!card) return;
      if (e.target.matches('input[type="checkbox"]')) {
        syncEntryFromCard(card);
      }
    });

    usersTable?.addEventListener('click', async (e) => {
      const card = e.target.closest('[data-user-card]');
      if (!card) return;
      const saveBtn = e.target.closest('.save-permissions');
      const resetBtn = e.target.closest('.reset-permissions');
      const userId = Number(card.dataset.userId || 0);
      const entry = userStore.get(userId);
      if (resetBtn) {
        resetCard(card);
        return;
      }
      if (!saveBtn || !entry || !hasChanges(entry)) return;
      saveBtn.setAttribute('disabled', 'disabled');
      setStatus('Сохраняем права...');
      const payloadPerms = { is_admin: entry.current.is_admin };
      PERMISSION_CONFIG.forEach(({ key }) => {
        payloadPerms[key] = entry.current.permissions[key] || false;
      });
      try {
        const res = await fetch('api.php?resource=users&action=update_permissions', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ user_id: userId, permissions: payloadPerms }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Не удалось сохранить права');
        entry.original = cloneUser({ id: userId, username: entry.current.username, is_admin: !!data.saved?.is_admin, permissions: data.saved?.permissions || {} });
        entry.current = cloneUser(entry.original);
        updateCardState(card, entry);
        card.classList.add('ring-2', 'ring-amber-300/60');
        setTimeout(() => card.classList.remove('ring-2', 'ring-amber-300/60'), 1000);
        setStatus('Права обновлены.', 'success');
        if (userId === currentUserId) {
          await fetch('webauthn.php?action=session');
        }
      } catch (err) {
        setStatus(err.message || 'Ошибка сохранения прав', 'error');
      } finally {
        saveBtn.removeAttribute('disabled');
      }
    });

    reloadBtn?.addEventListener('click', loadUsers);
    loadUsers();
  });
</script>

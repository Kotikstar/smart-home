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

  <section class="relative overflow-hidden rounded-3xl border border-amber-200/30 bg-amber-50/5 p-8 shadow-2xl shadow-amber-500/10">
    <div class="absolute inset-0 opacity-60" aria-hidden="true">
      <div class="absolute left-10 top-6 h-28 w-28 rounded-full bg-amber-400/20 mix-blend-screen"></div>
      <div class="absolute right-6 -bottom-6 h-40 w-40 rounded-full bg-cyan-400/16 mix-blend-screen"></div>
    </div>
    <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div class="space-y-2 max-w-3xl">
        <p class="text-xs uppercase tracking-[0.35em] text-amber-200/80">Администрирование</p>
        <h1 class="text-3xl font-bold leading-tight">Права пользователей и доступы</h1>
        <p class="text-gray-200/90 text-sm md:text-base">Отмечайте права для каждого оператора, назначайте администраторов и обновляйте доступы к разделам портала.</p>
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

    const PERMISSION_LABELS = {
      dashboard: 'Панель',
      fuel: 'Топливо',
      cards: 'Карты',
      dispense: 'Выдача',
      logs: 'Логи',
      diesel: 'Цены',
      passes: 'Пропуска',
      service: 'Сервис',
      carbook: 'Car Book',
    };

    const setStatus = (msg, tone = 'info') => {
      if (!adminStatus) return;
      adminStatus.textContent = msg;
      adminStatus.classList.remove('text-amber-200', 'text-emerald-200', 'text-rose-200');
      const toneMap = { success: 'text-emerald-200', warn: 'text-amber-200', error: 'text-rose-200' };
      if (toneMap[tone]) adminStatus.classList.add(toneMap[tone]);
    };

    const renderUsers = (users = []) => {
      if (!usersTable) return;
      usersTable.innerHTML = users.map((user) => {
        const permCheckboxes = Object.entries(PERMISSION_LABELS).map(([key, label]) => {
          const checked = user.permissions?.[key] ? 'checked' : '';
          return `<label class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-black/20 px-3 py-2"><input data-permission="${key}" type="checkbox" class="h-4 w-4 rounded border-white/30 bg-white/10" ${checked}><span class="text-xs">${label}</span></label>`;
        }).join('');
        const isAdminChecked = user.is_admin ? 'checked' : '';
        return `
          <div class="rounded-2xl border border-white/10 bg-white/5 p-4 space-y-3 shadow-lg shadow-amber-500/10" data-user-id="${user.id}">
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="font-semibold">${user.username}</p>
                <p class="text-xs text-gray-400">ID ${user.id}</p>
              </div>
              <label class="inline-flex items-center gap-2 text-sm text-amber-100 font-semibold">
                <input data-admin-toggle type="checkbox" class="h-4 w-4 rounded border-amber-200/70 bg-amber-200/20" ${isAdminChecked}>
                <span>Админ</span>
              </label>
            </div>
            <div class="flex flex-wrap gap-2 text-xs">${permCheckboxes}</div>
            <button class="save-permissions rounded-xl bg-gradient-to-r from-amber-500/80 to-amber-400/80 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-amber-500/30" data-user-id="${user.id}">Сохранить</button>
          </div>
        `;
      }).join('');
    };

    const loadUsers = async () => {
      setStatus('Загружаем пользователей...');
      try {
        const res = await fetch('api.php?resource=users&action=list');
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Не удалось получить пользователей');
        renderUsers(data);
        setStatus('Отметьте права и сохраните изменения.');
      } catch (err) {
        setStatus(err.message || 'Ошибка загрузки пользователей', 'error');
      }
    };

    usersTable?.addEventListener('click', async (e) => {
      const btn = e.target.closest('.save-permissions');
      if (!btn) return;
      const card = btn.closest('[data-user-id]');
      const userId = Number(btn.dataset.userId || card?.dataset.userId || 0);
      if (!userId) return;
      const payloadPerms = { is_admin: card?.querySelector('[data-admin-toggle]')?.checked || false };
      Object.keys(PERMISSION_LABELS).forEach((key) => {
        const input = card?.querySelector(`input[data-permission="${key}"]`);
        payloadPerms[key] = input?.checked || false;
      });
      btn.setAttribute('disabled', 'disabled');
      setStatus('Сохраняем права...');
      try {
        const res = await fetch('api.php?resource=users&action=update_permissions', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ user_id: userId, permissions: payloadPerms }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Не удалось сохранить права');
        const savedPerms = data?.saved?.permissions || {};
        const savedAdminFlag = !!data?.saved?.is_admin;
        const savedAdminText = savedAdminFlag ? 'админ' : 'не админ';

        const mismatches = [];
        if ((payloadPerms.is_admin || false) !== savedAdminFlag) {
          mismatches.push(`Админ: ${savedAdminText}`);
        }
        Object.keys(PERMISSION_LABELS).forEach((key) => {
          if ((payloadPerms[key] || false) !== (savedPerms[key] || false)) {
            mismatches.push(`${PERMISSION_LABELS[key]}: ${savedPerms[key] ? 'включено' : 'выключено'}`);
          }
        });

        const statusMsg = mismatches.length
          ? `Сохранено, но сервер вернул отличия → ${mismatches.join(', ')}`
          : `Права обновлены (${savedAdminText}).`;
        setStatus(statusMsg, mismatches.length ? 'warn' : 'success');
        await loadUsers();
        const refreshedCard = usersTable?.querySelector(`[data-user-id="${userId}"]`);
        if (refreshedCard) {
          refreshedCard.classList.add('ring-2', 'ring-amber-300/60');
          setTimeout(() => refreshedCard.classList.remove('ring-2', 'ring-amber-300/60'), 1200);
        }
        if (userId === currentUserId) {
          await fetch('webauthn.php?action=session');
        }
      } catch (err) {
        setStatus(err.message || 'Ошибка сохранения прав', 'error');
      } finally {
        btn.removeAttribute('disabled');
      }
    });

    reloadBtn?.addEventListener('click', loadUsers);
    loadUsers();
  });
</script>

<?php include 'footer.php'; ?>

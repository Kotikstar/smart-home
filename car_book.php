<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/car_book/inc.php';
define('REQUIRED_PERMISSION', 'carbook');
ensureSession();
ensureCarBookSchema($pdo);

$vehicles = fetchCarBookVehicles($pdo);
$stats = fetchCarBookStats($vehicles);
$events = fetchCarBookEvents($pdo);
$totals = fetchCarBookTotals($pdo);

$currentVehicleId = isset($_GET['vehicle_id']) ? (int) $_GET['vehicle_id'] : (int) ($vehicles[0]['id'] ?? 0);
$activeVehicle = null;
foreach ($vehicles as $v) {
    if ((int) $v['id'] === $currentVehicleId) {
        $activeVehicle = $v;
        break;
    }
}
if (!$activeVehicle && !empty($vehicles)) {
    $activeVehicle = $vehicles[0];
    $currentVehicleId = (int) $activeVehicle['id'];
}

$vehicleEvents = $activeVehicle ? fetchCarBookEventsByVehicle($pdo, $currentVehicleId) : [];
$vehicleWishes = $activeVehicle ? fetchVehicleWishes($pdo, $currentVehicleId) : [];
$vehicleExpenses = $activeVehicle ? fetchVehicleExpenses($pdo, $currentVehicleId) : [];
$vehicleExpensesTotal = $activeVehicle ? totalVehicleExpenses($pdo, $currentVehicleId) : 0;
$vehicleEventsTotal = $activeVehicle ? totalVehicleEvents($pdo, $currentVehicleId) : 0;

include __DIR__ . '/header.php';
?>

<main class="relative z-10 max-w-6xl mx-auto px-4 pb-16 pt-12 space-y-8">
  <section class="neon-border overflow-hidden relative">
    <div class="glow-ring"></div>
    <div class="relative px-8 py-10 space-y-6">
      <div class="inline-flex items-center gap-2 rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-xs font-semibold text-cyan-100">
        <span class="h-2 w-2 rounded-full bg-cyan-400 animate-pulse"></span>
        <span>Car Book</span>
      </div>
      <div class="space-y-3">
        <h1 class="text-3xl font-bold"><?php echo htmlspecialchars(t('carbook.title'), ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="text-slate-300 max-w-3xl"><?php echo htmlspecialchars(t('carbook.subtitle'), ENT_QUOTES, 'UTF-8'); ?></p>
      </div>
      <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-lg shadow-cyan-500/10">
          <div class="text-xs uppercase tracking-[0.2em] text-white/60"><?php echo htmlspecialchars(t('carbook.stats.total'), ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="text-3xl font-semibold"><?php echo (int) $stats['total']; ?></div>
        </div>
        <div class="rounded-2xl border border-emerald-300/40 bg-emerald-400/10 p-4 shadow-lg shadow-emerald-500/10">
          <div class="text-xs uppercase tracking-[0.2em] text-emerald-100/80"><?php echo htmlspecialchars(t('carbook.stats.ready'), ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="text-3xl font-semibold text-emerald-50"><?php echo (int) $stats['ready']; ?></div>
        </div>
        <div class="rounded-2xl border border-amber-300/40 bg-amber-400/10 p-4 shadow-lg shadow-amber-500/10">
          <div class="text-xs uppercase tracking-[0.2em] text-amber-100/80"><?php echo htmlspecialchars(t('carbook.stats.maintenance'), ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="text-3xl font-semibold text-amber-50"><?php echo (int) $stats['maintenance']; ?></div>
        </div>
        <div class="rounded-2xl border border-fuchsia-300/40 bg-fuchsia-400/10 p-4 shadow-lg shadow-fuchsia-500/10">
          <div class="text-xs uppercase tracking-[0.2em] text-fuchsia-100/80"><?php echo htmlspecialchars(t('carbook.stats.reserved'), ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="text-3xl font-semibold text-fuchsia-50"><?php echo (int) $stats['reserved']; ?></div>
        </div>
      </div>
      <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-sky-300/40 bg-sky-400/10 p-4 shadow-lg shadow-sky-500/10">
          <div class="text-xs uppercase tracking-[0.2em] text-sky-100/80 flex items-center gap-2">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-sky-500/20 text-lg">🚗</span>
            <span><?php echo htmlspecialchars(t('carbook.stats.total'), ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <div class="text-3xl font-semibold text-sky-50"><?php echo (int) $totals['vehicles']; ?></div>
        </div>
        <div class="rounded-2xl border border-cyan-300/40 bg-cyan-400/10 p-4 shadow-lg shadow-cyan-500/10">
          <div class="text-xs uppercase tracking-[0.2em] text-cyan-100/80 flex items-center gap-2">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-cyan-500/20 text-lg">📝</span>
            <span><?php echo htmlspecialchars(t('carbook.stats.events'), ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <div class="text-3xl font-semibold text-cyan-50"><?php echo (int) $totals['events']; ?></div>
        </div>
        <div class="rounded-2xl border border-amber-300/40 bg-amber-400/10 p-4 shadow-lg shadow-amber-500/10">
          <div class="text-xs uppercase tracking-[0.2em] text-amber-100/80 flex items-center gap-2">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-amber-500/20 text-lg">💰</span>
            <span><?php echo htmlspecialchars(t('carbook.stats.expenses'), ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <div class="text-3xl font-semibold text-amber-50"><?php echo number_format($totals['cost'], 0, '.', ' '); ?> ₽</div>
        </div>
      </div>
    </div>
  </section>

  <?php if ($activeVehicle): ?>
    <section class="neon-border overflow-hidden relative">
      <div class="glow-ring"></div>
      <div class="relative px-6 py-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
          <div>
            <div class="text-xs uppercase tracking-[0.2em] text-white/60"><?php echo htmlspecialchars(t('carbook.section.detail'), ENT_QUOTES, 'UTF-8'); ?></div>
            <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($activeVehicle['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
          </div>
          <form method="get" class="flex gap-2 items-center">
            <label class="text-xs uppercase tracking-[0.2em] text-white/60"><?php echo htmlspecialchars(t('carbook.form.name'), ENT_QUOTES, 'UTF-8'); ?></label>
            <select name="vehicle_id" onchange="this.form.submit()" class="rounded-xl bg-black/30 border border-white/10 px-3 py-2 text-sm min-w-[180px]">
              <?php foreach ($vehicles as $vehicle): ?>
                <option value="<?php echo (int) $vehicle['id']; ?>" <?php echo $vehicle['id'] == $currentVehicleId ? 'selected' : ''; ?>><?php echo htmlspecialchars($vehicle['title'], ENT_QUOTES, 'UTF-8'); ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
          <div class="rounded-2xl border border-white/10 bg-white/5 p-4 space-y-2">
            <div class="flex items-center justify-between text-sm text-white/70">
              <span><?php echo htmlspecialchars(t('carbook.card.mileage'), ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="font-semibold text-white"><?php echo (int) $activeVehicle['mileage']; ?> км</span>
            </div>
            <div class="flex items-center justify-between text-sm text-white/70">
              <span><?php echo htmlspecialchars(t('carbook.card.next_service'), ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="font-semibold text-white"><?php echo $activeVehicle['next_service_date'] ? htmlspecialchars($activeVehicle['next_service_date'], ENT_QUOTES, 'UTF-8') : '—'; ?></span>
            </div>
            <div class="flex items-center justify-between text-sm text-white/70">
              <span><?php echo htmlspecialchars(t('carbook.stats.events'), ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="font-semibold text-white"><?php echo (int) $vehicleEventsTotal; ?></span>
            </div>
            <div class="flex items-center justify-between text-sm text-white/70">
              <span><?php echo htmlspecialchars(t('carbook.card.expenses'), ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="font-semibold text-amber-100"><?php echo number_format($vehicleExpensesTotal, 0, '.', ' '); ?> ₽</span>
            </div>
            <?php if (!empty($activeVehicle['notes'])): ?>
              <div class="text-xs text-white/70 border-t border-white/5 pt-2"><?php echo nl2br(htmlspecialchars($activeVehicle['notes'], ENT_QUOTES, 'UTF-8')); ?></div>
            <?php endif; ?>
          </div>

          <div class="rounded-2xl border border-white/10 bg-white/5 p-4 space-y-3">
            <div class="flex items-center justify-between text-sm font-semibold">
              <span><?php echo htmlspecialchars(t('carbook.card.wishlist'), ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="text-white/60 text-xs"><?php echo count($vehicleWishes); ?> <?php echo htmlspecialchars(t('carbook.stats.events'), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="space-y-2 max-h-48 overflow-auto pr-1">
              <?php foreach ($vehicleWishes as $wish): ?>
                <div class="flex items-center justify-between rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-sm">
                  <div class="flex items-center gap-2">
                    <input type="checkbox" data-action="toggle-wish" data-id="<?php echo (int) $wish['id']; ?>" <?php echo $wish['is_done'] ? 'checked' : ''; ?>>
                    <span class="<?php echo $wish['is_done'] ? 'line-through text-white/50' : 'text-white'; ?>"><?php echo htmlspecialchars($wish['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                  </div>
                  <span class="text-xs text-white/50"><?php echo htmlspecialchars(substr($wish['created_at'], 0, 10), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
              <?php endforeach; ?>
              <?php if (empty($vehicleWishes)): ?>
                <div class="text-xs text-white/50"><?php echo htmlspecialchars(t('carbook.message.saved'), ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <form id="carbook-wish" class="space-y-2" data-vehicle="<?php echo (int) $currentVehicleId; ?>">
              <input type="hidden" name="vehicle_id" value="<?php echo (int) $currentVehicleId; ?>">
              <input name="title" required class="w-full rounded-xl bg-black/30 border border-white/10 px-3 py-2 text-sm" placeholder="<?php echo htmlspecialchars(t('carbook.form.wish.title'), ENT_QUOTES, 'UTF-8'); ?>">
              <button class="nav-link w-full justify-center bg-gradient-to-r from-fuchsia-500/80 to-sky-500/80 px-3 py-2 text-xs uppercase tracking-[0.2em]" type="submit"><?php echo htmlspecialchars(t('carbook.action.add_wish'), ENT_QUOTES, 'UTF-8'); ?></button>
            </form>
          </div>

          <div class="rounded-2xl border border-white/10 bg-white/5 p-4 space-y-3">
            <div class="flex items-center justify-between text-sm font-semibold">
              <span><?php echo htmlspecialchars(t('carbook.card.expenses'), ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="text-amber-100 text-sm font-bold"><?php echo number_format($vehicleExpensesTotal, 0, '.', ' '); ?> ₽</span>
            </div>
            <div class="space-y-2 max-h-48 overflow-auto pr-1">
              <?php foreach ($vehicleExpenses as $exp): ?>
                <div class="flex items-center justify-between rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-sm">
                  <div>
                    <div class="font-semibold"><?php echo htmlspecialchars($exp['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="text-xs text-white/60"><?php echo htmlspecialchars(substr($exp['created_at'], 0, 10), ENT_QUOTES, 'UTF-8'); ?></div>
                  </div>
                  <div class="text-amber-100 font-semibold"><?php echo number_format($exp['cost'], 0, '.', ' '); ?> ₽</div>
                </div>
              <?php endforeach; ?>
              <?php if (empty($vehicleExpenses)): ?>
                <div class="text-xs text-white/50"><?php echo htmlspecialchars(t('carbook.message.saved'), ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            </div>
            <form id="carbook-expense" class="space-y-2" data-vehicle="<?php echo (int) $currentVehicleId; ?>">
              <input type="hidden" name="vehicle_id" value="<?php echo (int) $currentVehicleId; ?>">
              <input name="title" required class="w-full rounded-xl bg-black/30 border border-white/10 px-3 py-2 text-sm" placeholder="<?php echo htmlspecialchars(t('carbook.form.expense.title'), ENT_QUOTES, 'UTF-8'); ?>">
              <input type="number" step="0.01" name="cost" required class="w-full rounded-xl bg-black/30 border border-white/10 px-3 py-2 text-sm" placeholder="<?php echo htmlspecialchars(t('carbook.form.expense.cost'), ENT_QUOTES, 'UTF-8'); ?>">
              <button class="nav-link w-full justify-center bg-gradient-to-r from-amber-500/80 to-rose-500/80 px-3 py-2 text-xs uppercase tracking-[0.2em]" type="submit"><?php echo htmlspecialchars(t('carbook.action.add_expense'), ENT_QUOTES, 'UTF-8'); ?></button>
            </form>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-xs uppercase tracking-[0.2em] text-white/60"><?php echo htmlspecialchars(t('carbook.section.vehicles'), ENT_QUOTES, 'UTF-8'); ?></div>
          <h2 class="text-xl font-semibold">Car Book</h2>
        </div>
        <div class="flex gap-2 text-xs">
          <?php foreach (carBookStatusOptions() as $status): ?>
            <span class="px-3 py-1 rounded-full border border-white/10 bg-white/5">
              <?php echo htmlspecialchars(carBookStatusLabel($status), ENT_QUOTES, 'UTF-8'); ?>
            </span>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($vehicles as $vehicle): ?>
          <article class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-lg shadow-cyan-500/10 flex flex-col gap-3">
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="text-sm font-semibold"><?php echo htmlspecialchars($vehicle['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="text-xs text-white/70"><?php echo htmlspecialchars(trim($vehicle['brand'] . ' · ' . $vehicle['license_plate']), ENT_QUOTES, 'UTF-8'); ?></div>
              </div>
              <span class="text-xs px-3 py-1 rounded-full border border-white/10 bg-white/10">
                <?php echo htmlspecialchars(carBookStatusLabel($vehicle['status']), ENT_QUOTES, 'UTF-8'); ?>
              </span>
            </div>
            <div class="grid grid-cols-2 gap-2 text-xs text-slate-200">
              <div class="rounded-xl border border-white/10 bg-black/20 p-3">
                <div class="text-white/60"><?php echo htmlspecialchars(t('carbook.card.mileage'), ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="font-semibold"><?php echo (int) $vehicle['mileage']; ?> км</div>
              </div>
              <div class="rounded-xl border border-white/10 bg-black/20 p-3">
                <div class="text-white/60"><?php echo htmlspecialchars(t('carbook.card.next_service'), ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="font-semibold"><?php echo $vehicle['next_service_date'] ? htmlspecialchars($vehicle['next_service_date'], ENT_QUOTES, 'UTF-8') : '—'; ?></div>
              </div>
            </div>
            <?php if (!empty($vehicle['notes'])): ?>
              <p class="text-sm text-slate-300"><?php echo nl2br(htmlspecialchars($vehicle['notes'], ENT_QUOTES, 'UTF-8')); ?></p>
            <?php endif; ?>
            <div class="flex flex-wrap gap-2 text-xs">
              <button class="nav-link bg-amber-400/15 border border-amber-300/30" data-action="update-status" data-id="<?php echo (int) $vehicle['id']; ?>" data-status="maintenance"><?php echo htmlspecialchars(t('carbook.action.service'), ENT_QUOTES, 'UTF-8'); ?></button>
              <button class="nav-link bg-emerald-400/15 border border-emerald-300/30" data-action="update-status" data-id="<?php echo (int) $vehicle['id']; ?>" data-status="ready"><?php echo htmlspecialchars(t('carbook.action.ready'), ENT_QUOTES, 'UTF-8'); ?></button>
              <button class="nav-link bg-fuchsia-400/15 border border-fuchsia-300/30" data-action="update-status" data-id="<?php echo (int) $vehicle['id']; ?>" data-status="reserved"><?php echo htmlspecialchars(t('carbook.action.reserve'), ENT_QUOTES, 'UTF-8'); ?></button>
              <button class="nav-link bg-white/10 border border-white/20" data-action="update-status" data-id="<?php echo (int) $vehicle['id']; ?>" data-status="offline"><?php echo htmlspecialchars(t('carbook.action.offline'), ENT_QUOTES, 'UTF-8'); ?></button>
            </div>
            <?php if ($vehicle['last_event']): ?>
              <div class="text-xs text-white/70"><?php echo htmlspecialchars(t('carbook.card.last_event'), ENT_QUOTES, 'UTF-8'); ?>: <?php echo htmlspecialchars($vehicle['last_event']['event_type'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($vehicle['last_event']['created_at'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="space-y-4">
      <div class="rounded-2xl border border-white/10 bg-white/5 p-5 shadow-lg shadow-emerald-500/10">
        <div class="text-xs uppercase tracking-[0.2em] text-white/60 mb-3"><?php echo htmlspecialchars(t('carbook.section.add'), ENT_QUOTES, 'UTF-8'); ?></div>
        <form id="carbook-create" class="space-y-3">
          <div class="grid grid-cols-1 gap-2">
            <input name="title" required class="w-full rounded-xl bg-black/30 border border-white/10 px-3 py-2 text-sm" placeholder="<?php echo htmlspecialchars(t('carbook.form.name'), ENT_QUOTES, 'UTF-8'); ?>">
            <input name="brand" class="w-full rounded-xl bg-black/30 border border-white/10 px-3 py-2 text-sm" placeholder="<?php echo htmlspecialchars(t('carbook.form.brand'), ENT_QUOTES, 'UTF-8'); ?>">
            <input name="license_plate" class="w-full rounded-xl bg-black/30 border border-white/10 px-3 py-2 text-sm" placeholder="<?php echo htmlspecialchars(t('carbook.form.plate'), ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <div class="grid grid-cols-2 gap-2">
            <select name="status" class="w-full rounded-xl bg-black/30 border border-white/10 px-3 py-2 text-sm">
              <?php foreach (carBookStatusOptions() as $status): ?>
                <option value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(carBookStatusLabel($status), ENT_QUOTES, 'UTF-8'); ?></option>
              <?php endforeach; ?>
            </select>
            <input type="number" name="mileage" class="w-full rounded-xl bg-black/30 border border-white/10 px-3 py-2 text-sm" placeholder="<?php echo htmlspecialchars(t('carbook.form.mileage'), ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <div class="grid grid-cols-2 gap-2">
            <input type="date" name="next_service_date" class="w-full rounded-xl bg-black/30 border border-white/10 px-3 py-2 text-sm" placeholder="<?php echo htmlspecialchars(t('carbook.form.next_service'), ENT_QUOTES, 'UTF-8'); ?>">
            <input name="notes" class="w-full rounded-xl bg-black/30 border border-white/10 px-3 py-2 text-sm" placeholder="<?php echo htmlspecialchars(t('carbook.form.notes'), ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <button class="nav-link bg-gradient-to-r from-emerald-500/80 to-cyan-500/80 px-4 py-2 text-xs uppercase tracking-[0.2em] shadow-lg shadow-emerald-500/20" type="submit"><?php echo htmlspecialchars(t('carbook.form.submit'), ENT_QUOTES, 'UTF-8'); ?></button>
        </form>
      </div>

      <div class="rounded-2xl border border-white/10 bg-white/5 p-5 shadow-lg shadow-cyan-500/10">
        <div class="text-xs uppercase tracking-[0.2em] text-white/60 mb-3"><?php echo htmlspecialchars(t('carbook.section.service'), ENT_QUOTES, 'UTF-8'); ?></div>
        <form id="carbook-event" class="space-y-3">
          <div class="grid grid-cols-1 gap-2">
            <select name="vehicle_id" required class="w-full rounded-xl bg-black/30 border border-white/10 px-3 py-2 text-sm">
              <?php foreach ($vehicles as $vehicle): ?>
                <option value="<?php echo (int) $vehicle['id']; ?>"><?php echo htmlspecialchars($vehicle['title'], ENT_QUOTES, 'UTF-8'); ?></option>
              <?php endforeach; ?>
            </select>
            <input name="event_type" required class="w-full rounded-xl bg-black/30 border border-white/10 px-3 py-2 text-sm" placeholder="<?php echo htmlspecialchars(t('carbook.form.event.type'), ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <div class="grid grid-cols-2 gap-2">
            <select name="status_after" class="w-full rounded-xl bg-black/30 border border-white/10 px-3 py-2 text-sm">
              <option value=""><?php echo htmlspecialchars(t('carbook.form.status'), ENT_QUOTES, 'UTF-8'); ?></option>
              <?php foreach (carBookStatusOptions() as $status): ?>
                <option value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(carBookStatusLabel($status), ENT_QUOTES, 'UTF-8'); ?></option>
              <?php endforeach; ?>
            </select>
            <input type="number" name="mileage" class="w-full rounded-xl bg-black/30 border border-white/10 px-3 py-2 text-sm" placeholder="<?php echo htmlspecialchars(t('carbook.form.event.mileage'), ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <div class="grid grid-cols-1 gap-2">
            <input type="number" step="0.01" name="cost" class="w-full rounded-xl bg-black/30 border border-white/10 px-3 py-2 text-sm" placeholder="<?php echo htmlspecialchars(t('carbook.form.event.cost'), ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <textarea name="note" class="w-full rounded-xl bg-black/30 border border-white/10 px-3 py-2 text-sm" placeholder="<?php echo htmlspecialchars(t('carbook.form.event.note'), ENT_QUOTES, 'UTF-8'); ?>"></textarea>
          <button class="nav-link bg-gradient-to-r from-cyan-500/80 to-blue-500/80 px-4 py-2 text-xs uppercase tracking-[0.2em] shadow-lg shadow-cyan-500/20" type="submit"><?php echo htmlspecialchars(t('carbook.form.submit'), ENT_QUOTES, 'UTF-8'); ?></button>
        </form>
      </div>
    </div>
  </section>

  <?php if ($activeVehicle): ?>
    <section class="rounded-2xl border border-white/10 bg-white/5 shadow-lg shadow-cyan-500/10">
      <div class="border-b border-white/5 px-6 py-4 flex items-center justify-between">
        <div>
          <div class="text-xs uppercase tracking-[0.2em] text-white/60"><?php echo htmlspecialchars(t('carbook.section.history'), ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="font-semibold"><?php echo htmlspecialchars(t('carbook.card.live_log'), ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($activeVehicle['title'], ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <div class="text-sm text-white/70"><?php echo htmlspecialchars(t('carbook.card.expenses'), ENT_QUOTES, 'UTF-8'); ?>: <span class="text-amber-100 font-semibold"><?php echo number_format($vehicleExpensesTotal, 0, '.', ' '); ?> ₽</span></div>
      </div>
      <div class="p-6 overflow-auto">
        <table class="w-full text-sm text-left text-white/90">
          <thead class="text-xs uppercase text-white/60">
            <tr>
              <th class="px-2 py-1"><?php echo htmlspecialchars(t('carbook.form.event.type'), ENT_QUOTES, 'UTF-8'); ?></th>
              <th class="px-2 py-1"><?php echo htmlspecialchars(t('carbook.form.event.mileage'), ENT_QUOTES, 'UTF-8'); ?></th>
              <th class="px-2 py-1"><?php echo htmlspecialchars(t('carbook.form.event.cost'), ENT_QUOTES, 'UTF-8'); ?></th>
              <th class="px-2 py-1"><?php echo htmlspecialchars(t('carbook.form.event.note'), ENT_QUOTES, 'UTF-8'); ?></th>
              <th class="px-2 py-1 text-right">Дата</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <?php foreach ($vehicleEvents as $event): ?>
              <tr>
                <td class="px-2 py-2 font-semibold"><?php echo htmlspecialchars($event['event_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="px-2 py-2 text-white/70"><?php echo $event['mileage'] ? (int) $event['mileage'] . ' км' : '—'; ?></td>
                <td class="px-2 py-2 text-amber-100"><?php echo $event['cost'] !== null ? number_format($event['cost'], 0, '.', ' ') . ' ₽' : '—'; ?></td>
                <td class="px-2 py-2 text-white/70"><?php echo htmlspecialchars($event['note'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="px-2 py-2 text-right text-white/60"><?php echo htmlspecialchars(substr($event['created_at'], 0, 16), ENT_QUOTES, 'UTF-8'); ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($vehicleEvents)): ?>
              <tr>
                <td colspan="5" class="px-2 py-4 text-center text-white/50">—</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  <?php endif; ?>

  <section class="rounded-2xl border border-white/10 bg-white/5 shadow-lg shadow-cyan-500/10">
    <div class="border-b border-white/5 px-6 py-4 flex items-center justify-between">
      <div>
        <div class="text-xs uppercase tracking-[0.2em] text-white/60"><?php echo htmlspecialchars(t('carbook.section.history'), ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="font-semibold"><?php echo htmlspecialchars(t('carbook.card.live_log'), ENT_QUOTES, 'UTF-8'); ?></div>
      </div>
    </div>
    <div class="p-6 space-y-3 text-sm text-slate-200">
      <?php foreach ($events as $event): ?>
        <div class="flex items-start justify-between rounded-xl border border-white/10 bg-black/20 p-3">
          <div>
            <div class="font-semibold"><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($event['license_plate'], ENT_QUOTES, 'UTF-8'); ?>)</div>
            <div class="text-white/70 text-xs flex flex-col gap-0.5">
              <span><?php echo htmlspecialchars($event['event_type'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($event['note'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
              <span>
                <?php if ($event['mileage']): ?>
                  <span class="text-white/60"><?php echo (int) $event['mileage']; ?> км</span>
                <?php endif; ?>
                <?php if ($event['cost'] !== null): ?>
                  <span class="text-amber-100 font-semibold"> · <?php echo number_format($event['cost'], 0, '.', ' '); ?> ₽</span>
                <?php endif; ?>
              </span>
            </div>
          </div>
          <div class="text-right text-xs text-white/70">
            <div><?php echo htmlspecialchars($event['created_at'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?php if ($event['status_after']): ?>
              <div class="font-semibold"><?php echo htmlspecialchars(carBookStatusLabel($event['status_after']), ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<script>
  const messageBox = document.createElement('div');
  messageBox.className = 'fixed bottom-4 right-4 rounded-xl border border-white/10 bg-black/80 px-4 py-3 text-sm shadow-lg shadow-cyan-500/10 hidden';
  messageBox.id = 'carbook-toast';
  document.body.appendChild(messageBox);

  function showToast(text, ok = true) {
    messageBox.textContent = text;
    messageBox.classList.remove('hidden');
    messageBox.style.borderColor = ok ? 'rgba(16,185,129,0.6)' : 'rgba(248,113,113,0.6)';
    messageBox.style.color = ok ? '#bbf7d0' : '#fecdd3';
    setTimeout(() => messageBox.classList.add('hidden'), 2500);
  }

  async function sendCarBook(url, data) {
    const formData = new FormData();
    Object.entries(data).forEach(([key, value]) => {
      if (value !== null && value !== undefined) {
        formData.append(key, value);
      }
    });
    const res = await fetch(url, { method: 'POST', body: formData, headers: { 'Accept': 'application/json' } });
    const json = await res.json();
    if (!json.ok) {
      throw new Error(json.error || 'Error');
    }
    return json;
  }

  document.querySelectorAll('[data-action="update-status"]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      try {
        await sendCarBook('car_book/api.php', {
          action: 'update_status',
          vehicle_id: btn.dataset.id,
          status: btn.dataset.status,
        });
        showToast('<?php echo addslashes(t('carbook.message.saved')); ?>');
        location.reload();
      } catch (e) {
        showToast('<?php echo addslashes(t('carbook.message.error')); ?>: ' + e.message, false);
      }
    });
  });

  const createForm = document.getElementById('carbook-create');
  if (createForm) {
    createForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const data = Object.fromEntries(new FormData(createForm).entries());
      data.action = 'create_vehicle';
      try {
        await sendCarBook('car_book/api.php', data);
        showToast('<?php echo addslashes(t('carbook.message.saved')); ?>');
        location.reload();
      } catch (err) {
        showToast('<?php echo addslashes(t('carbook.message.error')); ?>: ' + err.message, false);
      }
    });
  }

  const eventForm = document.getElementById('carbook-event');
  if (eventForm) {
    eventForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const data = Object.fromEntries(new FormData(eventForm).entries());
      data.action = 'log_event';
      try {
        await sendCarBook('car_book/api.php', data);
        showToast('<?php echo addslashes(t('carbook.message.saved')); ?>');
        location.reload();
      } catch (err) {
        showToast('<?php echo addslashes(t('carbook.message.error')); ?>: ' + err.message, false);
      }
    });
  }

  const expenseForm = document.getElementById('carbook-expense');
  if (expenseForm) {
    expenseForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const data = Object.fromEntries(new FormData(expenseForm).entries());
      data.action = 'add_expense';
      try {
        await sendCarBook('car_book/api.php', data);
        showToast('<?php echo addslashes(t('carbook.message.saved')); ?>');
        location.reload();
      } catch (err) {
        showToast('<?php echo addslashes(t('carbook.message.error')); ?>: ' + err.message, false);
      }
    });
  }

  const wishForm = document.getElementById('carbook-wish');
  if (wishForm) {
    wishForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const data = Object.fromEntries(new FormData(wishForm).entries());
      data.action = 'add_wish';
      try {
        await sendCarBook('car_book/api.php', data);
        showToast('<?php echo addslashes(t('carbook.message.saved')); ?>');
        location.reload();
      } catch (err) {
        showToast('<?php echo addslashes(t('carbook.message.error')); ?>: ' + err.message, false);
      }
    });
  }

  document.querySelectorAll('[data-action="toggle-wish"]').forEach((checkbox) => {
    checkbox.addEventListener('change', async () => {
      try {
        await sendCarBook('car_book/api.php', { action: 'toggle_wish', id: checkbox.dataset.id });
        showToast('<?php echo addslashes(t('carbook.message.saved')); ?>');
      } catch (err) {
        showToast('<?php echo addslashes(t('carbook.message.error')); ?>: ' + err.message, false);
      }
    });
  });
</script>

<?php include __DIR__ . '/footer.php'; ?>

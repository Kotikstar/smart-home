<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/i18n.php';
ensureSession();
$allowAnonymous = defined('ALLOW_ANON') && ALLOW_ANON === true;
requireAuthPage($allowAnonymous);
$requiredPermission = defined('REQUIRED_PERMISSION') ? REQUIRED_PERMISSION : null;
if ($requiredPermission) {
  requirePermissionPage($requiredPermission);
}
$currentLang = currentLang();
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8'); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars(t('app.title'), ENT_QUOTES, 'UTF-8'); ?></title>
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
    .floating-particles {
      position: fixed;
      inset: 0;
      pointer-events: none;
      background: radial-gradient(circle at 12% 20%, rgba(34, 211, 238, 0.12), transparent 26%),
                  radial-gradient(circle at 80% 30%, rgba(59, 130, 246, 0.12), transparent 24%),
                  radial-gradient(circle at 26% 78%, rgba(16, 185, 129, 0.1), transparent 30%);
      mix-blend-mode: screen;
      filter: blur(40px);
      opacity: 0.55;
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
      inset: -40%;
      background: linear-gradient(135deg, rgba(14,165,233,0.35), rgba(34,211,238,0.25), rgba(16,185,129,0.3));
      opacity: 0.28;
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
      inset: -40%;
      background: radial-gradient(circle at 30% 30%, rgba(59,130,246,0.28), transparent 45%),
                  radial-gradient(circle at 70% 70%, rgba(16,185,129,0.28), transparent 46%);
      opacity: 0.35;
      filter: blur(14px);
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
    }
  </style>
</head>
<body class="text-white">
  <div class="cyber-grid"></div>
  <div class="floating-particles"></div>
  <nav class="glass-nav p-4 sticky top-0 z-30">
    <div class="max-w-7xl mx-auto text-sm">
      <div class="flex items-center justify-between gap-3 md:hidden">
        <div class="flex items-center gap-2">
          <button id="menu-toggle" class="nav-link px-3 py-2 border border-white/10 bg-white/5" aria-expanded="false" aria-controls="nav-content">
            <span class="sr-only">Menu</span>
            <span class="block h-[2px] w-5 bg-white mb-[5px]"></span>
            <span class="block h-[2px] w-5 bg-white mb-[5px]"></span>
            <span class="block h-[2px] w-5 bg-white"></span>
          </button>
          <a href="all.php" class="font-semibold tracking-wide"><?php echo htmlspecialchars(t('app.title'), ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
        <div class="flex items-center gap-2 text-xs text-white/70">
          <span class="hidden sm:block"><?php echo htmlspecialchars(t('nav.language'), ENT_QUOTES, 'UTF-8'); ?></span>
          <form method="post" action="set_language.php" class="flex items-center gap-2 bg-white/5 px-3 py-1 rounded-full border border-white/10">
            <select name="lang" id="lang-mobile" class="bg-transparent text-white text-sm focus:outline-none" onchange="this.form.submit()">
              <?php foreach (availableLanguages() as $lang): ?>
                <option value="<?php echo htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $lang === $currentLang ? 'selected' : ''; ?>><?php echo htmlspecialchars(t('lang.' . $lang), ENT_QUOTES, 'UTF-8'); ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>
      </div>
      <div id="nav-content" class="hidden md:flex md:flex-wrap md:items-center gap-3 mt-3 md:mt-0">
        <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2">
          <a href="all.php" class="nav-link font-semibold"><?php echo htmlspecialchars(t('nav.home'), ENT_QUOTES, 'UTF-8'); ?></a>
          <a href="dashboard.php" class="nav-link requires-auth" data-requires-auth="true"><?php echo htmlspecialchars(t('nav.dashboard'), ENT_QUOTES, 'UTF-8'); ?></a>
          <a href="fuel.php" class="nav-link requires-auth" data-requires-auth="true"><?php echo htmlspecialchars(t('nav.fuel'), ENT_QUOTES, 'UTF-8'); ?></a>
          <a href="cards.php" class="nav-link requires-auth" data-requires-auth="true"><?php echo htmlspecialchars(t('nav.cards'), ENT_QUOTES, 'UTF-8'); ?></a>
          <a href="dispense.php" class="nav-link requires-auth" data-requires-auth="true"><?php echo htmlspecialchars(t('nav.dispense'), ENT_QUOTES, 'UTF-8'); ?></a>
          <a href="logs.php" class="nav-link requires-auth" data-requires-auth="true"><?php echo htmlspecialchars(t('nav.logs'), ENT_QUOTES, 'UTF-8'); ?></a>
          <a href="diesel_price.php" class="nav-link requires-auth" data-requires-auth="true"><?php echo htmlspecialchars(t('nav.diesel'), ENT_QUOTES, 'UTF-8'); ?></a>
          <a href="passes.php" class="nav-link requires-auth" data-requires-auth="true"><?php echo htmlspecialchars(t('nav.passes'), ENT_QUOTES, 'UTF-8'); ?></a>
          <a href="search.php" class="nav-link requires-auth" data-requires-auth="true"><?php echo htmlspecialchars(t('nav.search'), ENT_QUOTES, 'UTF-8'); ?></a>
          <a href="car_book.php" class="nav-link requires-auth" data-requires-auth="true"><?php echo htmlspecialchars(t('nav.carbook'), ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 w-full sm:w-auto sm:ml-auto">
          <form method="post" action="set_language.php" class="flex items-center gap-2 bg-white/5 px-3 py-1 rounded-full border border-white/10 text-xs w-full sm:w-auto justify-between sm:justify-start">
            <label for="lang" class="text-white/70"> <?php echo htmlspecialchars(t('nav.language'), ENT_QUOTES, 'UTF-8'); ?> </label>
            <select name="lang" id="lang" class="bg-transparent text-white text-sm focus:outline-none" onchange="this.form.submit()">
              <?php foreach (availableLanguages() as $lang): ?>
                <option value="<?php echo htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $lang === $currentLang ? 'selected' : ''; ?>><?php echo htmlspecialchars(t('lang.' . $lang), ENT_QUOTES, 'UTF-8'); ?></option>
              <?php endforeach; ?>
            </select>
          </form>
          <div class="flex flex-col sm:flex-row sm:items-center gap-2" id="auth-cta">
            <div id="user-pill" class="hidden items-center gap-2 rounded-full border border-emerald-300/40 bg-emerald-400/10 px-3 py-1 text-xs font-semibold text-emerald-100 shadow-lg shadow-emerald-500/10 backdrop-blur">
              <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
              <span id="user-pill-name"><?php echo htmlspecialchars(currentUsername() ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <button id="open-login" class="nav-link bg-white/5 px-4 py-2 text-xs uppercase tracking-[0.2em] border border-white/10 shadow-lg shadow-cyan-500/10 <?php echo currentUserId() ? 'hidden' : ''; ?>"><?php echo htmlspecialchars(t('nav.login'), ENT_QUOTES, 'UTF-8'); ?></button>
            <button id="logout-btn" class="nav-link bg-gradient-to-r from-emerald-500/80 to-cyan-500/80 px-4 py-2 text-xs uppercase tracking-[0.2em] shadow-lg shadow-emerald-500/20 <?php echo currentUserId() ? '' : 'hidden'; ?>"><?php echo htmlspecialchars(t('nav.logout'), ENT_QUOTES, 'UTF-8'); ?></button>
          </div>
        </div>
      </div>
    </div>
  </nav>
  <script>
    const navContent = document.getElementById('nav-content');
    const toggleBtn = document.getElementById('menu-toggle');

    const closeMenu = () => {
      if (navContent && window.innerWidth < 768) {
        navContent.classList.add('hidden');
        toggleBtn?.setAttribute('aria-expanded', 'false');
      }
    };

    toggleBtn?.addEventListener('click', () => {
      if (!navContent) return;
      const isHidden = navContent.classList.contains('hidden');
      if (isHidden) {
        navContent.classList.remove('hidden');
      } else {
        navContent.classList.add('hidden');
      }
      toggleBtn.setAttribute('aria-expanded', String(!isHidden));
    });

    window.addEventListener('resize', () => {
      if (!navContent) return;
      if (window.innerWidth >= 768) {
        navContent.classList.remove('hidden');
        toggleBtn?.setAttribute('aria-expanded', 'true');
      } else {
        closeMenu();
      }
    });

    navContent?.querySelectorAll('a.nav-link').forEach((link) => {
      link.addEventListener('click', closeMenu);
    });
  </script>

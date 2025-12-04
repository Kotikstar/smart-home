<?php
require_once __DIR__ . '/i18n.php';

$lang = $_POST['lang'] ?? $_GET['lang'] ?? null;
if ($lang) {
    setLang($lang);
}

$referer = $_SERVER['HTTP_REFERER'] ?? 'all.php';
header('Location: ' . $referer);
exit;

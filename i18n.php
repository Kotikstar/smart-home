<?php
require_once __DIR__ . '/auth.php';
ensureSession();

$translations = [
    'ru' => [
        'app.title' => 'Смарт-дом: топливо и пропуска',
        'nav.home' => 'Главная',
        'nav.dashboard' => 'Панель',
        'nav.fuel' => 'Топливо',
        'nav.cards' => 'Карты',
        'nav.dispense' => 'Выдача',
        'nav.logs' => 'Логи',
        'nav.diesel' => 'Цены на дизель',
        'nav.passes' => 'Пропуска',
        'nav.search' => 'Поиск пропуска',
        'nav.carbook' => 'Car Book',
        'nav.login' => 'Вход',
        'nav.logout' => 'Выход',
        'nav.language' => 'Язык',
        'lang.ru' => 'Русский',
        'lang.en' => 'English',
        'carbook.title' => 'Гараж и обслуживание',
        'carbook.subtitle' => 'Единый учёт автомобилей, сервисов и пробега прямо в панели смарт-дома.',
        'carbook.stats.total' => 'Всего машин',
        'carbook.stats.ready' => 'Готовы к выезду',
        'carbook.stats.maintenance' => 'На обслуживании',
        'carbook.stats.reserved' => 'Забронировано',
        'carbook.section.vehicles' => 'Флот и статусы',
        'carbook.section.history' => 'Журнал операций',
        'carbook.section.add' => 'Добавить машину',
        'carbook.section.service' => 'Сервис / событие',
        'carbook.form.name' => 'Название / водитель',
        'carbook.form.brand' => 'Марка',
        'carbook.form.plate' => 'Госномер',
        'carbook.form.status' => 'Статус',
        'carbook.form.mileage' => 'Пробег, км',
        'carbook.form.next_service' => 'Следующее ТО',
        'carbook.form.notes' => 'Комментарий',
        'carbook.form.submit' => 'Сохранить',
        'carbook.form.event.type' => 'Тип события',
        'carbook.form.event.mileage' => 'Пробег на момент',
        'carbook.form.event.note' => 'Комментарий по событию',
        'carbook.status.ready' => 'Готов',
        'carbook.status.maintenance' => 'Обслуживание',
        'carbook.status.reserved' => 'Забронировано',
        'carbook.status.offline' => 'Не активен',
        'carbook.card.mileage' => 'Пробег',
        'carbook.card.next_service' => 'Следующее ТО',
        'carbook.card.last_event' => 'Последнее событие',
        'carbook.card.live_log' => 'Живой лог',
        'carbook.action.service' => 'В сервис',
        'carbook.action.ready' => 'Готов',
        'carbook.action.reserve' => 'Забронировать',
        'carbook.action.offline' => 'В архив',
        'carbook.message.saved' => 'Сохранено',
        'carbook.message.error' => 'Ошибка',
    ],
    'en' => [
        'app.title' => 'Smart Home: fuel & passes',
        'nav.home' => 'Home',
        'nav.dashboard' => 'Dashboard',
        'nav.fuel' => 'Fuel',
        'nav.cards' => 'Cards',
        'nav.dispense' => 'Dispense',
        'nav.logs' => 'Logs',
        'nav.diesel' => 'Diesel prices',
        'nav.passes' => 'Passes',
        'nav.search' => 'Pass search',
        'nav.carbook' => 'Car Book',
        'nav.login' => 'Login',
        'nav.logout' => 'Logout',
        'nav.language' => 'Language',
        'lang.ru' => 'Русский',
        'lang.en' => 'English',
        'carbook.title' => 'Garage & maintenance',
        'carbook.subtitle' => 'Unified fleet tracking, service and mileage directly in the smart home panel.',
        'carbook.stats.total' => 'Total cars',
        'carbook.stats.ready' => 'Ready to drive',
        'carbook.stats.maintenance' => 'In maintenance',
        'carbook.stats.reserved' => 'Reserved',
        'carbook.section.vehicles' => 'Fleet & statuses',
        'carbook.section.history' => 'Activity history',
        'carbook.section.add' => 'Add vehicle',
        'carbook.section.service' => 'Service / event',
        'carbook.form.name' => 'Name / driver',
        'carbook.form.brand' => 'Brand',
        'carbook.form.plate' => 'Plate',
        'carbook.form.status' => 'Status',
        'carbook.form.mileage' => 'Mileage, km',
        'carbook.form.next_service' => 'Next service',
        'carbook.form.notes' => 'Notes',
        'carbook.form.submit' => 'Save',
        'carbook.form.event.type' => 'Event type',
        'carbook.form.event.mileage' => 'Mileage at event',
        'carbook.form.event.note' => 'Event note',
        'carbook.status.ready' => 'Ready',
        'carbook.status.maintenance' => 'Maintenance',
        'carbook.status.reserved' => 'Reserved',
        'carbook.status.offline' => 'Archived',
        'carbook.card.mileage' => 'Mileage',
        'carbook.card.next_service' => 'Next service',
        'carbook.card.last_event' => 'Last event',
        'carbook.card.live_log' => 'Live log',
        'carbook.action.service' => 'Send to service',
        'carbook.action.ready' => 'Mark ready',
        'carbook.action.reserve' => 'Reserve',
        'carbook.action.offline' => 'Archive',
        'carbook.message.saved' => 'Saved',
        'carbook.message.error' => 'Error',
    ],
];

function availableLanguages(): array
{
    return ['ru', 'en'];
}

function normalizeLang(string $lang): string
{
    return in_array($lang, availableLanguages(), true) ? $lang : 'ru';
}

function setLang(string $lang): void
{
    $_SESSION['lang'] = normalizeLang($lang);
}

function currentLang(): string
{
    if (!isset($_SESSION['lang'])) {
        $_SESSION['lang'] = 'ru';
    }
    return normalizeLang($_SESSION['lang']);
}

function t(string $key, ?string $default = null, array $replacements = []): string
{
    global $translations;
    $lang = currentLang();
    $value = $translations[$lang][$key] ?? ($translations['ru'][$key] ?? $default ?? $key);
    foreach ($replacements as $placeholder => $replacement) {
        $value = str_replace('{' . $placeholder . '}', $replacement, $value);
    }
    return $value;
}
?>

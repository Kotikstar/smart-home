<?php
define('GOTIFY_URL', 'https://go.whysperingbytes.space/message');
define('GOTIFY_TOKEN', 'A962SC4VJRDKHBk');

function sendGotify($title, $message, $priority = 5) {
    $data = json_encode([
        'title' => $title,
        'message' => $message,
        'priority' => $priority
    ]);
    $url = GOTIFY_URL . '?token=' . GOTIFY_TOKEN;
    $options = ['http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $data
    ]];
    @file_get_contents($url, false, stream_context_create($options));
}
function getSetting($key) {
    global $conn;
    $res = $conn->query("SELECT value FROM settings WHERE `key` = '$key'")->fetch_assoc();
    return $res ? $res['value'] : null;
}

function setSetting($key, $value) {
    global $conn;
    $conn->query("INSERT INTO settings (`key`, `value`) VALUES ('$key', '$value') ON DUPLICATE KEY UPDATE value = '$value'");
}

?>

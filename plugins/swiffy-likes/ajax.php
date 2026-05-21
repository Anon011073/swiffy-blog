<?php
require_once __DIR__ . '/../../app/functions.php';

$slug = $_GET['slug'] ?? '';
$type = $_GET['type'] ?? '';

if (!$slug || !in_array($type, ['like', 'dislike'])) {
    echo json_encode(['success' => false]);
    exit;
}

$file = __DIR__ . '/../../config/likes.json';
$data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

if (!isset($data[$slug])) {
    $data[$slug] = ['likes' => 0, 'dislikes' => 0];
}

// Check session AND cookie for better protection
if (session_status() === PHP_SESSION_NONE) session_start();

$session_key = "voted_" . $slug;
$cookie_key = "anon_voted_" . substr(md5($slug), 0, 10);

if (isset($_SESSION[$session_key]) || isset($_COOKIE[$cookie_key])) {
    echo json_encode([
        'success' => true, 
        'likes' => $data[$slug]['likes'], 
        'dislikes' => $data[$slug]['dislikes'], 
        'message' => 'Already voted'
    ]);
    exit;
}

// Update counts
if ($type === 'like') {
    $data[$slug]['likes']++;
} else {
    $data[$slug]['dislikes']++;
}

// Set session and long-lived cookie (30 days)
$_SESSION[$session_key] = true;
setcookie($cookie_key, '1', time() + (86400 * 30), "/");

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'likes' => $data[$slug]['likes'],
    'dislikes' => $data[$slug]['dislikes']
]);

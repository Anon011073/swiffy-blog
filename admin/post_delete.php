<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/posts.php';
require_once __DIR__ . '/../app/functions.php';

require_login('posts');

$slug = $_GET['slug'] ?? '';
$token = $_GET['token'] ?? '';

if ($slug && verify_csrf_token($token)) {
    delete_post($slug);
}

redirect('index.php');

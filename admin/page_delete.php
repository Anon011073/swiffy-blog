<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/pages.php';
require_once __DIR__ . '/../app/functions.php';

require_login('pages');

$slug = $_GET['slug'] ?? '';
$token = $_GET['token'] ?? '';

if ($slug && verify_csrf_token($token)) {
    delete_page($slug);
}

redirect('pages.php');

<?php
/**
 * Simple Thumbnail Generator
 */

$src = $_GET['src'] ?? '';
$w = (int)($_GET['w'] ?? 300);
$h = (int)($_GET['h'] ?? 300);

if (!$src) exit;

$upload_dir = __DIR__ . '/../../uploads/';
$cache_dir = __DIR__ . '/cache/';
$source_file = $upload_dir . basename($src);

if (!file_exists($source_file)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

$cache_file = $cache_dir . md5($src . $w . $h) . '.jpg';

if (file_exists($cache_file)) {
    header('Content-Type: image/jpeg');
    echo file_get_contents($cache_file);
    exit;
}

// Basic Resizing using GD
$info = getimagesize($source_file);
$mime = $info['mime'];

switch ($mime) {
    case 'image/jpeg': $img = imagecreatefromjpeg($source_file); break;
    case 'image/png':  $img = imagecreatefrompng($source_file); break;
    case 'image/gif':  $img = imagecreatefromgif($source_file); break;
    default: exit;
}

$orig_w = imagesx($img);
$orig_h = imagesy($img);

$new_img = imagecreatetruecolor($w, $h);
imagecopyresampled($new_img, $img, 0, 0, 0, 0, $w, $h, $orig_w, $orig_h);

header('Content-Type: image/jpeg');
imagejpeg($new_img, $cache_file, 80);
imagejpeg($new_img, null, 80);

imagedestroy($img);
imagedestroy($new_img);

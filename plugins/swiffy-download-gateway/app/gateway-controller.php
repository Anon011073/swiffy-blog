<?php
/**
 * Controller for the intermediate gateway page
 */
require_once __DIR__ . '/security.php';

function sfx_render_gateway($filename) {
    $filepath = SFX_DL_STORAGE_DIR . basename($filename);

    if (!file_exists($filepath)) {
        die("File not found.");
    }

    $file_info = [
        'name' => basename($filename),
        'size' => sfx_format_size(filesize($filepath)),
        'ext' => pathinfo($filename, PATHINFO_EXTENSION),
        'token' => sfx_generate_token($filename)
    ];

    $config = load_config();
    $timer = $config['sfx_dl_timer'] ?? 5;

    include SFX_DL_GATEWAY_DIR . '/views/gateway.php';
}

function sfx_format_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) $bytes /= 1024;
    return round($bytes, 2) . ' ' . $units[$i];
}

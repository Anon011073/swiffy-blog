<?php
/**
 * Secure file download handler (Harden version)
 */
require_once __DIR__ . '/security.php';

function sfx_handle_secure_download($token) {
    if (sfx_is_token_used($token)) {
        die("This download link has already been used.");
    }

    $filename = sfx_validate_token($token);

    if (!$filename) {
        die("Invalid or expired download link.");
    }

    // Path traversal protection: Ensure we only use the basename
    $safe_name = basename($filename);
    $filepath = realpath(SFX_DL_STORAGE_DIR . $safe_name);
    $storage_root = realpath(SFX_DL_STORAGE_DIR);

    // Ensure the file is actually within the storage directory
    if (!$filepath || strpos($filepath, $storage_root) !== 0 || !file_exists($filepath)) {
        die("Security violation: File access denied.");
    }

    sfx_mark_token_used($token);
    sfx_log_download($safe_name);
    sfx_stream_file($filepath, $safe_name);
}

function sfx_get_log_path($file) {
    $dir = dirname(__DIR__) . '/logs';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    return $dir . '/' . basename($file);
}

function sfx_is_token_used($token) {
    $used_file = sfx_get_log_path('used_tokens.json');
    if (!file_exists($used_file)) return false;
    $content = json_decode(file_get_contents($used_file), true);
    return is_array($content) && in_array($token, $content);
}

function sfx_mark_token_used($token) {
    $used_file = sfx_get_log_path('used_tokens.json');
    $content = file_exists($used_file) ? json_decode(file_get_contents($used_file), true) : [];
    $used = is_array($content) ? $content : [];
    $used[] = $token;
    if (count($used) > 1000) array_shift($used);
    file_put_contents($used_file, json_encode($used));
}

function sfx_stream_file($path, $name) {
    $mime = mime_content_type($path);
    $size = filesize($path);

    // Prevent sensitive file disclosure
    $disallowed_ext = ['php', 'phtml', 'htaccess', 'json', 'log'];
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    if (in_array(strtolower($ext), $disallowed_ext)) {
        die("Access denied for this file type.");
    }

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . $name . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $size);

    if (ob_get_level()) ob_end_clean();
    flush();
    readfile($path);
}

function sfx_log_download($filename) {
    $log_file = sfx_get_log_path('downloads.json');
    $content = file_exists($log_file) ? json_decode(file_get_contents($log_file), true) : [];
    $logs = is_array($content) ? $content : [];

    $entry = [
        'file' => $filename,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'time' => time(),
        'ref' => $_SERVER['HTTP_REFERER'] ?? 'direct'
    ];

    $logs[] = $entry;
    if (count($logs) > 5000) array_shift($logs);
    file_put_contents($log_file, json_encode($logs));
}

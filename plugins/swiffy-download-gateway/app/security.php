<?php
/**
 * Security utilities for Swiffy Download Gateway
 */

function sfx_generate_token($filename, $expiry = 3600) {
    $config = load_config();
    $salt = $config['admin_pass'] ?? 'sfx-default-salt';
    $time = time();
    $ip = $_SERVER['REMOTE_ADDR'];

    $token_data = [
        'file' => $filename,
        'exp' => $time + $expiry,
        'ip' => $ip
    ];

    $payload = base64_encode(json_encode($token_data));
    $sig = hash_hmac('sha256', $payload, $salt);

    return $payload . '.' . $sig;
}

function sfx_validate_token($token) {
    if (empty($token) || strpos($token, '.') === false) return false;

    list($payload, $sig) = explode('.', $token);
    $config = load_config();
    $salt = $config['admin_pass'] ?? 'sfx-default-salt';

    $expected_sig = hash_hmac('sha256', $payload, $salt);
    if (!hash_equals($expected_sig, $sig)) return false;

    $data = json_decode(base64_decode($payload), true);
    if (!$data) return false;

    if (time() > $data['exp']) return false;

    return $data['file'];
}

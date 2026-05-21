<?php
/**
 * Unlock logic for Download Gateway
 */

function sfx_check_unlock_status($filename, $mode, $post_slug = '') {
    if ($mode === 'none') return true;

    // In a real scenario, we'd check session or cookie
    if (isset($_SESSION['sfx_unlocked_' . $filename])) return true;

    return false;
}

function sfx_handle_unlock_request($filename, $mode, $post_slug = '') {
    // This would be called via AJAX or form post
    $_SESSION['sfx_unlocked_' . $filename] = true;
    return true;
}

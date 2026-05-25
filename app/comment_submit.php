<?php
/**
 * Comment submission handler
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/comments.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_slug = $_POST['post_slug'] ?? '';
    $content = sanitize($_POST['content'] ?? '');
    $config = load_config();

    if ($post_slug && $content) {
        $approved = false;
        $nickname = sanitize($_POST['nickname'] ?? '');
        $email = sanitize($_POST['email'] ?? '');

        if (is_logged_in()) {
            $approved = true;
            $nickname = !empty($config['admin_nickname']) ? $config['admin_nickname'] : ($config['admin_user'] ?? 'Admin');
            $email = $config['admin_email'] ?? '';
        } elseif (isset($_SESSION['swiffy_user'])) {
            $user = $_SESSION['swiffy_user'];
            $nickname = $user['nickname'] ?? $nickname;
            $email = $user['email'] ?? $email;
            if ($user['auto_approve_comments'] ?? false) {
                $approved = true;
            }
        }

        if ($nickname) {
            $comment_data = [
                'nickname' => $nickname,
                'email' => $email,
                'content' => $content,
                'approved' => $approved
            ];

            if (save_comment($post_slug, $comment_data)) {
                $redirect_url = $_SERVER['HTTP_REFERER'] ?? '../index.php?post=' . $post_slug;

                // Remove existing anchors and success flags
                $redirect_url = strtok($redirect_url, '#');
                $redirect_url = preg_replace('/[?&]comment_success=1/', '', $redirect_url);

                $sep = (strpos($redirect_url, '?') !== false) ? '&' : '?';
                $redirect_url .= $sep . 'comment_success=1#comments';

                redirect($redirect_url);
            }
        }
    }
}

redirect($_SERVER['HTTP_REFERER'] ?? '../index.php');

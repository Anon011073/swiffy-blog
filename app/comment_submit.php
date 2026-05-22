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

        if (is_logged_in()) {
            $approved = true;
            $nickname = !empty($config['admin_nickname']) ? $config['admin_nickname'] : ($config['admin_user'] ?? 'Admin');
        } elseif (isset($_SESSION['swiffy_user'])) {
            $user = $_SESSION['swiffy_user'];
            $nickname = $user['nickname'] ?? $nickname;
            if ($user['auto_approve_comments'] ?? false) {
                $approved = true;
            }
        }

        if ($nickname) {
            $comment_data = [
                'nickname' => $nickname,
                'content' => $content,
                'approved' => $approved
            ];

            if (save_comment($post_slug, $comment_data)) {
                redirect('../index.php?post=' . $post_slug . '&comment_success=1');
            }
        }
    }
}

redirect('../index.php');

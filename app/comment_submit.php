<?php
/**
 * Comment submission handler
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/comments.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_slug = $_POST['post_slug'] ?? '';
    $nickname = sanitize($_POST['nickname'] ?? '');
    $content = sanitize($_POST['content'] ?? '');

    if ($post_slug && $nickname && $content) {
        $approved = is_logged_in(); // Auto-approve if admin is logged in

        // Check for AnonUsers auto-approval
        if (!$approved && isset($_SESSION['anon_user'])) {
            $user = $_SESSION['anon_user'];
            if ($user['auto_approve_comments'] ?? false) {
                $approved = true;
            }
        }

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

redirect('../index.php');

<?php
/**
 * Comment management logic
 */

define('COMMENTS_DIR', __DIR__ . '/../content/comments/');

/**
 * Get comments for a post
 */
function get_comments($post_slug) {
    $file = COMMENTS_DIR . $post_slug . '.json';
    if (file_exists($file)) {
        $comments = json_decode(file_get_contents($file), true);
        if ($comments) {
            // Sort by date descending
            uasort($comments, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            return $comments;
        }
    }
    return [];
}

/**
 * Save a comment
 */
function save_comment($post_slug, $comment_data) {
    if (!is_dir(COMMENTS_DIR)) {
        mkdir(COMMENTS_DIR, 0755, true);
    }

    $file = COMMENTS_DIR . $post_slug . '.json';
    $comments = [];
    if (file_exists($file)) {
        $comments = json_decode(file_get_contents($file), true);
    }

    $comment_id = uniqid();
    $comment_data['date'] = date('Y-m-d H:i:s');
    $comments[$comment_id] = $comment_data;

    return file_put_contents($file, json_encode($comments, JSON_PRETTY_PRINT));
}

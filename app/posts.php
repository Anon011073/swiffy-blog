<?php
/**
 * Post management logic
 */

define('POSTS_DIR', __DIR__ . '/../content/posts/');

/**
 * Get all posts
 */
function get_posts($include_pending = false) {
    $posts = [];
    $files = glob(POSTS_DIR . '*.json');

    foreach ($files as $file) {
        $post = json_decode(file_get_contents($file), true);
        if ($post) {
            if (!$include_pending && isset($post['status']) && $post['status'] === 'pending') {
                continue;
            }
            $posts[] = $post;
        }
    }

    // Sort posts by date descending
    usort($posts, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    return $posts;
}

/**
 * Get a single post by slug
 */
function get_post($slug) {
    $file = POSTS_DIR . $slug . '.json';
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true);
    }
    return null;
}

/**
 * Save a post
 */
function save_post($data) {
    if (empty($data['slug'])) {
        $data['slug'] = generate_slug($data['title']);
    }

    $file = POSTS_DIR . $data['slug'] . '.json';

    // Auto-generate excerpt if empty
    if (empty($data['excerpt'])) {
        $data['excerpt'] = substr(strip_tags($data['content']), 0, 150) . '...';
    }

    $saved = file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));

    if ($saved) {
        $plugins_data = get_enabled_plugins_data();
        foreach ($plugins_data as $p_data) {
            if (isset($p_data['hooks']['post_saved'])) $p_data['hooks']['post_saved']($data['slug']);
        }
    }

    return $saved;
}

/**
 * Delete a post
 */
function delete_post($slug) {
    $file = POSTS_DIR . $slug . '.json';
    if (file_exists($file)) {
        $deleted = unlink($file);
        if ($deleted) {
            $plugins_data = get_enabled_plugins_data();
            foreach ($plugins_data as $p_data) {
                if (isset($p_data['hooks']['post_deleted'])) $p_data['hooks']['post_deleted']($slug);
            }
        }
        return $deleted;
    }
    return false;
}

/**
 * Get recent posts
 */
function get_recent_posts($limit = 5) {
    $posts = get_posts();
    return array_slice($posts, 0, $limit);
}

/**
 * Get paginated posts
 */
function get_posts_paginated($page = 1, $limit = 5) {
    $posts = get_posts();
    $offset = ($page - 1) * $limit;
    return array_slice($posts, $offset, $limit);
}

/**
 * Count total posts
 */
function count_posts() {
    $files = glob(POSTS_DIR . '*.json');
    return count($files);
}

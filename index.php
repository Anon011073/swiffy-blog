<?php
/**
 * Main Entry Point
 */

require_once __DIR__ . '/app/functions.php';
require_once __DIR__ . '/app/posts.php';
require_once __DIR__ . '/app/theme.php';

// Plugin initialization
$config = load_config();
get_enabled_plugins_data(); // This will load all plugin files and cache them
// HOOK: system_init
foreach (get_enabled_plugins_data() as $p_data) {
    if (isset($p_data['hooks']['system_init'])) $p_data['hooks']['system_init']();
}

// If not installed, redirect to installer
if (empty($config)) {
    if (file_exists('install.php')) {
        header('Location: install.php');
        exit;
    } else {
        die('CMS is not installed and install.php is missing.');
    }
}

$post_slug = $_GET['post'] ?? '';
$page_slug = $_GET['page'] ?? '';

// Handle special plugin pages
if ($page_slug === 'register') {
    render_theme('page', ['page' => ['title' => 'Register Account', 'content' => '[register]'], 'config' => $config]);
    exit;
}
$search_query = $_GET['s'] ?? '';
$p = (int)($_GET['p'] ?? 1);

if ($post_slug) {
    // Single post page
    $post = get_post(basename($post_slug));
    if ($post) {
        render_theme('post', ['post' => $post, 'config' => $config]);
    } else {
        header("HTTP/1.0 404 Not Found");
        die('Post not found');
    }
} elseif ($page_slug) {
    // Static page
    require_once __DIR__ . '/app/pages.php';
    $page = get_page(basename($page_slug));
    if ($page) {
        render_theme('page', ['page' => $page, 'config' => $config]);
    } else {
        header("HTTP/1.0 404 Not Found");
        die('Page not found');
    }
} elseif ($search_query) {
    // Search results
    $all_posts = get_posts();
    $results = array_filter($all_posts, function($p) use ($search_query) {
        return stripos($p['title'], $search_query) !== false || stripos($p['content'], $search_query) !== false;
    });
    render_theme('index', ['posts' => $results, 'config' => $config, 'is_search' => true, 'search_query' => $search_query]);
} else {
    // Homepage with pagination
    $limit = $config['posts_per_page'] ?? 5;
    $posts = get_posts_paginated($p, $limit);
    $total_posts = count_posts();
    $total_pages = ceil($total_posts / $limit);

    render_theme('index', [
        'posts' => $posts,
        'config' => $config,
        'current_page' => $p,
        'total_pages' => $total_pages
    ]);
}

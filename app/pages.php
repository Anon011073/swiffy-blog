<?php
/**
 * Static page management logic
 */

define('PAGES_DIR', __DIR__ . '/../content/pages/');

/**
 * Get all pages
 */
function get_pages() {
    $pages = [];
    if (!is_dir(PAGES_DIR)) mkdir(PAGES_DIR, 0755, true);
    $files = glob(PAGES_DIR . '*.json');

    foreach ($files as $file) {
        $page = json_decode(file_get_contents($file), true);
        if ($page) {
            $pages[] = $page;
        }
    }
    return $pages;
}

/**
 * Get a single page by slug
 */
function get_page($slug) {
    $file = PAGES_DIR . basename($slug) . '.json';
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true);
    }
    return null;
}

/**
 * Save a page
 */
function save_page($data) {
    if (!is_dir(PAGES_DIR)) mkdir(PAGES_DIR, 0755, true);
    if (empty($data['slug'])) {
        $data['slug'] = generate_slug($data['title']);
    }
    $file = PAGES_DIR . $data['slug'] . '.json';
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

/**
 * Delete a page
 */
function delete_page($slug) {
    $file = PAGES_DIR . basename($slug) . '.json';
    if (file_exists($file)) {
        return unlink($file);
    }
    return false;
}

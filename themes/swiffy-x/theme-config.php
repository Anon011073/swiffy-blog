<?php
/**
 * Swiffy-X Theme Config
 * Grouped into logical sections for tabbed UI.
 */
return [
    'name' => 'Swiffy-X',
    'description' => 'A high-performance reading theme with a two-mode layout system.',
    'author' => 'Swiffy Team',
    'version' => '2.0.0',
    'sections' => [
        'style' => 'Colors & Style',
        'typo' => 'Typography',
        'layout' => 'Layout & Feed',
        'fx' => 'Special Effects'
    ],
    'options' => [
        // Style
        ['name' => 'primary_color', 'label' => 'Primary Accent (Purple)', 'type' => 'color', 'default' => '#8b5cf6', 'section' => 'style'],
        ['name' => 'secondary_color', 'label' => 'Secondary Accent (Green)', 'type' => 'color', 'default' => '#22c55e', 'section' => 'style'],

        // Typography
        ['name' => 'title_font', 'label' => 'Article Title Font', 'type' => 'font', 'default' => 'Inter', 'section' => 'typo'],
        ['name' => 'body_font', 'label' => 'Body Content Font', 'type' => 'font', 'default' => 'Inter', 'section' => 'typo'],
        ['name' => 'index_font', 'label' => 'Index Card Font', 'type' => 'font', 'default' => 'Inconsolata', 'section' => 'typo'],
        ['name' => 'excerpt_font', 'label' => 'Excerpt Font', 'type' => 'font', 'default' => 'Inconsolata', 'section' => 'typo'],
        ['name' => 'meta_font', 'label' => 'Small UI Meta Font', 'type' => 'font', 'default' => 'Inter', 'section' => 'typo'],
        ['name' => 'site_title_separator', 'label' => 'Site Title Separator', 'type' => 'text', 'default' => '/', 'section' => 'typo'],
        ['name' => 'site_title_suffix', 'label' => 'Site Title Suffix (e.g. Blog)', 'type' => 'text', 'default' => 'Blog', 'section' => 'typo'],
        ['name' => 'site_title_size', 'label' => 'Site Title Size (px)', 'type' => 'number', 'default' => '30', 'section' => 'typo'],
        ['name' => 'post_title_size', 'label' => 'Post Title Size (rem, e.g. 3.5)', 'type' => 'number', 'default' => '4.0', 'section' => 'typo'],
        ['name' => 'body_text_size', 'label' => 'Body Text Size (rem, e.g. 1.25)', 'type' => 'number', 'default' => '1.25', 'section' => 'typo'],

        // Layout
        ['name' => 'reading_max_width', 'label' => 'Content Container Width (px)', 'type' => 'number', 'default' => 720, 'section' => 'layout'],
        ['name' => 'reading_line_height', 'label' => 'Reading Line Height', 'type' => 'text', 'default' => '1.85', 'section' => 'layout'],
        ['name' => 'feed_card_spacing', 'label' => 'Card Spacing (px)', 'type' => 'number', 'default' => 32, 'section' => 'layout'],
        ['name' => 'show_taxonomies', 'label' => 'Show Categories/Tags', 'type' => 'checkbox', 'default' => true, 'section' => 'layout'],
        ['name' => 'show_search_nav', 'label' => 'Show Navigation Search', 'type' => 'checkbox', 'default' => true, 'section' => 'layout'],

        // Effects
        ['name' => 'enable_comet_cursor', 'label' => 'Enable Swiffy Comet Effect', 'type' => 'checkbox', 'default' => true, 'section' => 'fx'],
        ['name' => 'custom_css', 'label' => 'Global Custom CSS', 'type' => 'textarea', 'default' => '', 'section' => 'fx']
    ]
];

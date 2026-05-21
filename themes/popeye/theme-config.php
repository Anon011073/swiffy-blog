<?php
/**
 * Popeye Theme Config
 */
return [
    'name' => 'Popeye',
    'description' => 'A clean, single-column focused theme for minimalists.',
    'author' => 'Swiffy Blog Team',
    'version' => '1.2.0',
    'options' => [
        [
            'name' => 'site_width',
            'label' => 'Site Content Width (px)',
            'type' => 'number',
            'default' => 650
        ],
        [
            'name' => 'header_sticky',
            'label' => 'Sticky Header',
            'type' => 'checkbox',
            'default' => true
        ],
        [
            'name' => 'header_blur',
            'label' => 'Enable Glassmorphism Header',
            'type' => 'checkbox',
            'default' => true
        ],
        [
            'name' => 'titles_only',
            'label' => 'Display Post Titles Only (Index)',
            'type' => 'checkbox',
            'default' => false
        ],
        [
            'name' => 'show_tax_meta',
            'label' => 'Show Categories & Tags in Meta',
            'type' => 'checkbox',
            'default' => true
        ],
        [
            'name' => 'show_author_bio',
            'label' => 'Show Author Bio under Post',
            'type' => 'checkbox',
            'default' => true
        ],
        [
            'name' => 'line_height',
            'label' => 'Body Line Height',
            'type' => 'number',
            'default' => 1.6
        ],
        [
            'name' => 'featured_img_style',
            'label' => 'Featured Image Style',
            'type' => 'select',
            'options' => [
                'full' => 'Above Title (Full)',
                'thumb' => 'Side-by-side (Thumbnail)'
            ],
            'default' => 'full'
        ],
        [
            'name' => 'body_font',
            'label' => 'Body Font',
            'type' => 'font',
            'default' => 'Inter'
        ],
        [
            'name' => 'title_font',
            'label' => 'Title Font',
            'type' => 'font',
            'default' => 'Playfair Display'
        ],
        [
            'name' => 'primary_color',
            'label' => 'Primary Brand Color',
            'type' => 'color',
            'default' => '#000000'
        ],
        [
            'name' => 'custom_css',
            'label' => 'Custom CSS',
            'type' => 'textarea',
            'default' => ''
        ]
    ]
];

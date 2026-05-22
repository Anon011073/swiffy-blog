<?php
/**
 * Default Theme Config
 */
return [
    'name' => 'Default',
    'description' => 'The standard Swiffy Blog theme, clean and powerful.',
    'author' => 'Swiffy Blog Team',
    'version' => '1.4.0',
    'sections' => [
        'general' => 'General Settings',
        'layout' => 'Layout & Structure',
        'typography' => 'Typography',
        'colors' => 'Colors & Styles',
        'advanced' => 'Advanced'
    ],
    'options' => [
        [
            'name' => 'front_page_template',
            'label' => 'Front Page Layout',
            'type' => 'select',
            'section' => 'layout',
            'options' => [
                'default' => 'Standard (1 Col + Sidebar)',
                'grid_sidebar' => 'Grid (2 Col + Sidebar)',
                'single_column' => 'Narrow (Single Column)'
            ],
            'default' => 'default'
        ],
        [
            'name' => 'featured_image_position',
            'label' => 'Index Featured Image Position',
            'type' => 'select',
            'section' => 'layout',
            'options' => [
                'left' => 'Left of Content (Thumbnail)',
                'top' => 'Above Title (Full Width)'
            ],
            'default' => 'left'
        ],
        [
            'name' => 'single_post_sidebar',
            'label' => 'Show Sidebar on Single Post/Page',
            'type' => 'select',
            'section' => 'layout',
            'options' => [
                'yes' => 'Yes',
                'no' => 'No'
            ],
            'default' => 'yes'
        ],
        [
            'name' => 'header_sticky',
            'label' => 'Sticky Header',
            'type' => 'checkbox',
            'section' => 'layout',
            'default' => false
        ],
        [
            'name' => 'header_blur',
            'label' => 'Glassmorphism Header (Blur)',
            'type' => 'checkbox',
            'section' => 'layout',
            'default' => false
        ],
        [
            'name' => 'container_width',
            'label' => 'Site Max-Width (px)',
            'type' => 'number',
            'section' => 'layout',
            'default' => 1100
        ],
        [
            'name' => 'sidebar_width',
            'label' => 'Sidebar Width (px)',
            'type' => 'number',
            'section' => 'layout',
            'default' => 300
        ],
        [
            'name' => 'title_font',
            'label' => 'Heading Font',
            'type' => 'font',
            'section' => 'typography',
            'default' => 'Poppins'
        ],
        [
            'name' => 'body_font',
            'label' => 'Body Font',
            'type' => 'font',
            'section' => 'typography',
            'default' => 'Inter'
        ],
        [
            'name' => 'title_font_size',
            'label' => 'Post Title Font Size (px)',
            'type' => 'number',
            'section' => 'typography',
            'default' => 32
        ],
        [
            'name' => 'body_font_size',
            'label' => 'Body Font Size (px)',
            'type' => 'number',
            'section' => 'typography',
            'default' => 16
        ],
        [
            'name' => 'site_title_font_size',
            'label' => 'Site Title Font Size (px)',
            'type' => 'number',
            'section' => 'typography',
            'default' => 24
        ],
        [
            'name' => 'site_title_border',
            'label' => 'Site Title Border',
            'type' => 'checkbox',
            'section' => 'typography',
            'default' => false
        ],
        [
            'name' => 'site_title_border_radius',
            'label' => 'Site Title Border Radius (px)',
            'type' => 'number',
            'section' => 'typography',
            'default' => 8
        ],
        [
            'name' => 'site_title_padding',
            'label' => 'Site Title Padding (px)',
            'type' => 'number',
            'section' => 'typography',
            'default' => 10
        ],
        [
            'name' => 'site_title_underline',
            'label' => 'Site Title Underline Effect',
            'type' => 'checkbox',
            'section' => 'typography',
            'default' => false
        ],
        [
            'name' => 'primary_color',
            'label' => 'Primary Accent Color',
            'type' => 'color',
            'section' => 'colors',
            'default' => '#007bff'
        ],
        [
            'name' => 'custom_css',
            'label' => 'Additional CSS',
            'type' => 'textarea',
            'section' => 'advanced',
            'default' => ''
        ]
    ]
];

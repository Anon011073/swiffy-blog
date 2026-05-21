<?php
/**
 * Default Theme Config
 */
return [
    'name' => 'Default',
    'description' => 'The standard AnonBlog theme, clean and powerful.',
    'author' => 'AnonBlog Team',
    'version' => '1.3.0',
    'options' => [
        [
            'name' => 'front_page_template',
            'label' => 'Front Page Layout',
            'type' => 'select',
            'options' => [
                'default' => 'Default (1 Column + Sidebar)',
                'grid' => 'Grid (3 Columns, No Sidebar)',
                'grid_sidebar' => 'Grid with Sidebar (2 Columns + Sidebar)',
                'single_column' => 'Single Column (No Sidebar)'
            ],
            'default' => 'default'
        ],
        [
            'name' => 'single_post_sidebar',
            'label' => 'Show Sidebar on Single Post Page',
            'type' => 'select',
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
            'default' => false
        ],
        [
            'name' => 'header_blur',
            'label' => 'Glassmorphism Header (Blur)',
            'type' => 'checkbox',
            'default' => false
        ],
        [
            'name' => 'featured_image_position',
            'label' => 'Featured Image Position',
            'type' => 'select',
            'options' => [
                'top' => 'Above Title (Full Width)',
                'left' => 'Left of Content (Thumbnail)'
            ],
            'default' => 'top'
        ],
        [
            'name' => 'site_title_font_size',
            'label' => 'Site Title Font Size (px)',
            'type' => 'number',
            'default' => 24
        ],
        [
            'name' => 'site_title_border',
            'label' => 'Site Title Border',
            'type' => 'checkbox',
            'default' => false
        ],
        [
            'name' => 'site_title_border_radius',
            'label' => 'Site Title Border Radius (px)',
            'type' => 'number',
            'default' => 8
        ],
        [
            'name' => 'site_title_padding',
            'label' => 'Site Title Padding (px)',
            'type' => 'number',
            'default' => 10
        ],
        [
            'name' => 'site_title_underline',
            'label' => 'Site Title Underline Effect',
            'type' => 'checkbox',
            'default' => false
        ],
        [
            'name' => 'title_font',
            'label' => 'Heading Font',
            'type' => 'font',
            'default' => 'Poppins'
        ],
        [
            'name' => 'body_font',
            'label' => 'Body Font',
            'type' => 'font',
            'default' => 'Inter'
        ],
        [
            'name' => 'title_font_size',
            'label' => 'Post Title Font Size (px)',
            'type' => 'number',
            'default' => 32
        ],
        [
            'name' => 'body_font_size',
            'label' => 'Body Font Size (px)',
            'type' => 'number',
            'default' => 16
        ],
        [
            'name' => 'primary_color',
            'label' => 'Primary Accent Color',
            'type' => 'color',
            'default' => '#007bff'
        ],
        [
            'name' => 'container_width',
            'label' => 'Site Max-Width (px)',
            'type' => 'number',
            'default' => 1100
        ],
        [
            'name' => 'sidebar_width',
            'label' => 'Sidebar Width (px)',
            'type' => 'number',
            'default' => 300
        ],
        [
            'name' => 'custom_css',
            'label' => 'Additional CSS',
            'type' => 'textarea',
            'default' => ''
        ]
    ]
];

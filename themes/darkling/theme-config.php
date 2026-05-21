<?php
/**
 * Darkling Theme Config
 */
return [
    'name' => 'Darkling',
    'description' => 'A dark-first, widget-centric theme with a clean aesthetic.',
    'author' => 'AnonBlog Team',
    'version' => '1.0.0',
    'options' => [
        [
            'name' => 'site_width',
            'label' => 'Site Content Width (px)',
            'type' => 'number',
            'default' => 800
        ],
        [
            'name' => 'primary_color',
            'label' => 'Accent Color',
            'type' => 'color',
            'default' => '#3498db'
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
            'default' => 'Poppins'
        ]
    ]
];

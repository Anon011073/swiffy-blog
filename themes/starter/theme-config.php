<?php
/**
 * Starter Theme Configuration & Options
 * This file allows you to define custom settings that users can change in the Admin Panel.
 */

return [
    'name' => 'Starter Theme',
    'author' => 'AnonBlog Team',
    'version' => '1.0.0',
    'description' => 'A clean starting point for theme developers with built-in customization support.',
    'options' => [
        // FONT OPTION: Renders a dropdown of 20+ Google Fonts
        [
            'name' => 'heading_font',
            'label' => 'Heading Font',
            'type' => 'font',
            'default' => 'Montserrat'
        ],
        // COLOR OPTION: Renders a color picker
        [
            'name' => 'accent_color',
            'label' => 'Primary Theme Color',
            'type' => 'color',
            'default' => '#e91e63'
        ],
        // CHECKBOX OPTION: Renders a simple toggle
        [
            'name' => 'show_sidebar',
            'label' => 'Display Sidebar',
            'type' => 'checkbox',
            'default' => true
        ],
        // TEXT OPTION: Renders a standard text input
        [
            'name' => 'footer_text',
            'label' => 'Custom Footer Copyright',
            'type' => 'text',
            'default' => 'Powered by AnonBlog'
        ],
        /*
        // SELECT OPTION EXAMPLE:
        [
            'name' => 'layout_style',
            'label' => 'Container Style',
            'type' => 'select',
            'options' => ['boxed' => 'Boxed Layout', 'full' => 'Full Width'],
            'default' => 'full'
        ],
        */
    ]
];

<?php
/**
 * Plugin Name: Swiffy Download Gateway
 * Description: Secure download portal with temporary links, countdowns, and file protection.
 * Version: 1.1.0
 * Author: Swiffy Team
 */

if (!defined('SFX_DL_GATEWAY_DIR')) {
    define('SFX_DL_GATEWAY_DIR', __DIR__);
    define('SFX_DL_STORAGE_DIR', __DIR__ . '/../../content/protected-uploads/');
}

if (!function_exists('sfx_terminate_request')) {
    function sfx_terminate_request() {
        die();
    }
}

return [
    'name' => 'Swiffy Download Gateway',
    'version' => '1.1.0',
    'author' => 'Swiffy Team',
    'settings_url' => 'settings.php?plugin=swiffy-download-gateway',
    'hooks' => [
        'system_init' => function() {
            // Handle protected download requests
            if (isset($_GET['sfx-dl'])) {
                require_once SFX_DL_GATEWAY_DIR . '/app/handler.php';
                sfx_handle_secure_download($_GET['sfx-dl']);
                sfx_terminate_request();
            }

            // Handle Gateway Page routing
            if (isset($_GET['download'])) {
                require_once SFX_DL_GATEWAY_DIR . '/app/gateway-controller.php';
                sfx_render_gateway($_GET['download']);
                sfx_terminate_request();
            }
        },
        'render_content' => function($content) {
            // [sfx-download file="filename.zip" label="Download File"]
            // Updated regex to support single or double quotes
            return preg_replace_callback('/\[sfx-download\s+file=(?:"|\')([^"\']+)(?:"|\')(?:\s+label=(?:"|\')([^"\']+)(?:"|\'))?\]/', function($matches) {
                $filename = $matches[1];
                $label = $matches[2] ?? 'Download';
                $url = 'index.php?download=' . urlencode($filename);

                return '<a href="' . $url . '" class="sfx-dl-btn">
                            <span class="btn-icon">⬇</span>
                            <span class="btn-text">' . htmlspecialchars($label) . '</span>
                            <span class="btn-glow"></span>
                        </a>';
            }, $content);
        },
        'system_header' => function() {
            return '<style>
                .sfx-dl-btn {
                    display: inline-flex;
                    align-items: center;
                    gap: 12px;
                    background: rgba(139, 92, 246, 0.1);
                    border: 1px solid rgba(139, 92, 246, 0.3);
                    color: #fff;
                    padding: 14px 28px;
                    border-radius: 12px;
                    text-decoration: none;
                    font-weight: 700;
                    position: relative;
                    overflow: hidden;
                    transition: all 0.3s ease;
                    font-family: inherit;
                    margin: 10px 0;
                }
                .sfx-dl-btn:hover {
                    background: rgba(139, 92, 246, 0.2);
                    border-color: #8b5cf6;
                    transform: translateY(-2px);
                    box-shadow: 0 0 200px rgba(139, 92, 246, 0.4);
                }
                .sfx-dl-btn .btn-glow {
                    position: absolute;
                    top: 0;
                    left: -100%;
                    width: 50%;
                    height: 100%;
                    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
                    transition: 0.5s;
                }
                .sfx-dl-btn:hover .btn-glow {
                    left: 150%;
                }
            </style>';
        }
    ]
];

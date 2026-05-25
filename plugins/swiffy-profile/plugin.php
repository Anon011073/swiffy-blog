<?php
/**
 * Plugin Name: Swiffy Profile Shortcode
 * Description: Adds a [profile] shortcode to display the admin profile on any page.
 */

return [
    'hooks' => [
        'render_content' => function($content) {
            if (strpos($content, '[profile]') !== false) {
                $config = load_config();
                $nickname = !empty($config['admin_nickname']) ? $config['admin_nickname'] : ($config['admin_user'] ?? 'Admin');
                $bio = !empty($config['admin_about_me']) ? nl2br(htmlspecialchars($config['admin_about_me'])) : 'No bio available.';

                $avatar_url = '';
                if ($config['use_gravatar'] ?? false) {
                    $email_hash = md5(strtolower(trim($config['admin_email'] ?? '')));
                    $avatar_url = "https://www.gravatar.com/avatar/$email_hash?s=240&d=mp";
                } elseif (!empty($config['admin_avatar'])) {
                    $avatar_url = "uploads/" . $config['admin_avatar'];
                }

                $html = '<div class="profile-card" style="padding: 2rem; background: rgba(255,255,255,0.05); border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); margin: 2rem 0; text-align: center;">';
                if ($avatar_url) {
                    $html .= '<img src="' . $avatar_url . '" alt="' . htmlspecialchars($nickname) . '" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 1.5rem; border: 4px solid var(--accent-purple, #8b5cf6);">';
                }
                $html .= '<h2 style="margin: 0 0 0.5rem 0; color: var(--text-main, #fff);">' . htmlspecialchars($nickname) . '</h2>';
                $html .= '<div style="color: var(--text-secondary, #cbd5e0); line-height: 1.6; max-width: 600px; margin: 0 auto;">' . $bio . '</div>';
                $html .= '</div>';

                $content = str_replace('[profile]', $html, $content);
            }
            return $content;
        }
    ]
];

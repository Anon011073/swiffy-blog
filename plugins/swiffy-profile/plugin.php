<?php
/**
 * Plugin Name: Swiffy Profile Shortcode
 * Description: Adds a [profile] shortcode to display a high-end admin profile.
 */

return [
    'hooks' => [
        'render_content' => function($content) {
            if (strpos($content, '[profile]') !== false) {
                require_once __DIR__ . '/../../app/posts.php';
                $config = load_config();
                $nickname = !empty($config['admin_nickname']) ? $config['admin_nickname'] : ($config['admin_user'] ?? 'Admin');
                $handle = !empty($config['admin_user']) ? $config['admin_user'] : 'admin';
                $bio = !empty($config['admin_about_me']) ? nl2br(htmlspecialchars($config['admin_about_me'])) : 'No bio available.';

                $avatar_url = '';
                if ($config['use_gravatar'] ?? false) {
                    $email_hash = md5(strtolower(trim($config['admin_email'] ?? '')));
                    $avatar_url = "https://www.gravatar.com/avatar/$email_hash?s=240&d=mp";
                } elseif (!empty($config['admin_avatar'])) {
                    $avatar_url = "uploads/" . $config['admin_avatar'];
                } else {
                    $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($nickname) . "&background=8b5cf6&color=fff";
                }

                $posts = get_posts();
                $paste_count = count($posts);
                $joined_date = "May 2026"; // Fallback or could be fetched if available

                $html = '
                <style>
                    .sfx-profile-container {
                        max-width: 1000px;
                        margin: 2rem auto;
                        background: #0f172a;
                        border-radius: 24px;
                        overflow: hidden;
                        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                        font-family: "Inter", sans-serif;
                        color: #f8fafc;
                    }
                    .sfx-profile-banner {
                        height: 200px;
                        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
                    }
                    .sfx-profile-header {
                        padding: 0 40px 30px;
                        position: relative;
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-end;
                        margin-top: -60px;
                    }
                    .sfx-profile-info {
                        display: flex;
                        align-items: flex-end;
                        gap: 25px;
                    }
                    .sfx-profile-avatar-wrapper {
                        position: relative;
                    }
                    .sfx-profile-avatar {
                        width: 160px;
                        height: 160px;
                        border-radius: 24px;
                        border: 8px solid #0f172a;
                        object-fit: cover;
                        background: #1e293b;
                    }
                    .sfx-profile-name-section {
                        padding-bottom: 10px;
                    }
                    .sfx-profile-name-row {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        margin-bottom: 4px;
                    }
                    .sfx-profile-nickname {
                        font-size: 2rem;
                        font-weight: 800;
                        margin: 0;
                        color: #fff;
                    }
                    .sfx-profile-badge {
                        background: rgba(245, 158, 11, 0.2);
                        color: #f59e0b;
                        padding: 4px 10px;
                        border-radius: 8px;
                        font-size: 0.7rem;
                        font-weight: 800;
                        text-transform: uppercase;
                        letter-spacing: 0.05em;
                        border: 1px solid rgba(245, 158, 11, 0.3);
                    }
                    .sfx-profile-handle {
                        color: #94a3b8;
                        font-size: 1.1rem;
                    }
                    .sfx-profile-stats {
                        display: flex;
                        gap: 20px;
                        padding-bottom: 10px;
                    }
                    .sfx-stat-card {
                        background: rgba(30, 41, 59, 0.5);
                        border: 1px solid rgba(255, 255, 255, 0.05);
                        padding: 12px 24px;
                        border-radius: 16px;
                        text-align: center;
                        min-width: 100px;
                    }
                    .sfx-stat-label {
                        display: block;
                        font-size: 0.65rem;
                        color: #64748b;
                        text-transform: uppercase;
                        letter-spacing: 0.1em;
                        font-weight: 700;
                        margin-bottom: 4px;
                    }
                    .sfx-stat-value {
                        font-size: 1.25rem;
                        font-weight: 800;
                        color: #fff;
                    }
                    .sfx-profile-content {
                        display: grid;
                        grid-template-columns: 1.5fr 1fr;
                        gap: 40px;
                        padding: 20px 40px 40px;
                    }
                    .sfx-section-title {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        font-size: 1.25rem;
                        font-weight: 700;
                        margin-bottom: 20px;
                        color: #fff;
                    }
                    .sfx-section-title i {
                        color: #8b5cf6;
                    }
                    .sfx-paste-list {
                        display: flex;
                        flex-direction: column;
                        gap: 12px;
                    }
                    .sfx-paste-item {
                        background: #1e293b;
                        border: 1px solid rgba(255, 255, 255, 0.05);
                        padding: 20px;
                        border-radius: 16px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        text-decoration: none;
                        transition: all 0.2s ease;
                    }
                    .sfx-paste-item:hover {
                        transform: translateX(8px);
                        background: #2d3748;
                        border-color: rgba(139, 92, 246, 0.3);
                    }
                    .sfx-paste-info h4 {
                        margin: 0 0 4px 0;
                        color: #fff;
                        font-size: 1rem;
                    }
                    .sfx-paste-meta {
                        font-size: 0.8rem;
                        color: #64748b;
                    }
                    .sfx-paste-arrow {
                        color: #475569;
                    }
                    .sfx-about-card {
                        background: #1e293b;
                        border: 1px solid rgba(255, 255, 255, 0.05);
                        padding: 30px;
                        border-radius: 20px;
                        line-height: 1.7;
                        color: #cbd5e1;
                    }
                    @media (max-width: 768px) {
                        .sfx-profile-header {
                            flex-direction: column;
                            align-items: center;
                            text-align: center;
                            padding: 0 20px 20px;
                        }
                        .sfx-profile-info {
                            flex-direction: column;
                            align-items: center;
                        }
                        .sfx-profile-content {
                            grid-template-columns: 1fr;
                        }
                        .sfx-profile-stats {
                            margin-top: 20px;
                        }
                    }
                </style>
                <div class="sfx-profile-container">
                    <div class="sfx-profile-banner"></div>
                    <div class="sfx-profile-header">
                        <div class="sfx-profile-info">
                            <div class="sfx-profile-avatar-wrapper">
                                <img src="' . $avatar_url . '" alt="' . htmlspecialchars($nickname) . '" class="sfx-profile-avatar">
                            </div>
                            <div class="sfx-profile-name-section">
                                <div class="sfx-profile-name-row">
                                    <h2 class="sfx-profile-nickname">' . htmlspecialchars($nickname) . '</h2>
                                    <span class="sfx-profile-badge">Admin</span>
                                </div>
                                <div class="sfx-profile-handle">@' . htmlspecialchars($handle) . '</div>
                            </div>
                        </div>
                        <div class="sfx-profile-stats">
                            <div class="sfx-stat-card">
                                <span class="sfx-stat-label">Pastes</span>
                                <span class="sfx-stat-value">' . $paste_count . '</span>
                            </div>
                            <div class="sfx-stat-card">
                                <span class="sfx-stat-label">Joined</span>
                                <span class="sfx-stat-value">' . $joined_date . '</span>
                            </div>
                        </div>
                    </div>
                    <div class="sfx-profile-content">
                        <div class="sfx-left-col">
                            <h3 class="sfx-section-title">📦 Public Pastes</h3>
                            <div class="sfx-paste-list">';

                $limit = 5;
                $recent_posts = array_slice($posts, 0, $limit);
                if (empty($recent_posts)) {
                    $html .= '<div style="padding: 40px; text-align: center; color: #64748b;">No public pastes yet.</div>';
                } else {
                    foreach ($recent_posts as $p) {
                        $p_date = date("M d, Y", strtotime($p['date']));
                        $html .= '
                        <a href="post.php?post=' . $p['slug'] . '" class="sfx-paste-item">
                            <div class="sfx-paste-info">
                                <h4>' . htmlspecialchars($p['title']) . '</h4>
                                <div class="sfx-paste-meta">post • ' . $p_date . '</div>
                            </div>
                            <div class="sfx-paste-arrow">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </a>';
                    }
                }

                $html .= '
                            </div>
                        </div>
                        <div class="sfx-right-col">
                            <h3 class="sfx-section-title">ℹ️ About</h3>
                            <div class="sfx-about-card">
                                ' . $bio . '
                            </div>
                        </div>
                    </div>
                </div>';

                $content = str_replace('[profile]', $html, $content);
            }
            return $content;
        }
    ]
];

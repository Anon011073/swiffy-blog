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
                require_once __DIR__ . '/../../app/comments.php';

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

                // Get latest 5 posts
                $all_posts = get_posts();
                $paste_count = count($all_posts);
                $latest_posts = array_slice($all_posts, 0, 5);
                $joined_date = "May 2026";

                // Get latest 5 comments across all posts
                $all_comments = [];
                $post_files = glob(__DIR__ . '/../../content/comments/*.json');
                foreach ($post_files as $f) {
                    $post_slug = basename($f, '.json');
                    $comments = json_decode(file_get_contents($f), true);
                    if ($comments) {
                        foreach ($comments as $cid => $c) {
                            $c['post_slug'] = $post_slug;
                            $all_comments[] = $c;
                        }
                    }
                }
                usort($all_comments, function($a, $b) {
                    return strtotime($b['date']) - strtotime($a['date']);
                });
                $latest_comments = array_slice($all_comments, 0, 5);

                $html = '
                <style>
                    .sfx-profile-wrapper {
                        display: flex;
                        justify-content: center;
                        padding: 2rem 1rem;
                    }
                    .sfx-profile-container {
                        width: 100%;
                        max-width: 850px;
                        background: #0D131F;
                        border-radius: 24px;
                        overflow: hidden;
                        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                        font-family: "Inter", sans-serif;
                        color: #f8fafc;
                        border: 1px solid rgba(255, 255, 255, 0.05);
                    }
                    .sfx-profile-banner {
                        height: 180px;
                        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
                    }
                    .sfx-profile-header {
                        padding: 0 40px 30px;
                        position: relative;
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-end;
                        margin-top: -50px;
                    }
                    .sfx-profile-info {
                        display: flex;
                        align-items: flex-end;
                        gap: 20px;
                    }
                    .sfx-profile-avatar {
                        width: 140px;
                        height: 140px;
                        border-radius: 20px;
                        border: 6px solid #0D131F;
                        object-fit: cover;
                        background: #1e293b;
                    }
                    .sfx-profile-nickname { font-size: 1.8rem; font-weight: 800; margin: 0; color: #fff; }
                    .sfx-profile-badge {
                        background: rgba(245, 158, 11, 0.2);
                        color: #f59e0b;
                        padding: 3px 8px;
                        border-radius: 6px;
                        font-size: 0.65rem;
                        font-weight: 800;
                        text-transform: uppercase;
                        border: 1px solid rgba(245, 158, 11, 0.3);
                    }
                    .sfx-profile-handle { color: #94a3b8; font-size: 1rem; }

                    .sfx-profile-stats { display: flex; gap: 15px; }
                    .sfx-stat-card {
                        background: rgba(30, 41, 59, 0.4);
                        border: 1px solid rgba(255, 255, 255, 0.03);
                        padding: 10px 20px;
                        border-radius: 14px;
                        text-align: center;
                    }
                    .sfx-stat-label { display: block; font-size: 0.6rem; color: #64748b; text-transform: uppercase; font-weight: 700; }
                    .sfx-stat-value { font-size: 1.1rem; font-weight: 800; color: #fff; }

                    .sfx-profile-content {
                        display: grid;
                        grid-template-columns: 1.4fr 1fr;
                        gap: 30px;
                        padding: 20px 40px 40px;
                    }
                    .sfx-section-title {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        font-size: 1.1rem;
                        font-weight: 700;
                        margin-bottom: 15px;
                        color: #fff;
                    }
                    .sfx-section-title svg { color: #8b5cf6; }

                    .sfx-card-list { display: flex; flex-direction: column; gap: 10px; }
                    .sfx-item-card {
                        background: #09090B;
                        border: 1px solid rgba(255, 255, 255, 0.05);
                        padding: 15px;
                        border-radius: 12px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        text-decoration: none;
                        transition: 0.2s;
                    }
                    .sfx-item-card:hover { transform: translateX(5px); border-color: rgba(139, 92, 246, 0.3); }
                    .sfx-item-card h4 { margin: 0 0 3px 0; color: #fff; font-size: 0.95rem; }
                    .sfx-item-meta { font-size: 0.75rem; color: #64748b; }

                    .sfx-about-card {
                        background: #09090B;
                        border: 1px solid rgba(255, 255, 255, 0.05);
                        padding: 20px;
                        border-radius: 16px;
                        line-height: 1.6;
                        color: #cbd5e1;
                        font-size: 0.95rem;
                        margin-bottom: 25px;
                    }

                    @media (max-width: 768px) {
                        .sfx-profile-header { flex-direction: column; align-items: center; text-align: center; }
                        .sfx-profile-info { flex-direction: column; align-items: center; }
                        .sfx-profile-content { grid-template-columns: 1fr; }
                    }
                </style>
                <div class="sfx-profile-wrapper">
                    <div class="sfx-profile-container">
                        <div class="sfx-profile-banner"></div>
                        <div class="sfx-profile-header">
                            <div class="sfx-profile-info">
                                <img src="' . $avatar_url . '" alt="' . htmlspecialchars($nickname) . '" class="sfx-profile-avatar">
                                <div class="sfx-profile-name-section">
                                    <div style="display: flex; align-items: center; gap: 10px;">
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
                                <h3 class="sfx-section-title">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                    Public Pastes
                                </h3>
                                <div class="sfx-card-list">';

                if (empty($latest_posts)) {
                    $html .= '<div style="padding: 20px; text-align: center; color: #64748b; font-size: 0.9rem;">No public pastes yet.</div>';
                } else {
                    foreach ($latest_posts as $p) {
                        $p_date = date("M d, Y", strtotime($p['date']));
                        $html .= '
                        <a href="index.php?post=' . $p['slug'] . '" class="sfx-item-card">
                            <div>
                                <h4>' . htmlspecialchars($p['title']) . '</h4>
                                <div class="sfx-item-meta">post • ' . $p_date . '</div>
                            </div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                        </a>';
                    }
                }

                $html .= '
                                </div>

                                <h3 class="sfx-section-title" style="margin-top: 30px;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                    Recent Activity
                                </h3>
                                <div class="sfx-card-list">';

                if (empty($latest_comments)) {
                    $html .= '<div style="padding: 20px; text-align: center; color: #64748b; font-size: 0.9rem;">No recent activity.</div>';
                } else {
                    foreach ($latest_comments as $c) {
                        $c_date = date("M d, Y", strtotime($c['date']));
                        $excerpt = substr(strip_tags($c['comment']), 0, 60) . (strlen($c['comment']) > 60 ? '...' : '');
                        $html .= '
                        <a href="index.php?post=' . $c['post_slug'] . '#comments" class="sfx-item-card">
                            <div>
                                <h4>Comment on ' . htmlspecialchars($c['post_slug']) . '</h4>
                                <div class="sfx-item-meta">"' . htmlspecialchars($excerpt) . '" • ' . $c_date . '</div>
                            </div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                        </a>';
                    }
                }

                $html .= '
                                </div>
                            </div>
                            <div class="sfx-right-col">
                                <h3 class="sfx-section-title">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                    About
                                </h3>
                                <div class="sfx-about-card">
                                    ' . $bio . '
                                </div>
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

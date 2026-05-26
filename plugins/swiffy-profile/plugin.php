<?php
/**
 * Swiffy Profile Plugin
 * Generates a high-end public profile page.
 */
return [
    'hooks' => [
        'render_content' => function($content) {
            if (strpos($content, '[profile]') !== false) {
                require_once __DIR__ . '/../../app/posts.php';
                require_once __DIR__ . '/../../app/comments.php';

                $config = load_config();
                $opts = $config['theme_options'] ?? [];
                $nickname = !empty($config['admin_nickname']) ? $config['admin_nickname'] : ($config['admin_user'] ?? 'Admin');
                $handle = !empty($config['admin_user']) ? $config['admin_user'] : 'admin';
                $bio = !empty($config['admin_about_me']) ? nl2br(htmlspecialchars($config['admin_about_me'])) : 'No bio available.';

                $avatar_url = '';
                if ($config['use_gravatar'] ?? false) {
                    $email_hash = md5(strtolower(trim($config['admin_email'] ?? '')));
                    $avatar_url = "https://www.gravatar.com/avatar/$email_hash?s=240&d=mp";
                } elseif (!empty($config['admin_avatar'])) {
                    $avatar_url = "uploads/" . $config['admin_avatar'];
                }

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
                    return strtotime($b['date'] ?? 'now') - strtotime($a['date'] ?? 'now');
                });
                $latest_comments = array_slice($all_comments, 0, 5);

                $profile_width = !empty($opts['reading_max_width']) ? $opts['reading_max_width'] : 720;

                $html = '
                <style>
                    .sfx-profile-wrapper {
                        display: flex;
                        justify-content: center;
                        padding: 20px;
                        background: #0D131F;
                        min-height: 100vh;
                        font-family: "Inter", sans-serif;
                    }
                    .sfx-profile-container {
                        width: 100%;
                        max-width: ' . $profile_width . 'px;
                        background: #111827;
                        border-radius: 24px;
                        overflow: hidden;
                        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                        border: 1px solid rgba(255, 255, 255, 0.05);
                    }
                    .sfx-profile-banner {
                        height: 200px;
                        background: linear-gradient(135deg, #8b5cf6 0%, #d946ef 100%);
                        position: relative;
                    }
                    .sfx-profile-header {
                        padding: 0 40px 20px;
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-end;
                        margin-top: -60px;
                        position: relative;
                        z-index: 10;
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
                        border: 6px solid #111827;
                        object-fit: cover;
                        background: #1e293b;
                        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
                    }
                    .sfx-profile-name-section {
                        padding-bottom: 10px;
                    }
                    .sfx-profile-nickname { font-size: 2.2rem; font-weight: 800; margin: 0; color: #fff; line-height: 1.2; }
                    .sfx-profile-badge {
                        background: rgba(245, 158, 11, 0.2);
                        color: #f59e0b;
                        padding: 4px 10px;
                        border-radius: 6px;
                        font-size: 0.7rem;
                        font-weight: 800;
                        text-transform: uppercase;
                        border: 1px solid rgba(245, 158, 11, 0.3);
                        vertical-align: middle;
                    }
                    .sfx-profile-handle { color: #94a3b8; font-size: 1.1rem; margin-top: 5px; }

                    .sfx-profile-stats { display: flex; gap: 15px; padding-bottom: 10px; }
                    .sfx-stat-card {
                        background: rgba(30, 41, 59, 0.6);
                        border: 1px solid rgba(255, 255, 255, 0.05);
                        padding: 12px 24px;
                        border-radius: 16px;
                        text-align: center;
                        backdrop-filter: blur(10px);
                    }
                    .sfx-stat-label { display: block; font-size: 0.65rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; margin-bottom: 4px; }
                    .sfx-stat-value { font-size: 1.2rem; font-weight: 800; color: #fff; }

                    .sfx-profile-content {
                        display: grid;
                        grid-template-columns: 1.1fr 1fr;
                        gap: 20px;
                        padding: 20px 20px 20px;
                    }
                    .sfx-section-title {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        font-size: 1.2rem;
                        font-weight: 700;
                        margin-bottom: 20px;
                        color: #fff;
                        letter-spacing: -0.01em;
                    }
                    .sfx-section-title svg { color: #8b5cf6; }

                    .sfx-card-list { display: flex; flex-direction: column; gap: 12px; }
                    .sfx-item-card {
                        background: #09090B;
                        border: 1px solid rgba(255, 255, 255, 0.05);
                        padding: 18px;
                        border-radius: 14px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        text-decoration: none;
                        transition: all 0.2s ease;
                    }
                    .sfx-item-card:hover { transform: translateX(5px); border-color: rgba(139, 92, 246, 0.4); background: #121214; }
                    .sfx-item-card h4 { margin: 0 0 5px 0; color: #fff; font-size: 1rem; font-weight: 600; }
                    .sfx-item-meta { font-size: 0.8rem; color: #64748b; }

                    .sfx-about-card {
                        background: #09090B;
                        border: 1px solid rgba(255, 255, 255, 0.05);
                        padding: 25px;
                        border-radius: 20px;
                        line-height: 1.7;
                        color: #cbd5e1;
                        font-size: 1rem;
                        margin-bottom: 30px;
                    }

                    .sfx-social-links {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 10px;
                        margin-top: 20px;
                    }
                    .sfx-social-btn {
                        width: 40px;
                        height: 40px;
                        border-radius: 10px;
                        background: #1f2937;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: #fff;
                        text-decoration: none;
                        transition: 0.2s;
                        border: 1px solid rgba(255, 255, 255, 0.05);
                    }
                    .sfx-social-btn:hover { background: #8b5cf6; transform: translateY(-3px); }

                    @media (max-width: 850px) {
                        .sfx-profile-header { flex-direction: column; align-items: center; text-align: center; margin-top: -70px; }
                        .sfx-profile-info { flex-direction: column; align-items: center; }
                        .sfx-profile-content { grid-template-columns: 1fr; }
                        .sfx-profile-stats { margin-top: 20px; }
                    }
                </style>
                <div class="sfx-profile-wrapper">
                    <div class="sfx-profile-container">
                        <div class="sfx-profile-banner"></div>
                        <div class="sfx-profile-header">
                            <div class="sfx-profile-info">
                                <img src="' . $avatar_url . '" alt="' . htmlspecialchars($nickname) . '" class="sfx-profile-avatar">
                                <div class="sfx-profile-name-section">
                                    <div style="display: flex; align-items: center; gap: 12px; justify-content: inherit;">
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
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                    Public Pastes
                                </h3>
                                <div class="sfx-card-list">';

                if (empty($latest_posts)) {
                    $html .= '<div style="padding: 20px; text-align: center; color: #64748b; font-size: 0.9rem;">No public pastes yet.</div>';
                } else {
                    foreach ($latest_posts as $p) {
                        $p_date = date("M d, Y", strtotime($p['date']));
                        $html .= '
                        <a href="index.php?post=' . ($p['slug'] ?? '') . '" class="sfx-item-card">
                            <div>
                                <h4>' . htmlspecialchars($p['title'] ?? 'Untitled') . '</h4>
                                <div class="sfx-item-meta">post • ' . $p_date . '</div>
                            </div>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                        </a>';
                    }
                }

                $html .= '
                                </div>

                                <h3 class="sfx-section-title" style="margin-top: 40px;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                    Recent Activity
                                </h3>
                                <div class="sfx-card-list">';

                if (empty($latest_comments)) {
                    $html .= '<div style="padding: 20px; text-align: center; color: #64748b; font-size: 0.9rem;">No recent activity.</div>';
                } else {
                    foreach ($latest_comments as $c) {
                        $c_date = date("M d, Y", strtotime($c['date'] ?? 'now'));
                        $comment_text = $c['comment'] ?? '';
                        $excerpt = substr(strip_tags((string)$comment_text), 0, 60) . (strlen((string)$comment_text) > 60 ? '...' : '');
                        $html .= '
                        <a href="index.php?post=' . ($c['post_slug'] ?? '') . '#comments" class="sfx-item-card">
                            <div>
                                <h4>Comment on ' . htmlspecialchars($c['post_slug'] ?? 'Post') . '</h4>
                                <div class="sfx-item-meta">"' . htmlspecialchars($excerpt) . '" • ' . $c_date . '</div>
                            </div>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                        </a>';
                    }
                }

                $html .= '
                                </div>
                            </div>
                            <div class="sfx-right-col">
                                <h3 class="sfx-section-title">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                    About
                                </h3>
                                <div class="sfx-about-card">
                                    ' . $bio . '

                                    <div class="sfx-social-links">';

                $socials = [
                    'facebook' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>',
                    'x' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l11.733 16h4.267l-11.733-16z M4 20l6.768-6.768 M13.232 10.768L20 4"></path></svg>',
                    'instagram' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>',
                    'tiktok' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path></svg>',
                    'github' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>',
                    'deviantart' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18.4 4.4L16.8 2H11l-.8 1.4-1.2 2.2H4.4L2.8 8h4.4l1.2 2.2-1.2 2.2H2.8L4.4 14.8l1.6 2.4H11l.8-1.4 1.2-2.2h4.6l1.6-2.4h-4.4L14.6 9l1.2-2.2h4.4z"></path></svg>'
                ];

                foreach ($socials as $key => $icon) {
                    $url = $config['social_' . $key] ?? '';
                    if ($url) {
                        $html .= '<a href="' . htmlspecialchars($url) . '" target="_blank" class="sfx-social-btn" title="' . ucfirst($key) . '">' . $icon . '</a>';
                    }
                }

                $html .= '
                                    </div>
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

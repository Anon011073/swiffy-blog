<?php
$comments_limit = $config['recent_comments_limit'] ?? 3;
$show_avatar = $config['show_comment_avatar'] ?? true;
$avatar_size = $config['comment_avatar_size'] ?? 40;
$show_excerpt = $config['show_comment_excerpt'] ?? true;
$excerpt_length = $config['comment_excerpt_length'] ?? 50;
$widget_title = $config['recent_comments_title'] ?? 'Recent Comments';

$commentics_enabled = $config['commentics_enabled'] ?? false;
$disqus_shortname = $config['disqus_shortname'] ?? '';

echo '<div class="widget recent-comments-widget">';
echo '<h3>' . htmlspecialchars($widget_title) . '</h3>';

if ($commentics_enabled) {
    // Commentics Integration
    $db_host = $config['commentics_db_host'] ?? 'localhost';
    $db_name = $config['commentics_db_name'] ?? '';
    $db_user = $config['commentics_db_user'] ?? '';
    $db_pass = $config['commentics_db_pass'] ?? '';
    $db_prefix = $config['commentics_db_prefix'] ?? 'cmtx_';

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT c.id, c.name, c.comment, c.dated, p.identifier as post_slug
                FROM {$db_prefix}comments c
                JOIN {$db_prefix}pages p ON c.page_id = p.id
                WHERE c.is_approved = 1
                ORDER BY c.dated DESC
                LIMIT " . (int)$comments_limit;

        $stmt = $pdo->query($sql);
        $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($recent)) {
            echo '<p>No comments yet.</p>';
        } else {
            echo '<ul class="recent-comments-list">';
            foreach ($recent as $c) {
                $name = htmlspecialchars($c['name']);
                $date = date('M d, Y', strtotime($c['dated']));
                $url = "index.php?post=" . urlencode($c['post_slug']) . "#cmtx_comment_" . $c['id'];

                echo '<li class="comment-item">';
                echo '<div class="comment-meta">';
                echo '<span class="comment-author">' . $name . '</span> on ';
                echo '<a href="' . $url . '" class="comment-link">' . str_replace('-', ' ', $c['post_slug']) . '</a>';
                echo '<br><small class="comment-date">' . $date . '</small>';

                if ($show_excerpt) {
                    $text = strip_tags($c['comment']);
                    if (strlen($text) > $excerpt_length) {
                        $text = substr($text, 0, $excerpt_length) . '...';
                    }
                    echo '<p class="comment-excerpt">' . htmlspecialchars($text) . '</p>';
                }
                echo '</div>';
                echo '</li>';
            }
            echo '</ul>';
        }
    } catch (PDOException $e) {
        echo '<p style="color:red; font-size:0.8rem; opacity:0.6;">Connection failed</p>';
    }
} elseif (!empty($disqus_shortname)) {
    echo '<div id="recentcomments" class="dsq-widget">';
    echo '<script type="text/javascript" src="https://' . htmlspecialchars($disqus_shortname) . '.disqus.com/recent_comments_widget.js?num_items=' . (int)$comments_limit . '&hide_avatars=' . ($show_avatar ? '0' : '1') . '&avatar_size=' . (int)$avatar_size . '"></script>';
    echo '</div>';
} else {
    $all_comments = [];
    $comment_files = glob(__DIR__ . '/../../content/comments/*.json');

    foreach ($comment_files as $file) {
        $post_slug = basename($file, '.json');
        $data = json_decode(file_get_contents($file), true);
        if ($data) {
            foreach ($data as $id => $comment) {
                if ($comment['approved'] ?? true) {
                    $comment['post_slug'] = $post_slug;
                    $comment['id'] = $id;
                    $all_comments[] = $comment;
                }
            }
        }
    }

    usort($all_comments, function($a, $b) {
        return strtotime($b['date'] ?? '') - strtotime($a['date'] ?? '');
    });

    $recent = array_slice($all_comments, 0, $comments_limit);

    if (empty($recent)) {
        echo '<p>No comments yet.</p>';
    } else {
        echo '<ul class="recent-comments-list">';
        foreach ($recent as $c) {
            $name = htmlspecialchars($c['nickname'] ?? 'Anonymous');
            $date = date('M d, Y', strtotime($c['date'] ?? 'now'));
            $url = "index.php?post=" . urlencode($c['post_slug']) . "#comment-" . $c['id'];

            $avatar_url = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($c['email'] ?? ''))) . "?s=" . $avatar_size . "&d=mp";
            if ($name === ($config['admin_nickname'] ?? $config['admin_user'])) {
                 if (!empty($config['admin_avatar'])) {
                     $avatar_url = "uploads/" . $config['admin_avatar'];
                 }
            }

            echo '<li class="comment-item">';
            if ($show_avatar) {
                echo '<img src="' . $avatar_url . '" width="' . $avatar_size . '" height="' . $avatar_size . '" class="comment-avatar">';
            }
            echo '<div class="comment-meta">';
            echo '<span class="comment-author">' . $name . '</span> on ';
            echo '<a href="' . $url . '" class="comment-link">' . str_replace('-', ' ', $c['post_slug']) . '</a>';
            echo '<br><small class="comment-date">' . $date . '</small>';

            if ($show_excerpt) {
                $text = strip_tags($c['content'] ?? '');
                if (strlen($text) > $excerpt_length) {
                    $text = substr($text, 0, $excerpt_length) . '...';
                }
                echo '<p class="comment-excerpt">' . htmlspecialchars($text) . '</p>';
            }
            echo '</div>';
            echo '</li>';
        }
        echo '</ul>';
    }
}
echo '</div>';
?>

<style>
.recent-comments-list { list-style: none; padding: 0; margin: 0; }
.comment-item { display: flex; gap: 12px; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px; }
.comment-item:last-child { border-bottom: none; }
.comment-avatar { border-radius: 50%; object-fit: cover; }
.comment-author { font-weight: 700; color: var(--accent-purple); }
.comment-link { color: #fff; text-decoration: none; opacity: 0.8; font-size: 0.9rem; }
.comment-link:hover { opacity: 1; text-decoration: underline; }
.comment-date { color: #888; font-size: 0.75rem; }
.comment-excerpt { margin: 5px 0 0; font-size: 0.85rem; color: #ccc; line-height: 1.4; }
</style>

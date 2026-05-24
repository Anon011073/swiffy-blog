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
                echo '<strong>' . $name . '</strong> on ';
                echo '<a href="' . $url . '">' . str_replace('-', ' ', $c['post_slug']) . '</a>';
                echo '<br><small>' . $date . '</small>';

                if ($show_excerpt) {
                    $text = strip_tags($c['comment']);
                    if (strlen($text) > $excerpt_length) {
                        $text = substr($text, 0, $excerpt_length) . '...';
                    }
                    echo '<div class="comment-excerpt">' . htmlspecialchars($text) . '</div>';
                }
                echo '</div>';
                echo '</li>';
            }
            echo '</ul>';
        }
    } catch (PDOException $e) {
        echo '<p style="color:red; font-size:0.8rem;">DB Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
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
            echo '<strong>' . $name . '</strong> on ';
            echo '<a href="' . $url . '">' . str_replace('-', ' ', $c['post_slug']) . '</a>';
            echo '<br><small>' . $date . '</small>';

            if ($show_excerpt) {
                $text = strip_tags($c['content'] ?? '');
                if (strlen($text) > $excerpt_length) {
                    $text = substr($text, 0, $excerpt_length) . '...';
                }
                echo '<div class="comment-excerpt">' . htmlspecialchars($text) . '</div>';
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
.comment-item { display: flex; gap: 10px; margin-bottom: 12px; border-bottom: 1px solid #eee; padding-bottom: 8px; }
.comment-item:last-child { border-bottom: none; }
.comment-avatar { border-radius: 4px; }
.comment-meta { font-size: 0.9rem; }
.comment-excerpt { margin-top: 4px; color: #666; font-style: italic; }
</style>

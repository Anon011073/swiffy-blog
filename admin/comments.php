<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/functions.php';

require_login('comments');

$config = load_config();
$comments_dir = __DIR__ . '/../content/comments/';
$success = '';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    if (!verify_csrf_token($_POST['csrf_token'])) die('CSRF');
    $comment_rules = $_POST['comment_rules'] ?? '';
    if (update_config(['comment_rules' => $comment_rules])) {
        $success = "Comment settings updated.";
        $config = load_config();
    }
}

// Handle actions
if (isset($_GET['action']) && isset($_GET['post']) && isset($_GET['id']) && isset($_GET['token'])) {
    if (verify_csrf_token($_GET['token'])) {
        $post_slug = basename($_GET['post']);
        $comment_id = $_GET['id'];
        $comment_file = $comments_dir . $post_slug . '.json';

        if (file_exists($comment_file)) {
            $comments = json_decode(file_get_contents($comment_file), true);
            if (isset($comments[$comment_id])) {
                if ($_GET['action'] === 'approve') {
                    $comments[$comment_id]['approved'] = true;
                    $success = "Comment approved.";
                } elseif ($_GET['action'] === 'delete') {
                    unset($comments[$comment_id]);
                    $success = "Comment deleted.";
                }
                file_put_contents($comment_file, json_encode($comments, JSON_PRETTY_PRINT));
            }
        }
    }
}

// Get all comments across all posts
$all_comments = [];
$comment_files = glob($comments_dir . '*.json');
foreach ($comment_files as $file) {
    $post_slug = basename($file, '.json');
    $comments = json_decode(file_get_contents($file), true);
    if ($comments) {
        foreach ($comments as $id => $comment) {
            $comment['id'] = $id;
            $comment['post_slug'] = $post_slug;
            $all_comments[] = $comment;
        }
    }
}

// Sort comments by date descending
usort($all_comments, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: sans-serif; margin: 0; display: flex; min-height: 100vh; background: #f4f4f4; }
        .sidebar { width: 250px; background: #333; color: #fff; padding: 1rem; }
        .sidebar h2 { font-size: 1.2rem; margin-bottom: 2rem; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin-bottom: 1rem; }
        .sidebar ul li a { color: #ccc; text-decoration: none; display: block; padding: 0.5rem; border-radius: 4px; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background: #444; color: #fff; }
        .main-content { flex: 1; padding: 2rem; margin-left: 310px; margin-top: 50px; }
        .card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .btn { padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none; cursor: pointer; border: none; font-size: 0.9rem; }
        .btn-primary { background: #007bff; color: #fff; }
        .btn-success { background: #28a745; color: #fff; }
        .btn-danger { background: #d9534f; color: #fff; }
        .success { color: #5cb85c; background: #dff0d8; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        table th, table td { text-align: left; padding: 0.75rem; border-bottom: 1px solid #eee; }
        .comment-text { font-style: italic; color: #555; font-size: 0.9rem; }
        .status-pending { color: #856404; background-color: #fff3cd; padding: 2px 5px; border-radius: 3px; font-size: 0.7rem; }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
    <div class="main-content">
        <h1>Comments Management</h1>

        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>💬 Commenting Rules</h3>
            <p style="color: #666; font-size: 0.9rem; margin-bottom: 10px;">This text will appear above the comment box on post pages.</p>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <input type="hidden" name="action" value="update_settings">
                <textarea name="comment_rules" style="width: 100%; height: 80px; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><?php echo htmlspecialchars($config['comment_rules'] ?? ''); ?></textarea>
                <button type="submit" class="btn btn-primary">Save Rules</button>
            </form>
        </div>

        <div class="card">
            <h3>Recent Comments</h3>
            <?php if (empty($all_comments)): ?>
                <p>No comments found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Author</th>
                            <th>Comment</th>
                            <th>Post</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_comments as $c): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($c['nickname']); ?></td>
                                <td class="comment-text"><?php echo htmlspecialchars(substr($c['content'], 0, 50)) . (strlen($c['content']) > 50 ? '...' : ''); ?></td>
                                <td><?php echo htmlspecialchars($c['post_slug']); ?></td>
                                <td>
                                    <?php if ($c['approved'] ?? false): ?>
                                        Approved
                                    <?php else: ?>
                                        <span class="status-pending">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!($c['approved'] ?? false)): ?>
                                        <a href="comments.php?action=approve&id=<?php echo $c['id']; ?>&post=<?php echo $c['post_slug']; ?>&token=<?php echo get_csrf_token(); ?>" class="btn btn-success">Approve</a>
                                    <?php endif; ?>
                                    <a href="comments.php?action=delete&id=<?php echo $c['id']; ?>&post=<?php echo $c['post_slug']; ?>&token=<?php echo get_csrf_token(); ?>" class="btn btn-danger" onclick="return confirm('Delete this comment?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

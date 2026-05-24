<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/posts.php';
require_once __DIR__ . '/../app/functions.php';

require_login('posts');

$posts = get_posts(true); // Include pending for admin
$config = load_config();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .actions { display: flex; gap: 0.5rem; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { text-align: left; padding: 1rem; border-bottom: 1px solid var(--border-color); }
        table th { background: #f8fafc; font-weight: 700; color: #475569; }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
    <div class="main-content">
        <div class="header">
            <h1>Posts</h1>
            <a href="post_edit.php" class="btn btn-primary">Create New Post</a>
        </div>

        <div class="card">
            <?php if (empty($posts)): ?>
                <p>No posts found. Create your first post!</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($post['title']); ?></td>
                                <td style="color: var(--text-muted);"><?php echo htmlspecialchars($post['date']); ?></td>
                                <td>
                                    <?php if (isset($post['status']) && $post['status'] === 'pending'): ?>
                                        <span style="color: var(--warning); background: #fef3c7; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">Pending</span>
                                    <?php else: ?>
                                        <span style="color: var(--success); background: #f0fdf4; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">Published</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <a href="post_edit.php?post=<?php echo $post['slug']; ?>" class="btn" style="background: #e2e8f0; color: #475569;">Edit</a>
                                    <a href="post_delete.php?slug=<?php echo $post['slug']; ?>&token=<?php echo get_csrf_token(); ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
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

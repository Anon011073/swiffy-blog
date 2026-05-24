<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/pages.php';
require_once __DIR__ . '/../app/functions.php';

require_login('pages');

$pages = get_pages();
$config = load_config();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pages - Admin Panel</title>
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
            <h1>Pages</h1>
            <a href="page_edit.php" class="btn btn-primary">Create New Page</a>
        </div>

        <div class="card">
            <?php if (empty($pages)): ?>
                <p>No pages found. Create your first page!</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $p): ?>
                            <tr>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($p['title']); ?></td>
                                <td style="color: var(--text-muted);">/<?php echo htmlspecialchars($p['slug']); ?></td>
                                <td class="actions">
                                    <a href="page_edit.php?slug=<?php echo $p['slug']; ?>" class="btn" style="background: #e2e8f0; color: #475569;">Edit</a>
                                    <a href="page_delete.php?slug=<?php echo $p['slug']; ?>&token=<?php echo get_csrf_token(); ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
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

<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/pages.php';
require_once __DIR__ . '/../app/functions.php';

require_login('pages');

$slug = $_GET['slug'] ?? '';
$page_data = null;
$config = load_config();

if ($slug) {
    $page_data = get_page($slug);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $new_slug = $_POST['slug'] ?? generate_slug($title);

    if (empty($title) || empty($content)) {
        $error = "Title and content are required.";
    } else {
        $data = [
            'title' => $title,
            'slug' => $new_slug,
            'content' => $content
        ];

        if ($slug && $slug !== $new_slug) {
            delete_page($slug);
        }

        if (save_page($data)) {
            $success = "Page saved successfully.";
            $page_data = $data;
            $slug = $new_slug;
        } else {
            $error = "Failed to save page.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_data ? 'Edit Page' : 'Create Page'; ?> - Admin Panel</title>
    <!-- Jodit CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jodit/3.24.2/jodit.min.css"/>
    <style>
        body { font-family: sans-serif; margin: 0; display: flex; min-height: 100vh; background: #f4f4f4; }
        .sidebar { width: 250px; background: #333; color: #fff; padding: 1rem; }
        .sidebar h2 { font-size: 1.2rem; margin-bottom: 2rem; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin-bottom: 1rem; }
        .sidebar ul li a { color: #ccc; text-decoration: none; display: block; padding: 0.5rem; border-radius: 4px; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background: #444; color: #fff; }
        .main-content { flex: 1; padding: 2rem; margin-left: 310px; margin-top: 50px; }
        .card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        input[type="text"], textarea { width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { padding: 0.75rem 1.5rem; border-radius: 4px; text-decoration: none; cursor: pointer; border: none; font-size: 1rem; }
        .btn-primary { background: #007bff; color: #fff; }
        .error { color: #d9534f; background: #f2dede; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; }
        .success { color: #5cb85c; background: #dff0d8; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
    <div class="main-content">
        <h1><?php echo $page_data ? 'Edit Page' : 'Create New Page'; ?></h1>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($page_data['title'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="slug">Slug</label>
                    <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($page_data['slug'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content"><?php echo htmlspecialchars($page_data['content'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Save Page</button>
                <a href="pages.php" class="btn">Cancel</a>
            </form>
        </div>
    </div>

    <!-- Jodit JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jodit/3.24.2/jodit.min.js"></script>
    <script>
        const editor = new Jodit('#content', {
            height: 400
        });
    </script>
</body>
</html>

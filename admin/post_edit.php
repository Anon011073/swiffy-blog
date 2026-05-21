<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/functions.php';
require_once __DIR__ . '/../app/posts.php';

require_login('posts');

$slug = $_GET['post'] ?? '';
$post = $slug ? get_post($slug) : null;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $excerpt = $_POST['excerpt'] ?? '';
    $featured_image = $_POST['featured_image'] ?? '';
    $featured_image_x = $_POST['featured_image_x'] ?? 50;
    $featured_image_y = $_POST['featured_image_y'] ?? 50;
    $date = $_POST['date'] ?? date('Y-m-d');
    $new_slug = $_POST['slug'] ?? generate_slug($title);
    $comments_on = isset($_POST['comments_on']) ? true : false;

    // Core Taxonomies
    $categories = $_POST['categories'] ?? '';
    $tags = $_POST['tags'] ?? '';

    if (empty($title) || empty($content)) {
        $error = "Title and content are required.";
    } else {
        $post_data = [
            'title' => $title,
            'slug' => $new_slug,
            'content' => $content,
            'excerpt' => $excerpt,
            'featured_image' => $featured_image,
            'featured_image_x' => $featured_image_x,
            'featured_image_y' => $featured_image_y,
            'date' => $date,
            'comments_on' => $comments_on,
            'categories' => $categories,
            'tags' => $tags
        ];

        // HOOK: post_saved_pre
        $plugins_data = get_enabled_plugins_data();
        foreach ($plugins_data as $p_data) {
            if (isset($p_data['hooks']['post_saved_pre'])) {
                $post_data = $p_data['hooks']['post_saved_pre']($post_data);
            }
        }

        if ($slug && $slug !== $new_slug) {
            delete_post($slug);
        }

        $post_data['status'] = 'published';
        if (isset($_SESSION['anon_user'])) {
            $user = $_SESSION['anon_user'];
            if (!($user['auto_approve_posts'] ?? false)) {
                $post_data['status'] = 'pending';
            }
        }

        if (save_post($post_data)) {
            $success = "Post saved successfully." . (isset($post_data['status']) && $post_data['status'] === 'pending' ? " (Awaiting Admin Approval)" : "");
            $post = $post_data;
            $slug = $new_slug;
        } else {
            $error = "Failed to save post.";
        }
    }
}

$images = glob(__DIR__ . '/../uploads/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post ? 'Edit Post' : 'Create Post'; ?> - Admin Panel</title>
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
        input[type="text"], input[type="date"], textarea, select { width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { padding: 0.75rem 1.5rem; border-radius: 4px; text-decoration: none; cursor: pointer; border: none; font-size: 1rem; }
        .btn-primary { background: #007bff; color: #fff; }
        .error { color: #d9534f; background: #f2dede; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; }
        .success { color: #5cb85c; background: #dff0d8; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; }
        .image-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; margin-top: 10px; max-height: 200px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; }
        .image-item { cursor: pointer; border: 2px solid transparent; }
        .image-item img { width: 100%; height: auto; display: block; }
        .image-item.selected { border-color: #007bff; }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
    <div class="main-content">
        <h1><?php echo $post ? 'Edit Post' : 'Create New Post'; ?></h1>

        <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <div class="card">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="slug">Slug (Leave empty to auto-generate)</label>
                    <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($post['slug'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($post['date'] ?? date('Y-m-d')); ?>">
                </div>

                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content"><?php echo htmlspecialchars($post['content'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="excerpt">Excerpt (Optional)</label>
                    <textarea id="excerpt" name="excerpt" style="height: 100px;"><?php echo htmlspecialchars($post['excerpt'] ?? ''); ?></textarea>
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #e9ecef;">
                    <div>
                        <label for="categories">Categories (comma separated)</label>
                        <input type="text" id="categories" name="categories" value="<?php echo htmlspecialchars($post['categories'] ?? ''); ?>" placeholder="e.g. News, Tech">
                    </div>
                    <div>
                        <label for="tags">Tags (comma separated)</label>
                        <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars($post['tags'] ?? ''); ?>" placeholder="e.g. php, tutorial">
                    </div>
                </div>

                <div class="form-group">
                    <label>Featured Image</label>
                    <input type="hidden" id="featured_image" name="featured_image" value="<?php echo htmlspecialchars($post['featured_image'] ?? ''); ?>">
                    <div class="image-grid">
                        <div class="image-item <?php echo empty($post['featured_image']) ? 'selected' : ''; ?>" onclick="selectImage('')">
                            <div style="width: 100%; aspect-ratio: 1; background: #eee; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">No Image</div>
                        </div>
                        <?php foreach ($images as $img):
                            $img_name = basename($img);
                            $selected = ($post['featured_image'] ?? '') === $img_name ? 'selected' : '';
                        ?>
                            <div class="image-item <?php echo $selected; ?>" onclick="selectImage('<?php echo $img_name; ?>', this)">
                                <img src="../uploads/<?php echo $img_name; ?>" alt="">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="comments_on" <?php echo ($post['comments_on'] ?? true) ? 'checked' : ''; ?>> Enable comments for this post
                    </label>
                </div>
                <div id="focal-point-section" class="form-group" style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef; margin-top: 20px; display: <?php echo !empty($post['featured_image']) ? 'block' : 'none'; ?>;">
                    <label>🖼️ Featured Image Focal Point</label>
                    <p style="color: #666; font-size: 0.85rem; margin-bottom: 15px;">Drag the marker or use sliders to set the focus area for the index page cards.</p>

                    <div id="preview-frame" style="position: relative; width: 100%; height: 200px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; cursor: crosshair; background: #eee; margin-bottom: 15px;">
                        <img id="preview-img" src="<?php echo !empty($post['featured_image']) ? '../uploads/' . $post['featured_image'] : '../app/demo/uploads/demo_image.jpg'; ?>" style="position: absolute; width: 100%; height: 100%; object-fit: cover; pointer-events: none; object-position: <?php echo ($post['featured_image_x'] ?? 50); ?>% <?php echo ($post['featured_image_y'] ?? 50); ?>%;">
                        <div id="focal-marker" style="position: absolute; width: 30px; height: 30px; border: 3px solid #fff; border-radius: 50%; box-shadow: 0 0 10px rgba(0,0,0,0.5); transform: translate(-50%, -50%); pointer-events: none; z-index: 10; left: <?php echo ($post['featured_image_x'] ?? 50); ?>%; top: <?php echo ($post['featured_image_y'] ?? 50); ?>%;">
                            <div style="width: 6px; height: 6px; background: #fff; border-radius: 50%; margin: 9px auto;"></div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label style="font-size: 0.8rem;">Horizontal (%) <span id="val-x"><?php echo $post['featured_image_x'] ?? 50; ?></span></label>
                            <input type="range" name="featured_image_x" id="input-x" min="0" max="100" value="<?php echo $post['featured_image_x'] ?? 50; ?>" style="width: 100%;">
                        </div>
                        <div>
                            <label style="font-size: 0.8rem;">Vertical (%) <span id="val-y"><?php echo $post['featured_image_y'] ?? 50; ?></span></label>
                            <input type="range" name="featured_image_y" id="input-y" min="0" max="100" value="<?php echo $post['featured_image_y'] ?? 50; ?>" style="width: 100%;">
                        </div>
                    </div>
                </div>

                <script>
                    const frame = document.getElementById('preview-frame');
                    const img = document.getElementById('preview-img');
                    const marker = document.getElementById('focal-marker');
                    const inputX = document.getElementById('input-x');
                    const inputY = document.getElementById('input-y');
                    const valX = document.getElementById('val-x');
                    const valY = document.getElementById('val-y');
                    const focalSection = document.getElementById('focal-point-section');

                    function updateFocalPreview() {
                        const x = inputX.value;
                        const y = inputY.value;
                        img.style.objectPosition = `${x}% ${y}%`;
                        marker.style.left = `${x}%`;
                        marker.style.top = `${y}%`;
                        valX.innerText = x;
                        valY.innerText = y;
                    }

                    inputX.addEventListener('input', updateFocalPreview);
                    inputY.addEventListener('input', updateFocalPreview);

                    let isDragging = false;
                    const handlePointer = (e) => {
                        const rect = frame.getBoundingClientRect();
                        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                        let x = Math.round(((clientX - rect.left) / rect.width) * 100);
                        let y = Math.round(((clientY - rect.top) / rect.height) * 100);
                        inputX.value = Math.max(0, Math.min(100, x));
                        inputY.value = Math.max(0, Math.min(100, y));
                        updateFocalPreview();
                    };

                    frame.addEventListener('mousedown', (e) => { isDragging = true; handlePointer(e); });
                    window.addEventListener('mousemove', (e) => { if (isDragging) handlePointer(e); });
                    window.addEventListener('mouseup', () => { isDragging = false; });

                    updateFocalPreview();
                </script>

                <?php
                foreach (get_enabled_plugins_data() as $p_data) {
                    if (isset($p_data['hooks']['post_form_after'])) {
                        echo $p_data['hooks']['post_form_after']($post);
                    }
                }
                ?>

                <button type="submit" class="btn btn-primary">Save Post</button>
                <a href="index.php" class="btn">Cancel</a>
            </form>
        </div>
    </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jodit/3.24.2/jodit.min.js"></script>
    <script>
        const editor = new Jodit('#content', { height: 400 });
        function selectImage(imgName, el) {
            document.getElementById('featured_image').value = imgName;
            document.querySelectorAll('.image-item').forEach(item => item.classList.remove('selected'));
            if (el) el.classList.add('selected');
            else document.querySelector('.image-item').classList.add('selected');

            const focalImg = document.getElementById('preview-img');
            const focalSection = document.getElementById('focal-point-section');
            if (imgName) {
                if (focalImg) focalImg.src = '../uploads/' + imgName;
                if (focalSection) focalSection.style.display = 'block';
            } else {
                if (focalSection) focalSection.style.display = 'none';
            }
        }
    </script>
</body>
</html>

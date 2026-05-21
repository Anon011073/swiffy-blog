<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/functions.php';

require_login('media');

$img_name = $_GET['img'] ?? '';
$uploads_dir = __DIR__ . '/../uploads/';
$target_file = $uploads_dir . basename($img_name);

if (!file_exists($target_file)) die("Image not found.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) die('CSRF Failed');

    $x = (int)$_POST['x'];
    $y = (int)$_POST['y'];
    $w = (int)$_POST['w'];
    $h = (int)$_POST['h'];

    if ($w > 0 && $h > 0) {
        $info = getimagesize($target_file);
        $mime = $info['mime'];

        switch ($mime) {
            case 'image/jpeg': $src_img = imagecreatefromjpeg($target_file); break;
            case 'image/png':  $src_img = imagecreatefrompng($target_file); break;
            case 'image/gif':  $src_img = imagecreatefromgif($target_file); break;
            case 'image/webp': $src_img = imagecreatefromwebp($target_file); break;
            default: die("Unsupported format");
        }

        $dst_img = imagecreatetruecolor($w, $h);

        // Preserve transparency for PNG/WebP
        if ($mime == 'image/png' || $mime == 'image/webp') {
            imagealphablending($dst_img, false);
            imagesavealpha($dst_img, true);
        }

        imagecopyresampled($dst_img, $src_img, 0, 0, $x, $y, $w, $h, $w, $h);

        // Save as new file to avoid overwriting original immediately?
        // User probably expects overwrite for "Crop".
        switch ($mime) {
            case 'image/jpeg': imagejpeg($dst_img, $target_file, 90); break;
            case 'image/png':  imagepng($dst_img, $target_file); break;
            case 'image/gif':  imagegif($dst_img, $target_file); break;
            case 'image/webp': imagewebp($dst_img, $target_file); break;
        }

        imagedestroy($src_img);
        imagedestroy($dst_img);

        header("Location: media.php?success=Image cropped successfully.");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Crop Image - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .main-content { margin-left: 310px; margin-top: 60px; padding: 20px; }
        .crop-container { background: #fff; padding: 20px; border-radius: 8px; text-align: center; }
        #crop-preview { max-width: 100%; border: 2px dashed #007bff; margin-bottom: 20px; cursor: crosshair; }
        .form-group { display: flex; gap: 10px; justify-content: center; margin-bottom: 20px; }
        .form-group input { width: 80px; padding: 5px; text-align: center; }
    </style>
</head>
<body>
    <?php include "sidebar.php"; ?>
    <div class="main-content">
        <h1>Crop: <?php echo htmlspecialchars($img_name); ?></h1>
        <div class="crop-container">
            <p>Click and drag on the image to select the area you want to keep.</p>
            <div style="position: relative; display: inline-block;">
                <img src="../uploads/<?php echo $img_name; ?>?t=<?php echo time(); ?>" id="crop-preview">
                <div id="selection-box" style="position: absolute; border: 2px solid #fff; box-shadow: 0 0 0 9999px rgba(0,0,0,0.5); pointer-events: none; display: none;"></div>
            </div>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <input type="hidden" name="x" id="x" value="0">
                <input type="hidden" name="y" id="y" value="0">
                <input type="hidden" name="w" id="w" value="0">
                <input type="hidden" name="h" id="h" value="0">

                <div class="form-group">
                    <div>X: <input type="number" id="disp-x" readonly></div>
                    <div>Y: <input type="number" id="disp-y" readonly></div>
                    <div>W: <input type="number" id="disp-w" readonly></div>
                    <div>H: <input type="number" id="disp-h" readonly></div>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 10px 30px;">Apply Crop</button>
                <a href="media.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    <script>
        const img = document.getElementById('crop-preview');
        const box = document.getElementById('selection-box');
        let startX, startY, isDragging = false;

        img.addEventListener('mousedown', (e) => {
            startX = e.offsetX;
            startY = e.offsetY;
            isDragging = true;
            box.style.display = 'block';
            updateBox(startX, startY, 0, 0);
        });

        window.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            const rect = img.getBoundingClientRect();
            let currentX = e.clientX - rect.left;
            let currentY = e.clientY - rect.top;

            // Clamp
            currentX = Math.max(0, Math.min(currentX, img.width));
            currentY = Math.max(0, Math.min(currentY, img.height));

            const x = Math.min(startX, currentX);
            const y = Math.min(startY, currentY);
            const w = Math.abs(startX - currentX);
            const h = Math.abs(startY - currentY);

            updateBox(x, y, w, h);
        });

        window.addEventListener('mouseup', () => {
            isDragging = false;
        });

        function updateBox(x, y, w, h) {
            // Adjust for natural image size
            const scaleX = img.naturalWidth / img.width;
            const scaleY = img.naturalHeight / img.height;

            box.style.left = x + 'px';
            box.style.top = y + 'px';
            box.style.width = w + 'px';
            box.style.height = h + 'px';

            document.getElementById('x').value = Math.round(x * scaleX);
            document.getElementById('y').value = Math.round(y * scaleY);
            document.getElementById('w').value = Math.round(w * scaleX);
            document.getElementById('h').value = Math.round(h * scaleY);

            document.getElementById('disp-x').value = Math.round(x * scaleX);
            document.getElementById('disp-y').value = Math.round(y * scaleY);
            document.getElementById('disp-w').value = Math.round(w * scaleX);
            document.getElementById('disp-h').value = Math.round(h * scaleY);
        }
    </script>
</body>
</html>

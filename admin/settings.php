<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/functions.php';

require_login('settings');

$config = load_config();
$error = '';
$success = '';
$uploads_dir = __DIR__ . '/../uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) die('CSRF token validation failed.');

    if (isset($_POST['action']) && $_POST['action'] === 'reset_settings') {
        $defaults = [
            'site_name' => 'My Swiffy Blog',
            'posts_per_page' => 5,
            'sidebar_position' => 'right',
            'comments_enabled' => true,
            'back_to_top_enabled' => true,
            'back_to_top_type' => 'icon',
            'back_to_top_color' => '#8b5cf6',
            'back_to_top_size' => 40,
            'back_to_top_text' => 'Top',
            'show_author_bio' => true,
            'footer_text' => ''
        ];
        $new_config = array_merge($config, $defaults);
        if (update_config($new_config)) {
            $success = "Settings reset to defaults.";
            $config = load_config();
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'import_demo') {
        require_once __DIR__ . '/../app/demo_importer.php';
        if (import_demo_content()) {
            $success = "Demo content imported successfully!";
            $config = load_config();
        } else {
            $error = "Failed to import demo content.";
        }
    } else {
        $site_name = sanitize($_POST['site_name'] ?? '');
        $footer_text = $_POST['footer_text'] ?? '';
        $back_to_top_enabled = isset($_POST['back_to_top_enabled']);
        $back_to_top_type = $_POST['back_to_top_type'] ?? 'icon';
        $back_to_top_text = sanitize($_POST['back_to_top_text'] ?? 'Top');
        $back_to_top_color = $_POST['back_to_top_color'] ?? '#8b5cf6';
        $back_to_top_size = (int)($_POST['back_to_top_size'] ?? 40);
        $show_author_bio = isset($_POST['show_author_bio']);
        $comments_enabled = isset($_POST['comments_enabled']);
        $disqus_shortname = sanitize($_POST['disqus_shortname'] ?? '');
        $commentics_enabled = isset($_POST['commentics_enabled']);
        $commentics_path = sanitize($_POST['commentics_path'] ?? 'commentics/');
        $commentics_db_host = sanitize($_POST['commentics_db_host'] ?? 'localhost');
        $commentics_db_name = sanitize($_POST['commentics_db_name'] ?? '');
        $commentics_db_user = sanitize($_POST['commentics_db_user'] ?? '');
        $commentics_db_pass = $_POST['commentics_db_pass'] ?? '';
        $commentics_db_prefix = sanitize($_POST['commentics_db_prefix'] ?? 'cmtx_');
        $posts_per_page = (int)($_POST['posts_per_page'] ?? 5);
        $sidebar_position = $_POST['sidebar_position'] ?? 'right';

        $new_config = $config;
        $new_config['site_name'] = $site_name;
        $new_config['footer_text'] = $footer_text;
        $new_config['back_to_top_enabled'] = $back_to_top_enabled;
        $new_config['back_to_top_type'] = $back_to_top_type;
        $new_config['back_to_top_text'] = $back_to_top_text;
        $new_config['back_to_top_color'] = $back_to_top_color;
        $new_config['back_to_top_size'] = $back_to_top_size;
        $new_config['show_author_bio'] = $show_author_bio;
        $new_config['comments_enabled'] = $comments_enabled;
        $new_config['recent_comments_title'] = $_POST['recent_comments_title'] ?? 'Recent Comments';
        $new_config['recent_comments_limit'] = (int)($_POST['recent_comments_limit'] ?? 3);
        $new_config['comment_avatar_size'] = (int)($_POST['comment_avatar_size'] ?? 40);
        $new_config['comment_excerpt_length'] = (int)($_POST['comment_excerpt_length'] ?? 50);
        $new_config['show_comment_avatar'] = isset($_POST['show_comment_avatar']);
        $new_config['show_comment_excerpt'] = isset($_POST['show_comment_excerpt']);
        $new_config['disqus_shortname'] = $disqus_shortname;
        $new_config['commentics_enabled'] = $commentics_enabled;
        $new_config['commentics_path'] = $commentics_path;
        $new_config['commentics_db_host'] = $commentics_db_host;
        $new_config['commentics_db_name'] = $commentics_db_name;
        $new_config['commentics_db_user'] = $commentics_db_user;
        $new_config['commentics_db_pass'] = $commentics_db_pass;
        $new_config['commentics_db_prefix'] = $commentics_db_prefix;
        $new_config['posts_per_page'] = $posts_per_page;
        $new_config['sidebar_position'] = $sidebar_position;

        if (update_config($new_config)) {
            $success = "Settings updated.";
            $config = load_config();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - Swiffy Blog</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .main-content { margin-left: 310px; margin-top: 70px; padding: 2rem; }
        .card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
<div class="main-content">
    <h1>⚙️ General Settings</h1>
    <?php if ($success): ?><div class="alert alert-success" style="background:#def7ec; color:#03543f; padding:15px; border-radius:4px; margin-bottom:15px;"><?php echo $success; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger" style="background:#fde8e8; color:#9b1c1c; padding:15px; border-radius:4px; margin-bottom:15px;"><?php echo $error; ?></div><?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
        <div class="card" style="border-top: 5px solid #22c55e;">
            <h3>💬 Commentics Integration</h3>
            <p style="font-size: 0.9rem; color: #666; margin-bottom: 15px;">Integrate <a href="https://commentics.org" target="_blank">Commentics</a> for advanced moderation, anti-spam, and features.</p>

            <label style="display: flex; align-items: center; cursor: pointer; font-weight:bold; margin-bottom: 15px;">
                <input type="checkbox" name="commentics_enabled" <?php echo ($config["commentics_enabled"] ?? false) ? "checked" : ""; ?> style="margin-right: 10px;">
                Enable Commentics (Overrides built-in/Disqus)
            </label>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                <div>
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Commentics Folder Path</label>
                    <input type="text" name="commentics_path" value="<?php echo htmlspecialchars($config["commentics_path"] ?? "commentics/"); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;" placeholder="e.g. commentics/">
                </div>
                <div>
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Database Prefix</label>
                    <input type="text" name="commentics_db_prefix" value="<?php echo htmlspecialchars($config["commentics_db_prefix"] ?? "cmtx_"); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                </div>
            </div>

            <div style="background: #f9f9f9; padding: 15px; border-radius: 6px; border: 1px solid #eee;">
                <h4 style="margin-top: 0; margin-bottom: 10px;">Database Connection</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label style="display:block; margin-bottom:5px; font-size: 0.85rem;">Host</label>
                        <input type="text" name="commentics_db_host" value="<?php echo htmlspecialchars($config["commentics_db_host"] ?? "localhost"); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:5px; font-size: 0.85rem;">Database Name</label>
                        <input type="text" name="commentics_db_name" value="<?php echo htmlspecialchars($config["commentics_db_name"] ?? ""); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:5px; font-size: 0.85rem;">Username</label>
                        <input type="text" name="commentics_db_user" value="<?php echo htmlspecialchars($config["commentics_db_user"] ?? ""); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:5px; font-size: 0.85rem;">Password</label>
                        <input type="password" name="commentics_db_pass" value="<?php echo htmlspecialchars($config["commentics_db_pass"] ?? ""); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <h3>🌐 Site Configuration</h3>
            <label style="display:block; margin-bottom:5px; font-weight:bold;">Site Name</label>
            <input type="text" name="site_name" value="<?php echo htmlspecialchars($config['site_name']); ?>" required style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:4px;">

            <label style="display:block; margin-bottom:5px; font-weight:bold;">Custom Footer Text (Copyright, etc)</label>
            <textarea name="footer_text" style="width:100%; height:60px; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:4px;"><?php echo htmlspecialchars($config['footer_text'] ?? ''); ?></textarea>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:15px;">
                <div>
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Posts Per Page</label>
                    <input type="number" name="posts_per_page" value="<?php echo $config['posts_per_page']; ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Sidebar Position</label>
                    <select name="sidebar_position" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                        <option value="left" <?php echo ($config['sidebar_position']??'')==='left'?'selected':''; ?>>Left</option>
                        <option value="right" <?php echo ($config['sidebar_position']??'')==='right'?'selected':''; ?>>Right</option>
                    </select>
                </div>
            </div>

            <div style="margin-top:20px;">
                <label style="display:block; margin-bottom:10px; font-weight:bold; cursor:pointer;">
                    <input type="checkbox" name="comments_enabled" <?php echo ($config['comments_enabled']??false)?'checked':''; ?>> Enable built-in comments
                </label>
                <div class="form-group" style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
                    <label>Recent Comments Widget Settings</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 10px;">
                        <div>
                            <label style="font-size: 0.85rem; font-weight: normal;">Widget Title</label>
                            <input type="text" name="recent_comments_title" value="<?php echo htmlspecialchars($config['recent_comments_title'] ?? 'Recent Comments'); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                        </div>
                        <div>
                            <label style="font-size: 0.85rem; font-weight: normal;">Limit (How many to show)</label>
                            <input type="number" name="recent_comments_limit" value="<?php echo htmlspecialchars($config['recent_comments_limit'] ?? 3); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                        </div>
                        <div>
                            <label style="font-size: 0.85rem; font-weight: normal;">Avatar Size (px)</label>
                            <input type="number" name="comment_avatar_size" value="<?php echo htmlspecialchars($config['comment_avatar_size'] ?? 40); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                        </div>
                        <div>
                            <label style="font-size: 0.85rem; font-weight: normal;">Excerpt Length (chars)</label>
                            <input type="number" name="comment_excerpt_length" value="<?php echo htmlspecialchars($config['comment_excerpt_length'] ?? 50); ?>" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                        </div>
                    </div>
                    <div style="margin-top: 15px; display: flex; gap: 20px;">
                        <label style="font-weight: 400; font-size: 0.85rem; cursor: pointer;">
                            <input type="checkbox" name="show_comment_avatar" <?php echo ($config['show_comment_avatar'] ?? true) ? 'checked' : ''; ?>> Show Avatars
                        </label>
                        <label style="font-weight: 400; font-size: 0.85rem; cursor: pointer;">
                            <input type="checkbox" name="show_comment_excerpt" <?php echo ($config['show_comment_excerpt'] ?? true) ? 'checked' : ''; ?>> Show Comment Excerpt
                        </label>
                    </div>
                </div>
                <label style="display:block; margin-bottom:10px; font-weight:bold; cursor:pointer;">
                    <input type="checkbox" name="show_author_bio" <?php echo ($config['show_author_bio'] ?? true) ? 'checked' : ''; ?>> Show Author Bio box on single post pages
                </label>
            </div>

            <label style="display:block; margin-top:20px; margin-bottom:5px; font-weight:bold;">Disqus Shortname (Optional)</label>
            <input type="text" name="disqus_shortname" value="<?php echo htmlspecialchars($config['disqus_shortname'] ?? ''); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;" placeholder="Leave blank to use native comments">
        </div>

        <div class="card" style="border-top: 5px solid #8b5cf6;">
            <h3>🔝 Back to Top Button</h3>
            <div style="margin-bottom: 15px;">
                <label style="display: flex; align-items: center; cursor: pointer; font-weight:bold;">
                    <input type="checkbox" name="back_to_top_enabled" <?php echo ($config['back_to_top_enabled'] ?? false) ? 'checked' : ''; ?> style="margin-right: 10px;">
                    Enable "Back to Top" button
                </label>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Type</label>
                    <select name="back_to_top_type" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                        <option value="icon" <?php echo ($config['back_to_top_type'] ?? 'icon') === 'icon' ? 'selected' : ''; ?>>Icon Only</option>
                        <option value="text" <?php echo ($config['back_to_top_type'] ?? 'icon') === 'text' ? 'selected' : ''; ?>>Text Only</option>
                        <option value="both" <?php echo ($config['back_to_top_type'] ?? 'icon') === 'both' ? 'selected' : ''; ?>>Both</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Button Color</label>
                    <input type="color" name="back_to_top_color" value="<?php echo htmlspecialchars($config['back_to_top_color'] ?? '#8b5cf6'); ?>" style="width:100%; height:40px; padding:2px; border:1px solid #ddd; border-radius:4px;">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                <div>
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Link Text (if applicable)</label>
                    <input type="text" name="back_to_top_text" value="<?php echo htmlspecialchars($config['back_to_top_text'] ?? 'Top'); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Button Size (px)</label>
                    <input type="number" name="back_to_top_size" value="<?php echo htmlspecialchars($config['back_to_top_size'] ?? 40); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                </div>
            </div>
        </div>

        <div style="margin-top: 20px; display: flex; gap: 10px;">
            <button type="submit" style="flex: 1; padding: 15px; font-weight: bold; background:#8b5cf6; color:#fff; border:none; border-radius:6px; cursor:pointer;">💾 Save All Site Settings</button>
            <button type="submit" name="action" value="reset_settings" style="background: #6c757d; color: #fff; padding: 15px; font-weight: bold; border:none; border-radius:6px; cursor:pointer;" onclick="return confirm('Reset all site settings (not content) to defaults?')">🔄 Reset to Defaults</button>
        </div>
    </form>

    <div class="card" style="margin-top: 30px; border-top: 4px solid #ffc107;">
        <h3>✨ Demo Content</h3>
        <p>New to the CMS? You can import demo content (posts, pages, and sample settings) to see how everything looks.</p>
        <div class="warning" style="background: #fff3cd; padding: 15px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #ffeeba; color:#856404;">
            <strong>Note:</strong> This will NOT delete your existing content, but it will overwrite site settings with demo defaults.
        </div>
        <form method="POST" onsubmit="return confirm('Import demo content? This will update your site settings.')">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <input type="hidden" name="action" value="import_demo">
            <button type="submit" style="background: #ffc107; color: #000; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-weight: bold;">Import Demo Content</button>
        </form>
    </div>
</div>
</body>
</html>

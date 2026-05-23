<?php
require_once __DIR__ . '/../../../app/auth.php';
require_once __DIR__ . '/../../../app/functions.php';

require_login('plugins');

$config = load_config();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sitemap Settings - Swiffy Blog</title>
    <link rel="stylesheet" href="../../../admin/style.css">
    <style>
        .main-content { margin-left: 310px; margin-top: 70px; padding: 2rem; }
        .card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
    </style>
</head>
<body>
<?php include "../../../admin/sidebar.php"; ?>
<div class="main-content">
    <h1>🧩 Swiffy Sitemap Settings</h1>

    <div class="card">
        <p>Your sitemap is automatically generated and available at the following URL:</p>
        <div style="background: #f7fafc; padding: 1rem; border-radius: 6px; margin: 1rem 0; border: 1px solid #e2e8f0; font-family: monospace;">
            index.php?sitemap=1
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="../../../index.php?sitemap=1" target="_blank" class="btn btn-primary" style="background:#8b5cf6; color:#fff; padding:10px 20px; text-decoration:none; border-radius:4px; font-weight:bold;">🌐 View Live Sitemap</a>
            <a href="../../../admin/plugins.php" class="btn" style="background:#6c757d; color:#fff; padding:10px 20px; text-decoration:none; border-radius:4px; font-weight:bold;">&larr; Back to Plugins</a>
        </div>
    </div>

    <div class="card" style="border-top: 4px solid #8b5cf6;">
        <h3>ℹ️ Information</h3>
        <p>This plugin dynamically creates a sitemap including all your published posts. You can submit the URL above to search engines like Google and Bing to help them index your blog faster.</p>
    </div>
</div>
</body>
</html>

            <?php
            $widgets = $config['widget_areas'] ?? [];
            if (!empty($widgets['lower'])): ?>
                <div class="widgets-container lower-widgets">
                    <?php foreach ($widgets['lower'] as $w):
                        if (file_exists(__DIR__ . "/widget-{$w}.php")) include __DIR__ . "/widget-{$w}.php";
                    endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo !empty($config['footer_text']) ? nl2br(htmlspecialchars($config['footer_text'])) : '<?php echo htmlspecialchars($config['site_name']); ?>copy; ' . date('Y') . ' ' . htmlspecialchars($config['site_name']); ?></p>
        </div>
    </footer>

    <script>
        // Dark Mode Toggle
        const toggle = document.getElementById('theme-toggle');
        toggle.addEventListener('click', () => {
            const body = document.body;
            const current = body.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            body.setAttribute('data-theme', next);
            document.documentElement.setAttribute('data-theme', next);

            // Save to cookie for PHP persistence
            document.cookie = "dark_mode=" + (next === 'dark' ? '1' : '0') + ";path=/;max-age=31536000";
        });
    </script>
    <?php
    $assets = get_plugin_assets();
    foreach ($assets['js'] as $js) echo '<script src="'.$js.'"></script>';
    ?>
</body>
</html>

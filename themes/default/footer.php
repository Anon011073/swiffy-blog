            </main>
            <aside class="site-sidebar">
                <?php
                $sidebar_widgets = $config['widget_areas']['sidebar'] ?? ['search', 'recent_posts'];
                foreach ($sidebar_widgets as $w) $include_part('widget-' . $w);
                ?>
            </aside>
        </div>
    </div>
    <footer class="site-footer">
        <div class="container">
            <?php
            $f1 = $config['widget_areas']['footer1'] ?? [];
            $f2 = $config['widget_areas']['footer2'] ?? [];
            $f3 = $config['widget_areas']['footer3'] ?? [];
            if (!empty($f1) || !empty($f2) || !empty($f3)):
            ?>
                <div class="footer-widgets">
                    <div class="footer-widget">
                        <?php foreach ($f1 as $w) $include_part('widget-' . $w); ?>
                    </div>
                    <div class="footer-widget">
                        <?php foreach ($f2 as $w) $include_part('widget-' . $w); ?>
                    </div>
                    <div class="footer-widget">
                        <?php foreach ($f3 as $w) $include_part('widget-' . $w); ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="footer-bottom">
                <a href="#" id="back-to-top">Back to top ↑</a><br>
                <?php echo !empty($config['footer_text']) ? nl2br(htmlspecialchars($config['footer_text'])) : "&copy; " . date('Y') . " " . htmlspecialchars($config['site_name']) . "."; ?>
            </div>
        </div>
    </footer>
    <script>
        const toggle = document.getElementById('theme-toggle');
        if (toggle) {
            toggle.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                const theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                localStorage.setItem('theme', theme);
            });
        }

        const btt = document.getElementById('back-to-top');
        if (btt) {
            btt.addEventListener('click', (e) => {
                e.preventDefault();
                window.scrollTo({top: 0, behavior: 'smooth'});
            });
        }
    </script>
    <?php
    $theme_assets = get_plugin_assets();
    foreach ($theme_assets['js'] as $js) echo '<script src="'.$js.'"></script>';
    ?>
</body>
</html>

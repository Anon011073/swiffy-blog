    </main>

    <?php if ($config['back_to_top_enabled'] ?? false):
        $btt_type = $config['back_to_top_type'] ?? 'icon';
        $btt_text = $config['back_to_top_text'] ?? 'Top';
        $btt_color = $config['back_to_top_color'] ?? '#8b5cf6';
        $btt_size = (int)($config['back_to_top_size'] ?? 56);
    ?>
    <a href="#" class="sfx-scroll-up" id="sfxScrollBtn" aria-label="Back to Top" style="--sfx-bg: <?php echo !empty($btt_color) ? $btt_color : "#8b5cf6"; ?>; --sfx-sz: <?php echo $btt_size; ?>px;">
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; height: 100%;">
            <?php if ($btt_type === 'icon' || $btt_type === 'both'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
            <?php endif; ?>
            <?php if ($btt_type === 'text' || $btt_type === 'both'): ?>
                <span class="btt-text"><?php echo htmlspecialchars($btt_text); ?></span>
            <?php endif; ?>
        </div>
    </a>
    <?php endif; ?>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-inner">
                <div class="footer-widgets-grid">
                    <?php
                    $widget_areas = ['footer1', 'footer2', 'footer3'];
                    foreach ($widget_areas as $area):
                        if (!empty($config['widget_areas'][$area])): ?>
                            <div class="footer-column">
                                <?php
                                    foreach ($config['widget_areas'][$area] as $widget_type) {
                                        $include_part('widget-' . $widget_type);
                                    }
                                ?>
                            </div>
                        <?php endif;
                    endforeach; ?>
                </div>

                <div class="footer-bottom">
                    <div class="footer-info">
                        <?php if (!empty($config['footer_text'])): ?>
                            <?php echo nl2br(htmlspecialchars($config['footer_text'])); ?>
                        <?php else: ?>
                            &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($config['site_name']); ?>. Inspired by Zipply.
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        (function() {
            const backToTop = document.getElementById('sfxScrollBtn');
            if (backToTop) {
                const handleScroll = () => {
                    const scrolled = window.scrollY || window.pageYOffset || document.documentElement.scrollTop;
                    if (scrolled > 150) {
                        backToTop.classList.add('sfx-visible');
                    } else {
                        backToTop.classList.remove('sfx-visible');
                    }
                };
                window.addEventListener('scroll', handleScroll, { passive: true });
                handleScroll();
                backToTop.addEventListener('click', (e) => {
                    e.preventDefault();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }
        })();
    </script>

    <?php if ($config['theme_options']['enable_comet_cursor'] ?? true): ?>
    <canvas id="cometCanvas" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: -1;"></canvas>
    <script>
        (function() {
            const canvas = document.getElementById('cometCanvas');
            const ctx = canvas.getContext('2d');
            let width, height;
            const mouse = { x: window.innerWidth/2, y: window.innerHeight/2, active: false };
            const dots = [];
            const dotCount = 20;

            function resize() {
                width = canvas.width = window.innerWidth;
                height = canvas.height = window.innerHeight;
            }

            window.addEventListener('resize', resize);
            window.addEventListener('mousemove', e => {
                mouse.x = e.clientX;
                mouse.y = e.clientY;
                mouse.active = true;
            });

            resize();
            for (let i = 0; i < dotCount; i++) dots.push({ x: mouse.x, y: mouse.y });

            function draw() {
                ctx.clearRect(0, 0, width, height);
                if (!mouse.active) { requestAnimationFrame(draw); return; }

                let x = mouse.x;
                let y = mouse.y;

                ctx.globalCompositeOperation = 'screen';

                dots.forEach((dot, index) => {
                    dot.x += (x - dot.x) * 0.2;
                    dot.y += (y - dot.y) * 0.2;

                    const ratio = 1 - index / dotCount;
                    const size = 180 * ratio;
                    const opacity = 0.07 * ratio;

                    const gradient = ctx.createRadialGradient(dot.x, dot.y, 0, dot.x, dot.y, size);
                    gradient.addColorStop(0, `rgba(255, 255, 255, ${opacity})`);
                    gradient.addColorStop(0.6, `rgba(255, 255, 255, ${opacity * 0.3})`);
                    gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');

                    ctx.beginPath();
                    ctx.arc(dot.x, dot.y, size, 0, Math.PI * 2);
                    ctx.fillStyle = gradient;
                    ctx.fill();

                    x = dot.x;
                    y = dot.y;
                });

                ctx.globalCompositeOperation = 'source-over';
                requestAnimationFrame(draw);
            }
            draw();
        })();
    </script>
    <?php endif; ?>

    <?php
    $theme_assets = get_plugin_assets();
    if (isset($theme_assets['js'])) {
        foreach ($theme_assets['js'] as $js) echo '<script src="'.$js.'"></script>';
    }
    ?>
    <?php
    foreach (get_enabled_plugins_data() as $p) {
        if (isset($p['hooks']['system_footer'])) echo $p['hooks']['system_footer']();
    }
    ?>
</body>
</html>

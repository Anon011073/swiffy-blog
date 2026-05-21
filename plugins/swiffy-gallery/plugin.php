<?php
/**
 * Image swiffy-gallery Plugin for AnonBlog
 */

$config = load_config();
$swiffy_gallery_options = $config['swiffy_gallery_options'] ?? [
    'layout' => 'grid',
    'columns' => 3,
    'gap' => 15,
    'border_radius' => 8,
    'click_to_zoom' => true
];

return [
    'name' => 'gallery',
    'description' => 'Add image galleries to your posts using [swiffy-gallery images="img1,img2"] shortcode.',
    'author' => 'AnonBlog Team',
    'version' => '1.3.0',
    'settings_url' => '../plugins/swiffy-gallery/admin/settings.php',
    'hooks' => [
        'render_content' => function($content) use ($swiffy_gallery_options) {
            return preg_replace_callback('/\[swiffy-gallery images="(.*?)"\]/', function($matches) use ($swiffy_gallery_options) {
                $images = array_filter(array_map('trim', explode(',', $matches[1])));
                if (empty($images)) return '';

                $layout = $swiffy_gallery_options['layout'] ?? 'grid';
                $gap = $swiffy_gallery_options['gap'];
                $radius = $swiffy_gallery_options['border_radius'];
                $id = 'anon-gallery-' . uniqid();
                
                $html = '';

                if ($layout === 'slider') {
                    $html .= '
                    <div id="'.$id.'" class="anon-gallery-slider" style="position: relative; overflow: hidden; margin: 25px 0; border-radius: '.$radius.'px; background: rgba(0,0,0,0.05);">
                        <div class="slider-track" style="display: flex; transition: transform 0.5s ease-in-out;">';
                    foreach ($images as $img) {
                        $full = "uploads/" . htmlspecialchars($img);
                        $html .= '<div class="slider-slide" style="min-width: 100%; aspect-ratio: 16/9; display: flex; align-items: center; justify-content: center;">
                                    <img src="'.$full.'" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                  </div>';
                    }
                    $html .= '</div>
                        <button onclick="anonSliderPrev(\''.$id.'\')" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: #fff; border: none; padding: 15px 10px; cursor: pointer; border-radius: 4px; z-index: 10;">&#10094;</button>
                        <button onclick="anonSliderNext(\''.$id.'\')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: #fff; border: none; padding: 15px 10px; cursor: pointer; border-radius: 4px; z-index: 10;">&#10095;</button>
                    </div>
                    <script>
                    if (typeof anonSliderNext !== "function") {
                        function anonSliderNext(id) {
                            const track = document.querySelector("#" + id + " .slider-track");
                            const slides = track.children.length;
                            let current = parseInt(track.dataset.current || 0);
                            current = (current + 1) % slides;
                            track.style.transform = `translateX(-${current * 100}%)`;
                            track.dataset.current = current;
                        }
                        function anonSliderPrev(id) {
                            const track = document.querySelector("#" + id + " .slider-track");
                            const slides = track.children.length;
                            let current = parseInt(track.dataset.current || 0);
                            current = (current - 1 + slides) % slides;
                            track.style.transform = `translateX(-${current * 100}%)`;
                            track.dataset.current = current;
                        }
                    }
                    </script>';
                } else {
                    // Grid Layout
                    $cols = $swiffy_gallery_options['columns'];
                    $html .= '<div class="anon-gallery-grid" style="display: grid; grid-template-columns: repeat('.$cols.', 1fr); gap: '.$gap.'px; margin: 25px 0;">';
                    foreach ($images as $img) {
                        $thumb = "plugins/swiffy-gallery/thumb.php?src=" . urlencode($img) . "&w=400&h=400";
                        $full = "uploads/" . htmlspecialchars($img);
                        $html .= '<div class="gallery-item" style="overflow: hidden; border-radius: '.$radius.'px; border: 1px solid rgba(128,128,128,0.2); line-height: 0;">';
                        if ($swiffy_gallery_options['click_to_zoom'] ?? true) {
                            $html .= '<a href="'.$full.'" target="_blank">';
                        }
                        $html .= '<img src="'.$thumb.'" style="width: 100%; aspect-ratio: 1/1; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">';
                        if ($swiffy_gallery_options['click_to_zoom'] ?? true) {
                            $html .= '</a>';
                        }
                        $html .= '</div>';
                    }
                    $html .= '</div>';
                    $html .= '<style>@media (max-width: 768px) { .anon-gallery-grid { grid-template-columns: repeat(2, 1fr) !important; } } @media (max-width: 480px) { .anon-gallery-grid { grid-template-columns: 1fr !important; } }</style>';
                }
                
                return $html;
            }, $content);
        }
    ]
];

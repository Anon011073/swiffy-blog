<?php
/**
 * Like/Dislike Plugin for Swiffy Blog
 */

$config = load_config();
$likes_options = $config['likes_options'] ?? ['icon_set' => 'thumbs'];
$set = $likes_options['icon_set'] ?? 'thumbs';

$icons = [
    'thumbs' => ['pos' => '👍', 'neg' => '👎'],
    'hearts' => ['pos' => '❤️', 'neg' => '💔'],
    'stars'  => ['pos' => '⭐', 'neg' => '💀']
];
$pos_icon = $icons[$set]['pos'] ?? '👍';
$neg_icon = $icons[$set]['neg'] ?? '👎';

return [
    'name' => 'Swiffy Likes & Dislikes',
    'description' => 'Add simple like and dislike buttons to your posts.',
    'author' => 'Swiffy Blog People',
    'version' => '1.3.3',
    'settings_url' => '../plugins/swiffy-likes/admin/settings.php',
    'hooks' => [
        'render_content' => function($content) use ($pos_icon, $neg_icon) {
            if (isset($_GET['post'])) {
                $slug = $_GET['post'];
                
                $file = __DIR__ . '/../../config/likes.json';
                $data = [];
                if (file_exists($file)) {
                    $data = json_decode(file_get_contents($file), true) ?: [];
                }
                $counts = $data[$slug] ?? ['likes' => 0, 'dislikes' => 0];
                
                $root = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                $base = str_replace(['index.php', 'admin/'], '', $_SERVER['SCRIPT_NAME']);
                $ajax_url = $root . $base . "plugins/swiffy-likes/ajax.php";

                $html = '
                <div class="swiffy-likes" id="swiffy-likes-'.htmlspecialchars($slug).'" style="margin-top: 35px; padding: 25px 0; border-top: 2px solid rgba(128,128,128,0.15); display: flex; gap: 20px; align-items: center; clear: both;">
                    <button type="button" class="like-btn" onclick="swiffyLike(\''.$slug.'\', \'like\')" style="background: rgba(128,128,128,0.1); border: 1px solid rgba(128,128,128,0.2); padding: 10px 22px; cursor: pointer; border-radius: 10px; color: inherit; font-size: 1.3rem; display: flex; align-items: center; gap: 12px; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); outline: none;">
                        <span>'.$pos_icon.'</span> <span class="like-count" style="font-weight: bold; color: inherit;">'.$counts['likes'].'</span>
                    </button>
                    <button type="button" class="dislike-btn" onclick="swiffyLike(\''.$slug.'\', \'dislike\')" style="background: rgba(128,128,128,0.1); border: 1px solid rgba(128,128,128,0.2); padding: 10px 22px; cursor: pointer; border-radius: 10px; color: inherit; font-size: 1.3rem; display: flex; align-items: center; gap: 12px; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); outline: none;">
                        <span>'.$neg_icon.'</span> <span class="dislike-count" style="font-weight: bold; color: inherit;">'.$counts['dislikes'].'</span>
                    </button>
                </div>
                <script>
                if (typeof swiffyLike !== "function") {
                    function swiffyLike(slug, type) {
                        const container = document.getElementById("swiffy-likes-" + slug);
                        const btn = type === "like" ? container.querySelector(".like-btn") : container.querySelector(".dislike-btn");
                        
                        btn.style.transform = "scale(0.85)";
                        
                        const url = "'.$ajax_url.'?slug=" + encodeURIComponent(slug) + "&type=" + type;
                        fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            btn.style.transform = "scale(1.15)";
                            setTimeout(() => btn.style.transform = "scale(1)", 200);

                            if(data.success) {
                                container.querySelector(".like-count").innerText = data.likes;
                                container.querySelector(".dislike-count").innerText = data.dislikes;
                                
                                if(data.message === "Already voted") {
                                    alert("You have already voted on this post.");
                                } else {
                                    btn.style.borderColor = "#007bff";
                                    btn.style.background = "rgba(0,123,255,0.15)";
                                }
                            }
                        }).catch(err => {
                            console.error("Like error:", err);
                            btn.style.transform = "scale(1)";
                        });
                    }
                }
                </script>
                ';
                return $content . $html;
            }
            return $content;
        }
    ]
];

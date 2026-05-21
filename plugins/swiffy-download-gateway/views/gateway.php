<?php
$unlock_mode = $config['sfx_dl_unlock_mode'] ?? 'none';
$post_slug = $_GET['from'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Download - <?php echo htmlspecialchars($file_info['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inconsolata:wght@400;700&family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0b0f1a;
            --accent-purple: #8b5cf6;
            --text-main: #e2e8f0;
            --text-muted: #94a3b8;
            --glass-bg: rgba(20, 26, 45, 0.7);
            --border-color: rgba(255, 255, 255, 0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        .gateway-container {
            width: 100%;
            max-width: 650px;
            padding: 20px;
            position: relative;
            z-index: 10;
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border-color);
            border-radius: 32px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.6);
            position: relative;
            overflow: hidden;
        }

        .glow-effect {
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at center, rgba(139, 92, 246, 0.08) 0%, transparent 50%);
            pointer-events: none;
        }

        .file-icon {
            font-size: 5rem;
            margin-bottom: 25px;
            display: inline-block;
            filter: drop-shadow(0 0 20px rgba(139, 92, 246, 0.4));
        }

        h1 {
            font-family: 'Inconsolata', monospace;
            font-size: 2.2rem;
            margin-bottom: 12px;
            letter-spacing: -0.03em;
            font-weight: 700;
        }

        .file-meta {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 40px;
            display: flex;
            justify-content: center;
            gap: 24px;
            font-family: 'Inconsolata', monospace;
        }

        .unlock-area {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }

        .unlock-title {
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--accent-purple);
        }

        .timer-section {
            margin: 30px 0;
        }

        .timer-circle {
            width: 100px;
            height: 100px;
            border: 4px solid var(--border-color);
            border-top-color: var(--accent-purple);
            border-radius: 50%;
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inconsolata', monospace;
            font-weight: 700;
            font-size: 2rem;
            animation: spin 1.2s linear infinite;
        }

        .timer-circle span {
            animation: unspin 1.2s linear infinite;
        }

        @keyframes spin { 100% { transform: rotate(360deg); } }
        @keyframes unspin { 100% { transform: rotate(-360deg); } }

        .status-msg {
            font-family: 'Inconsolata', monospace;
            font-size: 1.2rem;
            color: var(--accent-purple);
            margin-bottom: 15px;
            min-height: 1.5em;
        }

        .dl-btn {
            display: none;
            background: var(--accent-purple);
            color: white;
            text-decoration: none;
            padding: 22px 44px;
            border-radius: 16px;
            font-weight: 800;
            font-size: 1.2rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            cursor: pointer;
            width: 100%;
            box-shadow: 0 20px 40px -10px rgba(139, 92, 246, 0.5);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .dl-btn:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 25px 50px -10px rgba(139, 92, 246, 0.6);
            filter: brightness(1.15);
        }

        .ad-area {
            margin-top: 50px;
            padding: 24px;
            border: 1px dashed var(--border-color);
            border-radius: 16px;
            color: var(--text-muted);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            background: rgba(255,255,255,0.01);
        }

        .particles { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; pointer-events: none; }

        /* Progress bar */
        .progress-container { width: 100%; height: 4px; background: var(--border-color); border-radius: 2px; margin-top: 20px; overflow: hidden; }
        .progress-bar { height: 100%; background: var(--accent-purple); width: 0%; transition: width 0.3s linear; box-shadow: 0 0 10px var(--accent-purple); }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>

    <div class="gateway-container">
        <div class="glass-card">
            <div class="glow-effect"></div>

            <div class="file-icon">
                <?php
                $ext = strtolower($file_info['ext']);
                if (in_array($ext, ['zip', 'rar', '7z'])) echo '📦';
                elseif (in_array($ext, ['mp3', 'wav'])) echo '🎵';
                elseif (in_array($ext, ['mp4', 'mkv'])) echo '🎬';
                elseif ($ext === 'pdf') echo '📄';
                else echo '💾';
                ?>
            </div>

            <h1><?php echo htmlspecialchars($file_info['name']); ?></h1>
            <div class="file-meta">
                <span>SIZE: <?php echo $file_info['size']; ?></span>
                <span>TYPE: <?php echo strtoupper($file_info['ext']); ?></span>
            </div>

            <?php if ($unlock_mode !== 'none'): ?>
                <div class="unlock-area" id="unlockArea">
                    <div class="unlock-title">Action Required</div>
                    <p style="color: var(--text-muted); margin-bottom: 20px;">
                        <?php
                        if ($unlock_mode === 'like') echo 'Please like the post to unlock this download.';
                        elseif ($unlock_mode === 'comment') echo 'Please leave a comment on the post to unlock this download.';
                        ?>
                    </p>
                    <button class="dl-btn" style="display: block; opacity: 0.5;" onclick="checkUnlock()">Check Unlock Status</button>
                </div>
            <?php endif; ?>

            <div class="timer-section" id="timerSection" style="<?php echo $unlock_mode !== 'none' ? 'display:none;' : ''; ?>">
                <div class="timer-circle">
                    <span id="countdown"><?php echo $timer; ?></span>
                </div>
                <div class="status-msg" id="statusMsg">Preparing secure link...</div>
                <div class="progress-container">
                    <div class="progress-bar" id="progressBar"></div>
                </div>
            </div>

            <a href="index.php?sfx-dl=<?php echo $file_info['token']; ?>" class="dl-btn" id="dlBtn">
                Start Download
            </a>

            <div class="ad-area">
                Advertisement Slot
            </div>
        </div>
    </div>

    <script>
        let timeLeft = <?php echo $timer; ?>;
        const totalTime = <?php echo $timer; ?>;
        const countdownEl = document.getElementById('countdown');
        const statusMsg = document.getElementById('statusMsg');
        const dlBtn = document.getElementById('dlBtn');
        const timerSection = document.getElementById('timerSection');
        const progressBar = document.getElementById('progressBar');
        const unlockArea = document.getElementById('unlockArea');

        const statusSteps = [
            "Initializing secure handshake...",
            "Authenticating request session...",
            "Verifying file integrity...",
            "Generating encrypted stream...",
            "Link verified. Secure ready."
        ];

        function startCountdown() {
            if (timerSection) timerSection.style.display = 'block';
            if (unlockArea) unlockArea.style.display = 'none';

            const timerInterval = setInterval(() => {
                timeLeft--;
                if (countdownEl) countdownEl.innerText = timeLeft;

                const progress = ((totalTime - timeLeft) / totalTime) * 100;
                if (progressBar) progressBar.style.width = `${progress}%`;

                const stepIdx = Math.min(Math.floor((progress/100) * statusSteps.length), statusSteps.length - 1);
                if (statusMsg) statusMsg.innerText = statusSteps[stepIdx];

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    if (timerSection) timerSection.style.display = 'none';
                    if (dlBtn) dlBtn.style.display = 'block';
                }
            }, 1000);
        }

        function checkUnlock() {
            // In this demo, we just simulate an unlock
            // Real logic would be an fetch call to check like/comment status
            alert("Checking... System detected unlock action!");
            startCountdown();
        }

        <?php if ($unlock_mode === 'none'): ?>
            startCountdown();
        <?php endif; ?>

        // Simple particle effect
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        document.getElementById('particles').appendChild(canvas);

        function resize() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        window.onresize = resize;
        resize();

        const particles = [];
        for(let i=0; i<60; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                vx: (Math.random() - 0.5) * 0.4,
                vy: (Math.random() - 0.5) * 0.4,
                sz: Math.random() * 2
            });
        }

        function anim() {
            ctx.clearRect(0,0,canvas.width, canvas.height);
            ctx.fillStyle = 'rgba(139, 92, 246, 0.3)';
            particles.forEach(p => {
                p.x += p.vx;
                p.y += p.vy;
                if(p.x < 0) p.x = canvas.width;
                if(p.x > canvas.width) p.x = 0;
                if(p.y < 0) p.y = canvas.height;
                if(p.y > canvas.height) p.y = 0;
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.sz, 0, Math.PI*2);
                ctx.fill();
            });
            requestAnimationFrame(anim);
        }
        anim();
    </script>
</body>
</html>

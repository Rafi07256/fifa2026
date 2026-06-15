<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://quge5.com/88/tag.min.js" data-zone="24891" async data-cfasync="false"></script>
    <title>BDIX Live TV Player</title>
    
    <!-- =======================================================
         MONETAG ADS INTEGRATION (0% PLAYER EFFECT)
         নিচের লাইনে আপনার Monetag থেকে পাওয়া বিজ্ঞাপন কোডটি বসিয়ে দিন।
         ======================================================= -->
    <script src="https://alwingulla.com/act/files/tag.min.js" data-zone="YOUR_MONETAG_ZONE_ID" async></script>
    <!-- ======================================================= -->

    <!-- HLS.js Library (For Live Stream Support) -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <!-- FontAwesome for UI Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-color: #0f172a; /* ডিফল্ট ব্যাকগ্রাউন্ড */
            --accent-color: #3b82f6; 
            --player-shadow: rgba(0, 0, 0, 0.5);
        }

        /* থিম অনুযায়ী ব্যাকগ্রাউন্ড এবং অ্যাকসেন্ট কালার পরিবর্তন */
        .theme-grey { 
            --bg-color: #374151; /* গ্রে ব্যাকগ্রাউন্ড */
            --accent-color: #9ca3af; 
        }
        .theme-blue { 
            --bg-color: #0f172a; /* ব্লু ব্যাকগ্রাউন্ড */
            --accent-color: #3b82f6; 
        }
        .theme-dark { 
            --bg-color: #000000; /* পিওর ব্ল্যাক ব্যাকগ্রাউন্ড */
            --accent-color: #ef4444; 
        }
        .theme-navy { 
            --bg-color: #0a1128; /* ডিপ নেভি ব্যাকগ্রাউন্ড */
            --accent-color: #00e5ff; 
        }
        .theme-white { 
            --bg-color: #ffffff; /* পিওর হোয়াইট ব্যাকগ্রাউন্ড */
            --accent-color: #3b82f6; 
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--bg-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
            transition: background 0.3s ease; /* ব্যাকগ্রাউন্ড পরিবর্তন স্মুথ করার জন্য */
        }

        /* Floating Theme Switcher Sidebar (Top Right) */
        .theme-panel {
            position: fixed;
            top: 25px;
            right: 25px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 30px;
            padding: 12px 10px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            z-index: 100;
        }

        .theme-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: transform 0.2s, border-color 0.2s;
        }

        .theme-dot:hover {
            transform: scale(1.25);
        }

        .theme-dot.active {
            border-color: #ffffff;
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.6);
        }

        /* Video Player Wrapper */
        .player-wrapper {
            position: relative;
            width: 85%;
            max-width: 900px;
            aspect-ratio: 16/9;
            background: #000000;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 50px var(--player-shadow);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        /* Loading Spinner */
        .spinner-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 20;
        }

        .spinner {
            width: 55px;
            height: 55px;
            border: 5px solid rgba(255, 255, 255, 0.1);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 0.8s infinite linear;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Custom Bottom Control Bar */
        .player-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.4) 70%, transparent 100%);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            opacity: 1;
            transition: opacity 0.3s ease;
            z-index: 30;
        }

        .left-controls, .right-controls {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .control-btn {
            background: none;
            border: none;
            color: #ffffff;
            font-size: 18px;
            cursor: pointer;
            outline: none;
            transition: color 0.2s, transform 0.1s;
            padding: 5px;
        }

        .control-btn:hover {
            color: var(--accent-color);
            transform: scale(1.1);
        }

        /* Clickable Live Sync Button Badge */
        .live-btn {
            background-color: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            padding: 5px 12px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 7px;
            text-transform: uppercase;
            cursor: pointer;
            user-select: none;
            transition: background 0.2s, transform 0.1s;
        }

        .live-btn:hover {
            background-color: rgba(239, 68, 68, 0.4);
            transform: scale(1.05);
        }

        .live-btn::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: #ef4444;
            border-radius: 50%;
            box-shadow: 0 0 8px #ef4444;
        }

        /* কন্ট্রোল বার হাইড ইফেক্ট */
        .player-wrapper.hide-controls .player-controls {
            opacity: 0;
            pointer-events: none;
        }
    </style>
</head>
<body class="theme-blue">

    <!-- Floating Theme Switcher Sidebar (Top Right) -->
    <div class="theme-panel">
        <div class="theme-dot active" style="background: #3b82f6;" onclick="changeTheme('theme-blue', this)"></div>
        <div class="theme-dot" style="background: #ffffff; border: 1px solid #ddd;" onclick="changeTheme('theme-white', this)"></div>
        <div class="theme-dot" style="background: #6b7280;" onclick="changeTheme('theme-grey', this)"></div>
        <div class="theme-dot" style="background: #111827;" onclick="changeTheme('theme-dark', this)"></div>
        <div class="theme-dot" style="background: #1e3a8a;" onclick="changeTheme('theme-navy', this)"></div>
    </div>

    <!-- Video Player Wrapper -->
    <div class="player-wrapper" id="playerContainer">
        <video id="video" autoplay muted playsinline></video>
        
        <!-- Loading Indicator -->
        <div class="spinner-container" id="loadingSpinner">
            <div class="spinner"></div>
        </div>

        <!-- Custom Controls Overlay -->
        <div class="player-controls">
            <div class="left-controls">
                <button class="control-btn" id="playBtn" onclick="togglePlay()"><i class="fas fa-pause"></i></button>
                <button class="live-btn" onclick="syncToLiveEdge()" title="Click to sync to live moment">Live</button>
            </div>
            <div class="right-controls">
                <button class="control-btn" id="volumeBtn" onclick="toggleMute()"><i class="fas fa-volume-mute"></i></button>
                <button class="control-btn" onclick="toggleFullscreen()"><i class="fas fa-expand"></i></button>
            </div>
        </div>
    </div>

    <script>
        const video = document.getElementById('video');
        const spinner = document.getElementById('loadingSpinner');
        const playBtn = document.getElementById('playBtn');
        const volumeBtn = document.getElementById('volumeBtn');
        const playerContainer = document.getElementById('playerContainer');
        let hlsInstance = null;

        // Theme changer logic
        function changeTheme(themeName, element) {
            document.body.className = themeName;
            document.querySelectorAll('.theme-dot').forEach(dot => dot.classList.remove('active'));
            element.classList.add('active');
        }

        // URL থেকে ID প্যারামিটার নেওয়ার ফাংশন
        function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            var results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }

        // Fetch streaming URL dynamically from Backend
        async function loadStream() {
            try {
                spinner.style.display = 'flex';
                const streamId = getUrlParameter('id');
                const response = await fetch('get_stream.php?id=' + streamId);
                const data = await response.json();
                
                if (data.url) {
                    initializePlayer(data.url);
                } else {
                    console.error("Stream URL not found in response.");
                    spinner.style.display = 'none';
                }
            } catch (error) {
                console.error("Error fetching stream token:", error);
                spinner.style.display = 'none';
            }
        }

        // Initialize Player using HLS.js
        function initializePlayer(streamUrl) {
            if (Hls.isSupported()) {
                hlsInstance = new Hls({
                    maxMaxBufferLength: 10,
                    enableWorker: true,
                    lowLatencyMode: true
                });
                hlsInstance.loadSource(streamUrl);
                hlsInstance.attachMedia(video);
                
                hlsInstance.on(Hls.Events.MANIFEST_PARSED, function() {
                    video.play().catch(() => {
                        updatePlayButtonState(true);
                    });
                });

                hlsInstance.on(Hls.Events.ERROR, function (event, data) {
                    if (data.fatal) {
                        switch (data.type) {
                            case Hls.ErrorTypes.NETWORK_ERROR:
                                hlsInstance.startLoad();
                                break;
                            case Hls.ErrorTypes.MEDIA_ERROR:
                                hlsInstance.recoverMediaError();
                                break;
                            default:
                                loadStream();
                                break;
                        }
                    }
                });

            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                video.src = streamUrl;
                video.addEventListener('loadedmetadata', function() {
                    video.play().catch(() => {
                        updatePlayButtonState(true);
                    });
                });
            }
        }

        // Click to seek to live edge
        function syncToLiveEdge() {
            if (video.seekable && video.seekable.length > 0) {
                spinner.style.display = 'flex';
                video.currentTime = video.seekable.end(video.seekable.length - 1) - 3;
                video.play().then(() => {
                    spinner.style.display = 'none';
                });
            }
        }

        // HTML5 Player Controls Logic
        function togglePlay() {
            if (video.paused) {
                video.play();
            } else {
                video.pause();
            }
        }

        function updatePlayButtonState(isPaused) {
            if (isPaused) {
                playBtn.innerHTML = '<i class="fas fa-play"></i>';
            } else {
                playBtn.innerHTML = '<i class="fas fa-pause"></i>';
            }
        }

        function toggleMute() {
            if (video.muted) {
                video.muted = false;
                volumeBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
            } else {
                video.muted = true;
                volumeBtn.innerHTML = '<i class="fas fa-volume-mute"></i>';
            }
        }

        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                playerContainer.requestFullscreen().catch(err => {
                    console.log("Error attempting to enable full-screen mode:", err);
                });
            } else {
                document.exitFullscreen();
            }
        }

        video.addEventListener('play', () => updatePlayButtonState(false));
        video.addEventListener('pause', () => updatePlayButtonState(true));

        // Loader Event Listeners
        video.addEventListener('waiting', () => { spinner.style.display = 'flex'; });
        video.addEventListener('playing', () => { spinner.style.display = 'none'; });
        video.addEventListener('canplay', () => { spinner.style.display = 'none'; });

        // Auto hide controls
        let controlsTimeout;
        function showControls() {
            playerContainer.classList.remove('hide-controls');
            clearTimeout(controlsTimeout);
            controlsTimeout = setTimeout(() => {
                if (!video.paused) {
                    playerContainer.classList.add('hide-controls');
                }
            }, 3000);
        }

        playerContainer.addEventListener('mousemove', showControls);
        playerContainer.addEventListener('click', showControls);
        video.addEventListener('pause', showControls);

        window.onload = loadStream;
    </script>
</body>
</html>
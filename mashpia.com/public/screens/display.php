<?php
// Get parameters from URL rewriting
$school_id = isset($_GET['school_id']) ? $_GET['school_id'] : null;
$screen_slug = isset($_GET['screen_slug']) ? $_GET['screen_slug'] : null;

// Fallback to parsing URL if parameters not available
if (!$school_id || !$screen_slug) {
    function parseScreenUrl($url = null) {
        $url = $url ?: $_SERVER['REQUEST_URI'];
        $path = parse_url($url, PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        
        // Pattern: /screens/{school_id}/{screen_slug}
        if (count($segments) >= 3 && $segments[0] === 'screens') {
            return [
                'school_id' => $segments[1],
                'screen_slug' => $segments[2],
                'valid' => true
            ];
        }
        
        return ['valid' => false];
    }

    $route = parseScreenUrl();
    if ($route['valid']) {
        $school_id = $route['school_id'];
        $screen_slug = $route['screen_slug'];
    }
}

// Include database connection
require_once __DIR__ . '/../api/header/db.php';

// Check if screen exists and get its details with settings
if ($school_id && $screen_slug) {
    $stmt = $MASHPIA_DB->prepare("
        SELECT s.*, 
               COALESCE(ss.show_promotions, 0) as show_promotions,
               COALESCE(ss.promotions_days, 7) as promotions_days,
               COALESCE(ss.show_birthdays, 0) as show_birthdays,
               COALESCE(ss.birthdays_days, 7) as birthdays_days
        FROM screens s
        LEFT JOIN screen_settings ss ON s.screen_id = ss.screen_id
        WHERE s.school_id = ? AND s.url = ?
    ");
    $stmt->execute([$school_id, $screen_slug]);
    $screen = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$screen) {
        echo "Screen not found";
        exit;
    }
    
    // Start session at the beginning
    session_start();
    
    // Password is always required
    if (true) {
        // Check if password was submitted
        if (isset($_POST['screen_password'])) {
            if ($_POST['screen_password'] === $screen['password']) {
                // Password correct, set session
                $session_key = 'screen_authenticated_' . $school_id . '_' . $screen_slug;
                $_SESSION[$session_key] = true;
                
                // Continue to show screen content instead of redirecting
                // The session is now set, so the next check will pass
            } else {
                // Password incorrect
                $error = "Incorrect password";
            }
        }
        
        // Check if already authenticated
        $session_key = 'screen_authenticated_' . $school_id . '_' . $screen_slug;
        
        if (!isset($_SESSION[$session_key])) {
            // Show password form
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Screen Access - <?php echo htmlspecialchars($screen['screen_name']); ?></title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
                <style>
                    body {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        min-height: 100vh;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    .password-card {
                        background: white;
                        border-radius: 15px;
                        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
                        padding: 2rem;
                        max-width: 400px;
                        width: 100%;
                    }
                    .password-card h2 {
                        color: #333;
                        text-align: center;
                        margin-bottom: 1.5rem;
                    }
                    .form-control {
                        border-radius: 10px;
                        border: 2px solid #e9ecef;
                        padding: 0.75rem 1rem;
                    }
                    .form-control:focus {
                        border-color: #667eea;
                        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
                    }
                    .btn-primary {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        border: none;
                        border-radius: 10px;
                        padding: 0.75rem 2rem;
                        font-weight: 600;
                    }
                    .alert {
                        border-radius: 10px;
                        border: none;
                    }
                </style>
            </head>
            <body>
                <div class="password-card">
                    <h2><i class="fas fa-lock me-2"></i>Screen Access</h2>
                    <p class="text-center text-muted mb-4">Enter PIN to view <?php echo htmlspecialchars($screen['screen_name']); ?></p>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="screen_password" class="form-label">PIN</label>
                            <input type="number" class="form-control" id="screen_password" name="screen_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-unlock me-2"></i>Access Screen
                        </button>
                    </form>
                </div>
            </body>
            </html>
            <?php
            exit;
        }
    }
    $screen_size = $screen['screen_size'];
    if ($screen_size == '1920x1080') {
        $screen_height = 1080;
        $screen_width = 1920;
    } else if ($screen_size == '1366x768') {
        $screen_height = 768;
        $screen_width = 1366;
    } else if ($screen_size == '1280x720') {
        $screen_height = 720;
        $screen_width = 1280;
    } else if ($screen_size == '1024x768') {
        $screen_height = 768;
        $screen_width = 1024;
    } else if ($screen_size == '800x600') {
        $screen_height = 600;
        $screen_width = 800;
    }
    // Display the screen content
    $images_dir = __DIR__ . '/images/';
    $school_id = $screen['school_id'] ?? null;
    $meta_file = $school_id ? $images_dir . 'metadata_' . $school_id . '.json' : null;
    $screen_id = $screen['screen_id'] ?? null;
    $images_to_show = [];
    if ($screen_id && $meta_file && file_exists($meta_file)) {
        $meta = json_decode(file_get_contents($meta_file), true);
        if (is_array($meta)) {
            foreach ($meta as $fname => $props) {
                if (
                    isset($props['screen_id']) && $props['screen_id'] == $screen_id &&
                    !empty($props['visible']) &&
                    file_exists($images_dir . $fname)
                ) {
                    $images_to_show[] = [
                        'url' => '/screens/images/' . $fname,
                        'size' => isset($props['size']) ? intval($props['size']) : 0,
                        'filename' => $fname
                    ];
                }
            }
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($screen['screen_name']); ?> - Digital Screen</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            body {
                margin: 0;
                padding: 0;
                background: #000;
                color: #fff;
                font-family: 'Arial', sans-serif;
                overflow: hidden;
            }
            .screen-container {
                width: <?php echo $screen_width; ?>px;
                height: <?php echo $screen_height; ?>px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                position: relative;
                overflow: hidden;
                box-sizing: border-box;
            }
            
            /* Layout Structure */
            .announcements-section {
                width: 100%;
                height: 50px;
                background: rgba(255,255,255,0.08);
                border-bottom: 1.5px solid rgba(255,255,255,0.13);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0 16px;
                box-sizing: border-box;
            }

            .content-grid {
                display: flex;
                flex-direction: column;
                width: 100%;
                height: calc(<?php echo $screen_height; ?>px - 50px);
                max-height: calc(<?php echo $screen_height; ?>px - 50px);
                box-sizing: border-box;
                overflow: hidden;
            }

            .content-row {
                display: flex;
                flex: 1;
                width: 100%;
                overflow: hidden;
            }
            
            .content-row:first-child {
                height: calc((<?php echo $screen_height; ?>px - 50px) * 0.4);
                max-height: calc((<?php echo $screen_height; ?>px - 50px) * 0.4);
            }
            
            .content-row:last-child {
                height: calc((<?php echo $screen_height; ?>px - 50px) * 0.6);
                max-height: calc((<?php echo $screen_height; ?>px - 50px) * 0.6);
            }

            .content-section {
                flex: 1;
                padding: 12px 8px 0 8px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
                background: rgba(255,255,255,0.05);
                box-sizing: border-box;
                border-right: 1.5px solid rgba(255,255,255,0.13);
                border-bottom: 1.5px solid rgba(255,255,255,0.13);
                overflow: hidden;
                height: 100%;
                max-height: 100%;
            }

            .content-section:last-child {
                border-right: none;
            }

            .content-row:last-child .content-section {
                border-bottom: none;
            }

            /* Section titles */
            .content-section h2 {
                margin-top: 0;
                margin-bottom: 0.8rem;
                font-size: 1.2rem;
            }

            .promotions-title {
                color: #ffd700;
            }
            
            .birthdays-title {
                color: #ff69b4;
            }
            
            .tehillim-title {
                color: #98fb98;
            }
            

            


            /* Content containers */
            .images-container {
                width: 100%;
                height: calc(100% - 40px);
                max-height: calc(100% - 40px);
                overflow: auto;
                box-sizing: border-box;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .promotions-container {
                width: 100%;
                max-width: 800px;
                height: calc(100% - 40px);
                max-height: calc(100% - 40px);
                display: flex;
                flex-direction: column;
                gap: 12px;
                overflow: hidden;
                box-sizing: border-box;
            }
            
            .tehillim-container {
                width: 100%;
                height: calc(100% - 40px);
                max-height: calc(100% - 40px);
                overflow: hidden;
                position: relative;
                box-sizing: border-box;
            }
            
            .tehillim-section {
                overflow: hidden;
                height: 100%;
                max-height: 100%;
            }
            
            .tehillim-container .date-section {
                height: 100%;
                max-height: 100%;
                overflow: hidden;
            }
            
            .tehillim-container .children-list {
                height: calc(100% - 60px);
                max-height: calc(100% - 60px);
                overflow: hidden;
            }

            /* Date sections for promotions/birthdays */
            .date-section {
                background: rgba(255,255,255,0.1);
                border-radius: 15px;
                overflow: hidden;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255,255,255,0.2);
                flex: 1;
                display: flex;
                flex-direction: column;
                min-height: 0;
                max-height: 100%;
            }
            
            .date-header {
                background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
                color: #333;
                padding: 8px 12px;
                font-size: 1rem;
                font-weight: bold;
                text-align: center;
                text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
                border-bottom: 2px solid rgba(255,255,255,0.3);
            }
            
            .children-list {
                flex: 1;
                overflow: hidden;
                padding: 10px;
                position: relative;
                height: 100%;
                min-height: 150px;
            }
            
            .children-list-inner {
                display: flex;
                flex-direction: column;
                animation: scrollChildren 15s linear infinite;
            }
            
            .children-list:hover .children-list-inner {
                animation-play-state: paused;
            }
            
            @keyframes scrollChildren {
                0% { transform: translateY(0); }
                100% { transform: translateY(-50%); }
            }
            
            .children-list::-webkit-scrollbar {
                width: 6px;
            }
            
            .children-list::-webkit-scrollbar-track {
                background: transparent;
            }
            
            .children-list::-webkit-scrollbar-thumb {
                background: rgba(255,255,255,0.3);
                border-radius: 3px;
            }
            
            .children-list::-webkit-scrollbar-thumb:hover {
                background: rgba(255,255,255,0.5);
            }
            
            .child-item {
                padding: 4px 8px;
                margin-bottom: 2px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            
            .child-item:last-child {
                margin-bottom: 0;
            }
            
            .child-name {
                font-size: 0.85rem;
                font-weight: bold;
                color: #fff;
            }
            
            .child-rank {
                font-size: 0.7rem;
                color: rgba(255,255,255,0.8);
                font-style: italic;
            }
            

            

            
            /* Loading and status */
            .no-content {
                font-size: 1rem;
                opacity: 0.6;
                text-align: center;
                font-style: italic;
            }
            
            .loading {
                font-size: 0.9rem;
                opacity: 0.8;
                text-align: center;
            }
            
            .spinner {
                border: 2px solid rgba(255,255,255,0.3);
                border-top: 2px solid #fff;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                animation: spin 1s linear infinite;
                margin: 0 auto 10px;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            /* Screen info */
            .screen-size {
                position: absolute;
                top: 10px;
                right: 10px;
                background: rgba(255,255,255,0.1);
                padding: 6px 10px;
                border-radius: 3px;
                font-size: 0.7rem;
            }
            
            .timestamp {
                position: absolute;
                bottom: 10px;
                left: 10px;
                font-size: 0.8rem;
                opacity: 0.7;
            }
            
            /* Responsive adjustments */
            @media (max-width: 1024px) {
                .content-section {
                    padding: 16px 8px 0 8px;
                }
                .content-section h2 {
                    font-size: 1.5rem;
                }
                .date-header { font-size: 1.1rem; padding: 8px 12px; }
                .child-name { font-size: 0.9rem; }
                .child-rank { font-size: 0.75rem; }
                .child-item { padding: 8px 10px; margin-bottom: 8px; }
                .children-list { padding: 12px; }
            }

            @media (max-width: 768px) {
                .content-grid {
                    flex-direction: column;
                }
                .content-row {
                    flex-direction: column;
                }
                .content-section {
                    border-right: none;
                    border-bottom: 1.5px solid rgba(255,255,255,0.13);
                    padding: 12px 4px 0 4px;
                }
                .content-section:last-child {
                    border-bottom: none;
                }
                .date-header { font-size: 1rem; padding: 6px 10px; }
                .child-name { font-size: 0.85rem; }
                .child-rank { font-size: 0.7rem; }
                .child-item { padding: 6px 8px; margin-bottom: 6px; }
                .children-list { padding: 10px; }
            }
            
            @media (max-width: <?php echo $screen_width; ?>px), (max-height: <?php echo $screen_height; ?>px) {
                .screen-container {
                    width: 100vw !important;
                    height: auto !important;
                    min-height: 100vh;
                    border-radius: 0;
                    overflow-x: auto;
                }
            }
        </style>
    </head>
    <body>
        <div class="screen-container">
            <div class="screen-size"><?php echo $screen_size; ?></div>
            <div class="timestamp" id="timestamp"></div>
            
            <!-- Announcements Section -->
            <div class="announcements-section">
                <h2 style="font-size:1rem;margin:0;"><i class="fas fa-bullhorn me-2"></i>Announcements</h2>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Top Row: Images and Tehillim -->
                <div class="content-row">
                    <div class="content-section">
                        <h2><i class="fas fa-images me-2"></i>Screen Images</h2>
                        <div class="images-container">
                            <?php if (isset($images_to_show) && count($images_to_show)): ?>
                                <div style="display:flex;flex-wrap:wrap;gap:1rem;justify-content:center;align-items:center;">
                                    <?php foreach ($images_to_show as $img): ?>
                                        <div style="background:#fff;padding:0.5rem;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.04);display:flex;align-items:center;justify-content:center;">
                                            <img src="<?= htmlspecialchars($img['url']) ?>" style="<?= $img['size'] > 0 ? "width:{$img['size']}px;height:{$img['size']}px;object-fit:contain;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);max-width:100%;max-height:100%;" : "max-width:100%;height:auto;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);" ?>" alt="Screen Image">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="no-content">No images available</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="content-section tehillim-section">
                        <h2 class="tehillim-title">
                            <i class="fas fa-pray me-2"></i>Tehillim
                        </h2>
                        <div class="tehillim-container" id="tehillim-list">
                            <div class="loading">
                                <div class="spinner"></div>
                                Loading tehillim...
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bottom Row: Promotions and Birthdays -->
                <div class="content-row">
                    <div class="content-section">
                        <h2 class="promotions-title">
                            <i class="fas fa-star me-2"></i>Promotions
                        </h2>
                        <div class="promotions-container" id="promotions-list">
                            <div class="loading">
                                <div class="spinner"></div>
                                Loading promotions...
                            </div>
                        </div>
                    </div>
                    
                    <div class="content-section">
                        <h2 class="birthdays-title">
                            <i class="fas fa-birthday-cake me-2"></i>Birthdays
                        </h2>
                        <div class="promotions-container" id="birthdays-list">
                            <div class="loading">
                                <div class="spinner"></div>
                                Loading birthdays...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            // Update timestamp
            function updateTimestamp() {
                const now = new Date();
                const timestamp = now.toLocaleString();
                document.getElementById('timestamp').textContent = timestamp;
            }
            
            // Update timestamp every second
            updateTimestamp();
            setInterval(updateTimestamp, 1000);
            
            async function getPromotions(days) {
                const promotionsList = document.getElementById('promotions-list');
                console.log('Fetching promotions for days:', days);
                
                fetch('/api/core/homepage/promotions?start=' + (days - 1)) // -1 b/c the end date is the current day
                    .then(response => {
                        console.log('Promotions response status:', response.status);
                        if (!response.ok) {
                            throw new Error('Failed to fetch promotions: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Promotions data:', data); // Debug log
                        if (data && data.success && data.data && Object.keys(data.data).length > 0) {
                            let html = '';
                            // Create a section for each date with fixed header and scrolling children list
                            Object.entries(data.data).forEach(([heDate, promotions]) => {
                                let childItems = ''; // Initialize for child items
                                // Add each child promotion to the list
                                promotions.forEach(promotion => {
                                    const formattedName = promotion.full_name.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
                                    childItems += `<div class="child-item">
                                        <span class="child-name">${formattedName}</span>
                                        <span class="child-rank">${promotion.rank_name}</span>
                                    </div>`;
                                });
                                const duration = Math.max(8, promotions.length * 1.5); // 1.5s per item, min 8s
                                html += `
                                    <div class="date-section">
                                        <div class="date-header">${heDate}</div>
                                        <div class="children-list">
                                            <div class="children-list-inner" style="animation-duration: ${duration}s">
                                                ${childItems}${childItems}
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            promotionsList.innerHTML = html;
                        } else {
                            promotionsList.innerHTML = '<div class="no-content">No promotions available</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching promotions:', error);
                        promotionsList.innerHTML = '<div class="no-content">Unable to load promotions</div>';
                    });
            }

            function getBirthdays(days) {
                const birthdaysList = document.getElementById('birthdays-list');
                console.log('Fetching birthdays for days:', days);
                
                fetch('/api/core/homepage/birthdays?start=' + (days - 1)) // -1 b/c the end date is the current day
                    .then(response => {
                        console.log('Birthdays response status:', response.status);
                        if (!response.ok) {
                            throw new Error('Failed to fetch birthdays: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Birthdays data:', data); // Debug log
                        if (data && data.success && data.data && Object.keys(data.data).length > 0) {
                            let html = '';
                            Object.entries(data.data).forEach(([heDate, birthdays]) => {
                                let childItems = ''; // Initialize for child items
                                birthdays.forEach(birthday => {
                                    const formattedName = birthday.name.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
                                    childItems += `<div class="child-item">
                                        <span class="child-name">${formattedName}</span>
                                        <span class="child-rank">${birthday.platoon}</span>
                                    </div>`;
                                });
                                const duration = Math.max(8, birthdays.length * 1.5); // 1.5s per item, min 8s
                                html += `
                                    <div class="date-section">
                                        <div class="date-header">${heDate}</div>
                                        <div class="children-list">
                                            <div class="children-list-inner" style="animation-duration: ${duration}s">
                                                ${childItems}${childItems}
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            birthdaysList.innerHTML = html;
                        } else {
                            birthdaysList.innerHTML = '<div class="no-content">No birthdays this week</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching birthdays:', error);
                        birthdaysList.innerHTML = '<div class="no-content">Unable to load birthdays</div>';
                    });
            }
            
            function getTehillim() {
                const tehillimList = document.getElementById('tehillim-list');
                console.log('Fetching tehillim');
                
                fetch('/screens/ajax/getTehillim.php')
                    .then(response => {
                        console.log('Tehillim response status:', response.status);
                        if (!response.ok) {
                            throw new Error('Failed to fetch tehillim: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Tehillim data:', data);
                        if (data && data.success && data.data && data.data.length > 0) {
                            let html = '';
                            data.data.forEach(tehillim => {
                                const formattedName = tehillim.name.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
                                html += `<div class="child-item">
                                    <span class="child-name">${formattedName}</span>
                                    <span class="child-rank">${tehillim.chapter}</span>
                                </div>`;
                            });
                            const duration = Math.max(8, data.data.length * 1.5);
                            tehillimList.innerHTML = `
                                <div style="height: 100%; max-height: 100%; overflow: hidden; display: flex; flex-direction: column;">
                                    <div class="date-header">Today's Tehillim</div>
                                    <div style="flex: 1; overflow: hidden; padding: 15px;">
                                        <div class="children-list-inner" style="animation-duration: ${duration}s">
                                            ${html}${html}
                                        </div>
                                    </div>
                                </div>
                            `;
                        } else {
                            tehillimList.innerHTML = '<div class="no-content">No tehillim available</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching tehillim:', error);
                        tehillimList.innerHTML = '<div class="no-content">Unable to load tehillim</div>';
                    });
            }
            
            // Auto-refresh content every 5 minutes
            function refreshContent() {
                <?php if ($screen['show_promotions']): ?>
                    getPromotions(<?php echo $screen['promotions_days']; ?>);
                <?php else: ?>
                    document.getElementById('promotions-list').innerHTML = '<div class="no-content">Promotions disabled</div>';
                <?php endif; ?>
                <?php if ($screen['show_birthdays']): ?>
                    getBirthdays(<?php echo $screen['birthdays_days']; ?>);
                <?php else: ?>
                    document.getElementById('birthdays-list').innerHTML = '<div class="no-content">Birthdays disabled</div>';
                <?php endif; ?>
                getTehillim();
            }
            
            // Initial load
            refreshContent();
            
            // Refresh every 5 minutes
            setInterval(refreshContent, 5 * 60 * 1000);
        </script>
    </body>
    </html>
    <?php
} else {
    echo "Invalid screen URL";
}
?>
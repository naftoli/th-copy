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
            }
            .screen-title {
                font-size: 3rem;
                font-weight: bold;
                margin-bottom: 1rem;
                text-align: center;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            }
            .screen-info {
                font-size: 1.5rem;
                text-align: center;
                opacity: 0.8;
            }
            .screen-size {
                position: absolute;
                top: 20px;
                right: 20px;
                background: rgba(255,255,255,0.1);
                padding: 10px 15px;
                border-radius: 5px;
                font-size: 0.9rem;
            }
            .timestamp {
                position: absolute;
                bottom: 20px;
                left: 20px;
                font-size: 1rem;
                opacity: 0.7;
            }
            
            /* Promotions and Birthdays UI */
            .content-sections {
                display: flex;
                width: 100%;
                height: 100%;
                position: absolute;
                top: 0;
                left: 0;
            }
            
            .promotions-section {
                flex: 1;
                padding: 40px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                background: rgba(255,255,255,0.05);
                border-right: 1px solid rgba(255,255,255,0.1);
            }
            
            .birthdays-section {
                flex: 1;
                padding: 40px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                background: rgba(255,255,255,0.05);
            }
            
            .section-title {
                font-size: 2.5rem;
                font-weight: bold;
                margin-bottom: 30px;
                text-align: center;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            }
            
            .promotions-title {
                color: #ffd700;
            }
            
            .birthdays-title {
                color: #ff69b4;
            }
            
                    .content-list {
            width: 100%;
            max-width: 600px;
            text-align: center;
        }
        
                    .promotions-container {
                width: 100%;
                max-width: 800px;
                height: 100%;
                display: flex;
                flex-direction: column;
                gap: 20px;
            }
            
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
            }
            
            .date-header {
                background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
                color: #333;
                padding: 15px 20px;
                font-size: 1.4rem;
                font-weight: bold;
                text-align: center;
                text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
                border-bottom: 2px solid rgba(255,255,255,0.3);
            }
            
            .children-list {
                flex: 1;
                overflow: hidden;
                padding: 15px;
                position: relative;
                height: 100%;
                min-height: 200px;
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
                background: rgba(255,255,255,0.1);
                border-radius: 10px;
                padding: 12px 15px;
                margin-bottom: 10px;
                border-left: 4px solid #ffd700;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }
            
            .child-item:hover {
                transform: translateX(5px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            }
            
            .child-item:last-child {
                margin-bottom: 0;
            }
            
            .child-name {
                font-size: 1.1rem;
                font-weight: bold;
                color: #fff;
                margin-bottom: 5px;
            }
            
            .child-rank {
                font-size: 0.9rem;
                color: rgba(255,255,255,0.8);
                font-style: italic;
            }
            
            /* Birthday specific styling */
            .birthdays-section .date-header {
                background: linear-gradient(135deg, #ff69b4 0%, #ff8da1 100%);
            }
            
            .birthdays-section .child-item {
                border-left: 4px solid #ff69b4;
            }
            
            .content-item {
                background: rgba(255,255,255,0.1);
                border-radius: 15px;
                padding: 20px;
                margin-bottom: 20px;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255,255,255,0.2);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            
            .content-item:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            }
            
            .promotion-item {
                border-left: 5px solid #ffd700;
            }
            
            .birthday-item {
                border-left: 5px solid #ff69b4;
            }
            
            .item-title {
                font-size: 1.3rem;
                font-weight: bold;
                margin-bottom: 10px;
                color: #fff;
            }
            
            .item-description {
                font-size: 1rem;
                opacity: 0.9;
                line-height: 1.4;
            }
            
                    .item-date {
            font-size: 0.9rem;
            opacity: 0.7;
            margin-top: 10px;
            font-style: italic;
        }
        
        .item-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-top: 10px;
            border: 2px solid rgba(255,255,255,0.3);
        }
            
            .no-content {
                font-size: 1.5rem;
                opacity: 0.6;
                text-align: center;
                font-style: italic;
            }
            
            .loading {
                font-size: 1.2rem;
                opacity: 0.8;
                text-align: center;
            }
            
            .spinner {
                border: 3px solid rgba(255,255,255,0.3);
                border-top: 3px solid #fff;
                border-radius: 50%;
                width: 30px;
                height: 30px;
                animation: spin 1s linear infinite;
                margin: 0 auto 15px;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            /* Responsive adjustments for different screen sizes */
            @media (max-width: 1920px) {
                .section-title { font-size: 2rem; }
                .item-title { font-size: 1.1rem; }
                .item-description { font-size: 0.9rem; }
                .date-header { font-size: 1.3rem; padding: 12px 18px; }
                .child-name { font-size: 1rem; }
                .child-rank { font-size: 0.85rem; }
            }
            
            @media (max-width: 1366px) {
                .section-title { font-size: 1.8rem; }
                .item-title { font-size: 1rem; }
                .item-description { font-size: 0.85rem; }
                .date-header { font-size: 1.2rem; padding: 10px 15px; }
                .child-name { font-size: 0.95rem; }
                .child-rank { font-size: 0.8rem; }
                .child-item { padding: 10px 12px; }
            }
            
            @media (max-width: 1024px) {
                .section-title { font-size: 1.5rem; }
                .item-title { font-size: 0.9rem; }
                .item-description { font-size: 0.8rem; }
                .date-header { font-size: 1.1rem; padding: 8px 12px; }
                .child-name { font-size: 0.9rem; }
                .child-rank { font-size: 0.75rem; }
                .child-item { padding: 8px 10px; margin-bottom: 8px; }
                .children-list { padding: 12px; }
            }
            
            @media (max-width: 768px) {
                .content-sections {
                    flex-direction: column;
                }
                .promotions-section, .birthdays-section {
                    flex: none;
                    height: 50vh;
                    padding: 20px;
                }
                .date-header { font-size: 1rem; padding: 6px 10px; }
                .child-name { font-size: 0.85rem; }
                .child-rank { font-size: 0.7rem; }
                .child-item { padding: 6px 8px; margin-bottom: 6px; }
                .children-list { padding: 10px; }
            }
        </style>
    </head>
    <body>
        <div class="screen-container">
            <div class="screen-size"><?php echo $screen_size; ?></div>
            <div class="timestamp" id="timestamp"></div>
            
            <?php if ($screen['show_promotions'] || $screen['show_birthdays']): ?>
                <div class="content-sections">
                    <?php if ($screen['show_promotions']): ?>
                        <div class="promotions-section">
                            <h2 class="section-title promotions-title">
                                <i class="fas fa-star me-2"></i>Promotions
                            </h2>
                            <div class="promotions-container" id="promotions-list">
                                <div class="loading">
                                    <div class="spinner"></div>
                                    Loading promotions...
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($screen['show_birthdays']): ?>
                        <div class="birthdays-section">
                            <h2 class="section-title birthdays-title">
                                <i class="fas fa-birthday-cake me-2"></i>Birthdays
                            </h2>
                            <div class="promotions-container" id="birthdays-list">
                                <div class="loading">
                                    <div class="spinner"></div>
                                    Loading birthdays...
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="screen-title"><?php echo htmlspecialchars($screen['screen_name']); ?></div>
                <div class="screen-info">Digital Screen Display</div>
            <?php endif; ?>
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
                
                fetch('/api/core/homepage/promotions?start=' + days)
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
                                html += `<div class="date-section">
                                    <div class="date-header">${heDate}</div>
                                    <div class="children-list">`;
                                
                                let childItems = ''; // Initialize for child items
                                // Add each child promotion to the list
                                promotions.forEach(promotion => {
                                    // promotion.name is "rank_name first last"
                                    const [rank, ...nameParts] = promotion.name.split(' ');
                                    const name = nameParts.join(' ');
                                    childItems += `<div class="child-item">
                                        <div class="child-name">${name}</div>
                                        <div class="child-rank">${rank}</div>
                                    </div>`;
                                });
                                const duration = Math.max(8, promotions.length * 1.5); // 1.5s per item, min 8s
                                html += `<div class="children-list"><div class="children-list-inner" style="animation-duration: ${duration}s">${childItems}${childItems}</div></div>`;
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
                
                fetch('/api/core/homepage/birthdays?start=' + days)
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
                                html += `<div class="date-section">
                                    <div class="date-header">${heDate}</div>
                                    <div class="children-list">`;
                                
                                let childItems = ''; // Initialize for child items
                                birthdays.forEach(birthday => {
                                    childItems += `<div class="child-item">
                                        <div class="child-name">${birthday.name}</div>
                                        <div class="child-rank">${birthday.platoon}</div>
                                    </div>`;
                                });
                                const duration = Math.max(8, birthdays.length * 1.5); // 1.5s per item, min 8s
                                html += `<div class="children-list"><div class="children-list-inner" style="animation-duration: ${duration}s">${childItems}${childItems}</div></div>`;
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
            
            // Auto-refresh content every 5 minutes
            function refreshContent() {
                <?php if ($screen['show_promotions']): ?>
                    getPromotions(<?php echo $screen['promotions_days']; ?>);
                <?php endif; ?>
                <?php if ($screen['show_birthdays']): ?>
                    getBirthdays(<?php echo $screen['birthdays_days']; ?>);
                <?php endif; ?>
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
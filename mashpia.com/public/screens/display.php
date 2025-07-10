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

// Check if screen exists and get its details
if ($school_id && $screen_slug) {
    $stmt = $MASHPIA_DB->prepare("SELECT * FROM screens WHERE school_id = ? AND url = ?");
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
            if (password_verify($_POST['screen_password'], $screen['password'])) {
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
                    <p class="text-center text-muted mb-4">Enter password to view <?php echo htmlspecialchars($screen['screen_name']); ?></p>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="screen_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="screen_password" name="screen_password" required>
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
                width: 100vw;
                height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .screen-title {
                font-size: 3rem;
                font-weight: bold;
                margin-bottom: 1rem;
                text-align: center;
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
        </style>
    </head>
    <body>
        <div class="screen-container">
            <div class="screen-size">
                <?php echo htmlspecialchars($screen['screen_size']); ?>
            </div>
            
            <div class="screen-title">
                <i class="fas fa-tv me-3"></i>
                <?php echo htmlspecialchars($screen['screen_name']); ?>
            </div>
            
            <div class="screen-info">
                <i class="fas fa-school me-2"></i>
                School ID: <?php echo htmlspecialchars($school_id); ?>
            </div>
            
            <div class="timestamp" id="timestamp"></div>
        </div>
        
        <script>
            // Update timestamp
            function updateTimestamp() {
                const now = new Date();
                document.getElementById('timestamp').textContent = now.toLocaleString();
            }
            
            updateTimestamp();
            setInterval(updateTimestamp, 1000);
            
            // Auto-refresh every 30 seconds
            setTimeout(() => {
                location.reload();
            }, 30000);
        </script>
    </body>
    </html>
    <?php
} else {
    echo "Invalid screen URL";
}
?>
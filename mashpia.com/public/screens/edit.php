<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../api/header/db.php';

require_once __DIR__ . '/../class.adminSchools.php';
$adminSchools = new adminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $adminSchools->getSchools();

// Get screen ID and school ID from URL
$school_id = isset($_GET['school_id']) ? (int)$_GET['school_id'] : 0;
$screen_url = isset($_GET['screen']) ? $_GET['screen'] : '';

// Validate access - ensure the school can only edit their own screens if not super admin
if ($admin_user['auth'] !== 'super' && !in_array($school_id, array_keys($schools))) {
    header('Location: index.php');
    exit;
}

// Get screen data
$sql = "SELECT * FROM screens WHERE url = ? AND school_id = ?";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([$screen_url, $school_id]);
$screen = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$screen) {
    header('Location: index.php');
    exit;
}

// Get school data
$school_name = $schools[$school_id];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $screen_name = trim($_POST['screen_name']);
    $screen_size = $_POST['screen_size'];
    $display_type = $_POST['display_type'];
    $content_data = $_POST['content_data'];
    $refresh_interval = (int)$_POST['refresh_interval'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate data
    if (empty($screen_name)) {
        $error = "Screen name is required.";
    } else {
                    // Update screen
            $update_sql = "UPDATE screens SET 
                           screen_name = ?, 
                           screen_size = ?,
                           display_type = ?, 
                           content_data = ?, 
                           refresh_interval = ?, 
                           is_active = ?
                           WHERE url = ? AND school_id = ?";
            
            $update_stmt = $MASHPIA_DB->prepare($update_sql);
            $result = $update_stmt->execute([
                $screen_name,
                $screen_size,
                $display_type,
                $content_data,
                $refresh_interval,
                $is_active,
                $screen_url,
                $school_id
            ]);
        
        if ($result) {
            $success = "Screen updated successfully!";
            // Refresh screen data
            $stmt->execute([$screen_id, $school_id]);
            $screen = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Failed to update screen.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Digital Screen</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CodeMirror for JSON editing -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4258a2;
            --secondary-color: #e92d41;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--dark-text);
        }

        .main-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin: 2rem auto;
            max-width: 1200px;
            overflow: hidden;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2d3a6a 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .hero-section h1 {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .hero-section p {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 0;
        }

        .content-section {
            padding: 2rem;
        }

        .form-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid #e9ecef;
        }

        .form-card h2 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(66, 88, 162, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2d3a6a 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(66, 88, 162, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }

        .alert {
            border-radius: var(--border-radius);
            border: none;
            padding: 1rem 1.5rem;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }

        .display-type-info {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 0.5rem;
        }

        .display-type-info h6 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .CodeMirror {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            height: 300px;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .preview-section {
            background: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .preview-section h3 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .main-container {
                margin: 1rem;
            }
            
            .hero-section {
                padding: 1.5rem 1rem;
            }
            
            .hero-section h1 {
                font-size: 1.5rem;
            }
            
            .content-section {
                padding: 1.5rem 1rem;
            }
            
            .form-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="hero-section">
            <h1><i class="fas fa-edit me-2"></i>Edit Digital Screen</h1>
            <p><?php echo htmlspecialchars($school_name); ?> - <?php echo htmlspecialchars($screen['screen_name']); ?></p>
        </div>

        <div class="content-section">
            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <h2><i class="fas fa-cog me-2"></i>Screen Settings</h2>
                <form method="POST" id="edit-screen-form">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="screen_name" class="form-label">Screen Name</label>
                                <input type="text" id="screen_name" name="screen_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($screen['screen_name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="screen_size" class="form-label">Screen Size</label>
                                <select name="screen_size" id="screen_size" class="form-select" required>
                                    <option value="">Select screen size...</option>
                                    <option value="1920x1080" <?php echo ($screen['screen_size'] ?? '') === '1920x1080' ? 'selected' : ''; ?>>1920x1080 (Full HD)</option>
                                    <option value="1366x768" <?php echo ($screen['screen_size'] ?? '') === '1366x768' ? 'selected' : ''; ?>>1366x768 (HD)</option>
                                    <option value="1280x720" <?php echo ($screen['screen_size'] ?? '') === '1280x720' ? 'selected' : ''; ?>>1280x720 (HD Ready)</option>
                                    <option value="1024x768" <?php echo ($screen['screen_size'] ?? '') === '1024x768' ? 'selected' : ''; ?>>1024x768 (XGA)</option>
                                    <option value="800x600" <?php echo ($screen['screen_size'] ?? '') === '800x600' ? 'selected' : ''; ?>>800x600 (SVGA)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="display_type" class="form-label">Display Type</label>
                                <select name="display_type" id="display_type" class="form-select" required>
                                    <option value="announcements" <?php //echo $screen['display_type'] === 'announcements' ? 'selected' : ''; ?>>Announcements</option>
                                    <option value="schedule" <?php //echo $screen['display_type'] === 'schedule' ? 'selected' : ''; ?>>Schedule</option>
                                    <option value="achievements" <?php //echo $screen['display_type'] === 'achievements' ? 'selected' : ''; ?>>Achievements</option>
                                    <option value="news" <?php //echo $screen['display_type'] === 'news' ? 'selected' : ''; ?>>News</option>
                                    <option value="custom" <?php //echo $screen['display_type'] === 'custom' ? 'selected' : ''; ?>>Custom Content</option>
                                </select>
                                <div class="display-type-info" id="display-type-info">
                                    <h6>Announcements</h6>
                                    <p class="mb-0">Display school announcements and important messages.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="refresh_interval" class="form-label">Refresh Interval (seconds)</label>
                                <input type="number" id="refresh_interval" name="refresh_interval" class="form-control" 
                                       value="<?php //echo $screen['refresh_interval']; ?>" min="30" max="3600">
                                <small class="form-text text-muted">How often the screen content refreshes (30-3600 seconds)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                           <?php //echo $screen['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Active Screen
                                    </label>
                                    <small class="form-text text-muted d-block">Enable this screen to display content</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="content_data" class="form-label">Content Data (JSON)</label>
                        <textarea id="content_data" name="content_data" class="form-control" rows="10"><?php //echo htmlspecialchars($screen['content_data']); ?></textarea>
                        <small class="form-text text-muted">Enter JSON data for the screen content. Use the preview below to see how it will look.</small>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Screens
                        </a>
                    </div>
                </form>
            </div>

            <div class="preview-section">
                <h3><i class="fas fa-eye me-2"></i>Preview</h3>
                <div id="preview-content">
                    <div class="text-center text-muted">
                        <i class="fas fa-tv fa-3x mb-3"></i>
                        <p>Preview will appear here when you select a display type and enter content.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- CodeMirror -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
    
    <script>
        // Initialize CodeMirror for JSON editing
        const contentEditor = CodeMirror.fromTextArea(document.getElementById('content_data'), {
            mode: 'application/json',
            theme: 'monokai',
            lineNumbers: true,
            autoCloseBrackets: true,
            matchBrackets: true,
            indentUnit: 2,
            tabSize: 2,
            lineWrapping: true
        });

        // Display type information
        const displayTypeInfo = {
            announcements: {
                title: 'Announcements',
                description: 'Display school announcements and important messages.',
                example: {
                    "announcements": [
                        {
                            "title": "Welcome to School",
                            "message": "Have a great day!",
                            "priority": "high"
                        }
                    ]
                }
            },
            schedule: {
                title: 'Schedule',
                description: 'Show class schedules and events.',
                example: {
                    "schedule": [
                        {
                            "time": "9:00 AM",
                            "class": "Math",
                            "room": "101"
                        }
                    ]
                }
            },
            achievements: {
                title: 'Achievements',
                description: 'Display student achievements and awards.',
                example: {
                    "achievements": [
                        {
                            "student": "John Doe",
                            "achievement": "Perfect Attendance",
                            "date": "2024-01-15"
                        }
                    ]
                }
            },
            news: {
                title: 'News',
                description: 'Show school news and updates.',
                example: {
                    "news": [
                        {
                            "headline": "School Event",
                            "summary": "Join us for the annual fundraiser",
                            "date": "2024-01-20"
                        }
                    ]
                }
            },
            custom: {
                title: 'Custom Content',
                description: 'Custom JSON structure for special displays.',
                example: {
                    "custom": {
                        "title": "Custom Display",
                        "content": "Your custom content here"
                    }
                }
            }
        };

        function updateDisplayTypeInfo() {
            const displayType = document.getElementById('display_type').value;
            const info = displayTypeInfo[displayType];
            const infoDiv = document.getElementById('display-type-info');
            
            infoDiv.innerHTML = `
                <h6>${info.title}</h6>
                <p class="mb-0">${info.description}</p>
            `;
            
            updatePreview();
        }

        function updatePreview() {
            const displayType = document.getElementById('display_type').value;
            const contentData = contentEditor.getValue();
            const previewDiv = document.getElementById('preview-content');
            
            try {
                const parsedData = JSON.parse(contentData);
                const info = displayTypeInfo[displayType];
                
                let previewHTML = `
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-tv me-2"></i>${info.title} Preview</h5>
                        </div>
                        <div class="card-body">
                `;
                
                switch(displayType) {
                    case 'announcements':
                        if (parsedData.announcements) {
                            parsedData.announcements.forEach(announcement => {
                                previewHTML += `
                                    <div class="alert alert-info">
                                        <h6>${announcement.title}</h6>
                                        <p class="mb-0">${announcement.message}</p>
                                    </div>
                                `;
                            });
                        }
                        break;
                    case 'schedule':
                        if (parsedData.schedule) {
                            previewHTML += '<div class="table-responsive"><table class="table table-sm">';
                            previewHTML += '<thead><tr><th>Time</th><th>Class</th><th>Room</th></tr></thead><tbody>';
                            parsedData.schedule.forEach(item => {
                                previewHTML += `<tr><td>${item.time}</td><td>${item.class}</td><td>${item.room}</td></tr>`;
                            });
                            previewHTML += '</tbody></table></div>';
                        }
                        break;
                    case 'achievements':
                        if (parsedData.achievements) {
                            parsedData.achievements.forEach(achievement => {
                                previewHTML += `
                                    <div class="alert alert-success">
                                        <h6>🏆 ${achievement.student}</h6>
                                        <p class="mb-0">${achievement.achievement}</p>
                                        <small class="text-muted">${achievement.date}</small>
                                    </div>
                                `;
                            });
                        }
                        break;
                    case 'news':
                        if (parsedData.news) {
                            parsedData.news.forEach(item => {
                                previewHTML += `
                                    <div class="alert alert-warning">
                                        <h6>📰 ${item.headline}</h6>
                                        <p class="mb-0">${item.summary}</p>
                                        <small class="text-muted">${item.date}</small>
                                    </div>
                                `;
                            });
                        }
                        break;
                    case 'custom':
                        previewHTML += `
                            <div class="alert alert-secondary">
                                <h6>Custom Content</h6>
                                <pre class="mb-0">${JSON.stringify(parsedData, null, 2)}</pre>
                            </div>
                        `;
                        break;
                }
                
                previewHTML += '</div></div>';
                previewDiv.innerHTML = previewHTML;
                
            } catch (e) {
                previewDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Invalid JSON format. Please check your content data.
                    </div>
                `;
            }
        }

        // Event listeners
        document.getElementById('display_type').addEventListener('change', updateDisplayTypeInfo);
        contentEditor.on('change', updatePreview);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateDisplayTypeInfo();
        });
    </script>
</body>
</html> 
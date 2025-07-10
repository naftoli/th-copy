<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../header.php';

require_once __DIR__ . '/../class.adminSchools.php';
$adminSchools = new adminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $adminSchools->getSchools();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Digital Screen</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            padding: 3rem 2rem;
            text-align: center;
        }

        .hero-section h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .hero-section p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 0;
        }

        .content-section {
            padding: 3rem 2rem;
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

        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #1e7e34 100%);
            border: none;
            border-radius: 6px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 6px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-1px);
        }

        .table {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }

        .table thead {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2d3a6a 100%);
            color: white;
        }

        .table th {
            border: none;
            font-weight: 600;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .code-badge {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            color: var(--primary-color);
        }

        .screens-section {
            margin-top: 3rem;
        }

        .screens-section h2 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
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

        @media (max-width: 768px) {
            .main-container {
                margin: 1rem;
            }
            
            .hero-section {
                padding: 2rem 1rem;
            }
            
            .hero-section h1 {
                font-size: 2rem;
            }
            
            .content-section {
                padding: 2rem 1rem;
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
            <h1><i class="fas fa-tv me-2"></i>Digital Screens</h1>
            <p>Create beautiful digital screens for your school in seconds</p>
        </div>

        <div class="content-section">
            <div class="form-card">
                <h2><i class="fas fa-plus-circle me-2"></i>Create New Screen</h2>
                <form method="POST" id="create-screen-form">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="school_id" class="form-label">School Name</label>
                                <select name="school_id" id="school_id" class="form-select" required>
                                    <option value="">Select a school...</option>
                                    <?php foreach ($schools as $school_id => $school_name) { ?>
                                        <option value="<?php echo $school_id; ?>
                                        <?php if (count($schools) == 1) echo 'selected'; ?>
                                        "><?php echo $school_name; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="screen_name" class="form-label">Screen Name</label>
                                <input type="text" id="screen_name" name="screen_name" class="form-control" required 
                                       placeholder="e.g., Main Lobby, Cafeteria, Library">
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-magic me-2"></i>Create Screen & Get URL
                        </button>
                    </div>
                </form>
            </div>

            <div class="screens-section">
                <h2><i class="fas fa-list me-2"></i>Recent Screens</h2>
                <div class="loading-spinner" id="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading screens...</p>
                </div>
                <div class="recent-screens" id="recent-screens"></div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function copyUrl(path) {
            const fullUrl = window.location.origin + path;
            navigator.clipboard.writeText(fullUrl).then(() => {
                showAlert('URL copied to clipboard!', 'success');
            }).catch(() => {
                showAlert('Failed to copy URL', 'danger');
            });
        }

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.content-section');
            container.insertBefore(alertDiv, container.firstChild);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        document.getElementById('create-screen-form').addEventListener('submit', function(e) {
            e.preventDefault();
            createScreen();
        });

        function createScreen() {
            const form = document.getElementById('create-screen-form');
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';
            
            const formData = new FormData(form);
            fetch('ajax/addScreen.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Screen created successfully!', 'success');
                    form.reset();
                    getScreens();
                } else {
                    showAlert(data.message || 'Failed to create screen', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while creating the screen', 'danger');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        }

        function getScreens() {
            const loadingSpinner = document.getElementById('loading-spinner');
            const recentScreens = document.getElementById('recent-screens');
            
            loadingSpinner.style.display = 'block';
            recentScreens.innerHTML = '';
            
            fetch('ajax/getScreens.php')
                .then(response => response.json())
                .then(data => {
                    loadingSpinner.style.display = 'none';
                    
                    if (data.length === 0) {
                        recentScreens.innerHTML = `
                            <div class="text-center py-5">
                                <i class="fas fa-tv fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">No screens created yet</h4>
                                <p class="text-muted">Create your first digital screen above!</p>
                            </div>
                        `;
                        return;
                    }
                    
                    let html = `
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-school me-2"></i>School</th>
                                        <th><i class="fas fa-tv me-2"></i>Screen Name</th>
                                        <th><i class="fas fa-link me-2"></i>Display URL</th>
                                        <th><i class="fas fa-cogs me-2"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    Object.keys(data).forEach((school_id) => {
                        data[school_id].forEach((screen) => {
                            html += `
                                <tr>
                                    <td><strong>${screen.school_name || 'N/A'}</strong></td>
                                    <td>${screen.screen_name}</td>
                                    <td>
                                        <span class="code-badge">/screens/display.php/${school_id}/${screen.url}</span>
                                    </td>
                                                                         <td>
                                         <div class="btn-group" role="group">
                                             <button onclick="copyUrl('/screens/display.php/${school_id}/${screen.url}')" class="btn btn-success btn-sm">
                                                 <i class="fas fa-copy me-1"></i>Copy
                                             </button>
                                             <a href="edit.php?school_id=${school_id}&screen=${screen.url}" class="btn btn-outline-primary btn-sm">
                                                 <i class="fas fa-edit me-1"></i>Edit Content
                                             </a>
                                             <a href="/screens/display.php/${school_id}/${screen.url}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                 <i class="fas fa-external-link-alt me-1"></i>View
                                             </a>
                                         </div>
                                     </td>
                                </tr>
                            `;
                        });
                    });
                    
                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                    
                    recentScreens.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    loadingSpinner.style.display = 'none';
                    recentScreens.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Failed to load screens. Please try again.
                        </div>
                    `;
                });
        }

        // Load screens on page load
        document.addEventListener('DOMContentLoaded', function() {
            getScreens();
        });
    </script>
</body>
</html>
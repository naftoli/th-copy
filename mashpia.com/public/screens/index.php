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
            <div class="text-center mb-4">
                <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#screenModal" onclick="openCreateModal()">
                    <i class="fas fa-plus-circle me-2"></i>Add Screen
                </button>
            </div>

            <div class="screens-section">
                <h2><i class="fas fa-list me-2"></i>My  Screens</h2>
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
    
    <!-- Screen Modal -->
    <div class="modal fade" id="screenModal" tabindex="-1" aria-labelledby="screenModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="screenModalLabel">
                        <i class="fas fa-tv me-2"></i><span id="modalTitle">Add New Screen</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="screenForm">
                        <input type="hidden" id="modalMode" value="create">
                        <input type="hidden" id="editScreenUrl" value="">
                        <input type="hidden" id="editSchoolId" value="">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="modalSchoolId" class="form-label">School Name</label>
                                    <select name="school_id" id="modalSchoolId" class="form-select" required>
                                        <option value="">Select a school...</option>
                                        <?php foreach ($schools as $school_id => $school_name) { ?>
                                            <option value="<?php echo $school_id; ?>"><?php echo $school_name; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="modalScreenName" class="form-label">Screen Name</label>
                                    <input type="text" id="modalScreenName" name="screen_name" class="form-control" required 
                                           placeholder="e.g., Main Lobby, Cafeteria, Library">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="modalScreenSize" class="form-label">Screen Size</label>
                                    <select name="screen_size" id="modalScreenSize" class="form-select" required>
                                        <option value="">Select screen size...</option>
                                        <option value="1920x1080">1920x1080 (Full HD)</option>
                                        <option value="1366x768">1366x768 (HD)</option>
                                        <option value="1280x720">1280x720 (HD Ready)</option>
                                        <option value="1024x768">1024x768 (XGA)</option>
                                        <option value="800x600">800x600 (SVGA)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="modalUrl" class="form-label">URL</label>
                                    <input type="text" id="modalUrl" class="form-control" readonly>
                                    <small class="form-text text-muted">Auto-generated from screen name</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveScreen()">
                        <i class="fas fa-save me-2"></i><span id="saveButtonText">Create Screen</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
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

        function generateSlug(text) {
            return text.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        }

        function openCreateModal() {
            document.getElementById('modalMode').value = 'create';
            document.getElementById('modalTitle').textContent = 'Add New Screen';
            document.getElementById('saveButtonText').textContent = 'Create Screen';
            document.getElementById('screenForm').reset();
            document.getElementById('modalUrl').value = '';
            document.getElementById('modalSchoolId').disabled = false;
            document.getElementById('modalScreenName').disabled = false;
        }

        function openEditModal(schoolId, screenUrl, screenName, screenSize) {
            document.getElementById('modalMode').value = 'edit';
            document.getElementById('modalTitle').textContent = 'Edit Screen';
            document.getElementById('saveButtonText').textContent = 'Update Screen';
            document.getElementById('editScreenUrl').value = screenUrl;
            document.getElementById('editSchoolId').value = schoolId;
            
            document.getElementById('modalSchoolId').value = schoolId;
            document.getElementById('modalScreenName').value = screenName;
            document.getElementById('modalScreenSize').value = screenSize;
            document.getElementById('modalUrl').value = screenUrl;
            
            // Disable school field in edit mode, but allow screen name to be edited
            document.getElementById('modalSchoolId').disabled = true;
            document.getElementById('modalScreenName').disabled = false;
        }

        // Auto-generate URL when screen name changes
        document.getElementById('modalScreenName').addEventListener('input', function() {
            const screenName = this.value;
            const url = generateSlug(screenName);
            document.getElementById('modalUrl').value = url;
        });

        function saveScreen() {
            const mode = document.getElementById('modalMode').value;
            const formData = new FormData();
            
            formData.append('school_id', document.getElementById('modalSchoolId').value);
            formData.append('screen_name', document.getElementById('modalScreenName').value);
            formData.append('screen_size', document.getElementById('modalScreenSize').value);
            
            if (mode === 'edit') {
                formData.append('screen_url', document.getElementById('editScreenUrl').value);
                formData.append('new_url', document.getElementById('modalUrl').value);
                formData.append('mode', 'edit');
            } else {
                formData.append('mode', 'create');
            }
            
            const saveBtn = document.querySelector('#screenModal .btn-primary');
            const originalText = saveBtn.innerHTML;
            
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            
            const endpoint = mode === 'edit' ? 'ajax/updateScreen.php' : 'ajax/addScreen.php';
            
            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(mode === 'edit' ? 'Screen updated successfully!' : 'Screen created successfully!', 'success');
                    
                    // Close the modal
                    const modalElement = document.getElementById('screenModal');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    } else {
                        // Fallback: trigger the close button
                        const closeButton = modalElement.querySelector('[data-bs-dismiss="modal"]');
                        if (closeButton) {
                            closeButton.click();
                        }
                    }
                    
                    getScreens();
                } else {
                    showAlert(data.message || 'Failed to save screen', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while saving the screen', 'danger');
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
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
                                        <th><i class="fas fa-expand-arrows-alt me-2"></i>Screen Size</th>
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
                                    <td><span class="badge bg-info">${screen.screen_size || 'Not set'}</span></td>
                                    <td>
                                        <span class="code-badge">/screens/${school_id}/${screen.url}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button onclick="copyUrl('/screens/${school_id}/${screen.url}')" class="btn btn-success btn-sm">
                                                <i class="fas fa-copy me-1"></i>Copy
                                            </button>
                                            <button onclick="openEditModal('${school_id}', '${screen.url}', '${screen.screen_name}', '${screen.screen_size || ''}')" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#screenModal">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </button>
                                            <a href="/screens/${school_id}/${screen.url}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-external-link-alt me-1"></i>View
                                            </a>
                                            <button onclick="deleteScreen('${school_id}', '${screen.url}', '${screen.screen_name}')" class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-trash me-1"></i>Delete
                                            </button>
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

        function deleteScreen(schoolId, screenUrl, screenName) {
            if (confirm(`Are you sure you want to delete the screen "${screenName}"? This action cannot be undone.`)) {
                const formData = new FormData();
                formData.append('school_id', schoolId);
                formData.append('screen_url', screenUrl);
                
                fetch('ajax/deleteScreen.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Screen deleted successfully!', 'success');
                        getScreens();
                    } else {
                        showAlert(data.message || 'Failed to delete screen', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while deleting the screen', 'danger');
                });
            }
        }

        // Load screens on page load
        document.addEventListener('DOMContentLoaded', function() {
            getScreens();
        });
    </script>
</body>
</html>
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

        /* Tab Styling */
        .nav-tabs {
            border-bottom: 2px solid #e9ecef;
        }

        .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #6c757d;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: var(--primary-color);
            background-color: transparent;
        }

        .nav-tabs .nav-link.active {
            border-bottom: 3px solid var(--primary-color);
            color: var(--primary-color);
            background-color: transparent;
            font-weight: 600;
        }

        .tab-content {
            padding-top: 1.5rem;
        }

        .tab-pane {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
        
        .form-control.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
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
                <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#screenModal" onclick="openCreateScreenModal()">
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
                    <div id="modalError" class="alert alert-danger" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="modalErrorMessage"></span>
                    </div>
                    <form id="screenForm">
                        <input type="hidden" id="modalMode" value="create">
                        <input type="hidden" id="editScreenUrl" value="">
                        <input type="hidden" id="editSchoolId" value="">
                        <input type="hidden" id="editScreenId" value="">
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="modalSchoolId" class="form-label">School Name</label>
                                    <select name="school_id" id="modalSchoolId" class="form-select" required>
                                        <option value="">Select a school...</option>
                                        <?php foreach ($schools as $school_id => $school_name) { ?>
                                            <option value="<?php echo $school_id; ?>"><?php echo $school_name; ?></option>
                                        <?php } ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a school.</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Settings Section -->
                        <div id="settingsSection" style="display: none;">
                            <hr class="my-4">
                            
                            <!-- Tab Navigation -->
                            <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="screen-settings-tab" data-bs-toggle="tab" data-bs-target="#screen-settings" type="button" role="tab" aria-controls="screen-settings" aria-selected="true">
                                        <i class="fas fa-tv me-2"></i>Screen Settings
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="content-settings-tab" data-bs-toggle="tab" data-bs-target="#content-settings" type="button" role="tab" aria-controls="content-settings" aria-selected="false">
                                        <i class="fas fa-cog me-2"></i>Content Settings
                                    </button>
                                </li>
                            </ul>
                            
                            <!-- Tab Content -->
                            <div class="tab-content" id="settingsTabContent">
                                <!-- Screen Settings Tab -->
                                <div class="tab-pane fade show active" id="screen-settings" role="tabpanel" aria-labelledby="screen-settings-tab">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-0">Configure basic screen settings</p>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="modalScreenName" class="form-label">Screen Name</label>
                                                <input type="text" id="modalScreenName" name="screen_name" class="form-control" required 
                                                       placeholder="e.g., Main Lobby, Cafeteria, Library">
                                                <div class="invalid-feedback">Please enter a screen name.</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="modalScreenSize" class="form-label">Screen Size</label>
                                                <select name="screen_size" id="modalScreenSize" class="form-select" required>
                                                    <option value="">Select screen size...</option>
                                                    <option value="1920x1080">1920x1080</option>
                                                    <option value="1366x768">1366x768</option>
                                                    <option value="1280x720">1280x720</option>
                                                    <option value="1024x768">1024x768</option>
                                                    <option value="800x600">800x600</option>
                                                </select>
                                                <div class="invalid-feedback">Please select a screen size.</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="modalUrl" class="form-label">URL</label>
                                                <input type="text" id="modalUrl" name="url" class="form-control" placeholder="Auto-generated from screen name">
                                                <small class="form-text text-muted">Leave empty to auto-generate from screen name</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="modalPassword" class="form-label">Display PIN</label>
                                                <input type="number" id="modalPassword" name="password" class="form-control" required>
                                                <div class="invalid-feedback">Please enter a PIN.</div>
                                                <small class="form-text text-muted">PIN required to protect screen access</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Content Settings Tab -->
                                <div class="tab-pane fade" id="content-settings" role="tabpanel" aria-labelledby="content-settings-tab">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-0">Configure what content appears on this screen</p>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Promotions</label>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="showPromotions" name="show_promotions">
                                                    <label class="form-check-label" for="showPromotions">
                                                        Show promotions on this screen
                                                    </label>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label for="promotionsDays" class="form-label">Number of days to show promotions</label>
                                                    <select name="promotions_days" id="promotionsDays" class="form-select">
                                                        <option value="1">1 day</option>
                                                        <option value="2">2 days</option>
                                                        <option value="3">3 days</option>
                                                        <option value="4">4 days</option>
                                                        <option value="5">5 days</option>
                                                        <option value="6">6 days</option>
                                                        <option value="7">7 days</option>
                                                        <option value="8">8 days</option>
                                                        <option value="9">9 days</option>
                                                        <option value="10">10 days</option>
                                                        <option value="11">11 days</option>
                                                        <option value="12">12 days</option>
                                                        <option value="13">13 days</option>
                                                        <option value="14">14 days</option>
                                                        <option value="15">15 days</option>
                                                        <option value="16">16 days</option>
                                                        <option value="17">17 days</option>
                                                        <option value="18">18 days</option>
                                                        <option value="19">19 days</option>
                                                        <option value="20">20 days</option>
                                                        <option value="21">21 days</option>
                                                        <option value="22">22 days</option>
                                                        <option value="23">23 days</option>
                                                        <option value="24">24 days</option>
                                                        <option value="25">25 days</option>
                                                        <option value="26">26 days</option>
                                                        <option value="27">27 days</option>
                                                        <option value="28">28 days</option>
                                                        <option value="29">29 days</option>
                                                        <option value="30">30 days</option>
                                                        <option value="60">2 months</option>
                                                        <option value="90">3 months</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="promotionsGender" class="form-label">Gender filter</label>
                                                    <select name="promotions_gender" id="promotionsGender" class="form-select">
                                                        <option value="0">All</option>
                                                        <option value="M">Boys</option>
                                                        <option value="F">Girls</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Birthdays</label>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="showBirthdays" name="show_birthdays">
                                                    <label class="form-check-label" for="showBirthdays">
                                                        Show birthdays on this screen
                                                    </label>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label for="birthdaysDays" class="form-label">Number of days to show birthdays</label>
                                                    <select name="birthdays_days" id="birthdaysDays" class="form-select">
                                                        <option value="1">1 day</option>
                                                        <option value="2">2 days</option>
                                                        <option value="3">3 days</option>
                                                        <option value="4">4 days</option>
                                                        <option value="5">5 days</option>
                                                        <option value="6">6 days</option>
                                                        <option value="7">7 days</option>
                                                        <option value="8">8 days</option>
                                                        <option value="9">9 days</option>
                                                        <option value="10">10 days</option>
                                                        <option value="11">11 days</option>
                                                        <option value="12">12 days</option>
                                                        <option value="13">13 days</option>
                                                        <option value="14">14 days</option>
                                                        <option value="15">15 days</option>
                                                        <option value="16">16 days</option>
                                                        <option value="17">17 days</option>
                                                        <option value="18">18 days</option>
                                                        <option value="19">19 days</option>
                                                        <option value="20">20 days</option>
                                                        <option value="21">21 days</option>
                                                        <option value="22">22 days</option>
                                                        <option value="23">23 days</option>
                                                        <option value="24">24 days</option>
                                                        <option value="25">25 days</option>
                                                        <option value="26">26 days</option>
                                                        <option value="27">27 days</option>
                                                        <option value="28">28 days</option>
                                                        <option value="29">29 days</option>
                                                        <option value="30">30 days</option>
                                                        <option value="60">2 months</option>
                                                        <option value="90">3 months</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="birthdaysGender" class="form-label">Gender filter</label>
                                                    <select name="birthdays_gender" id="birthdaysGender" class="form-select">
                                                        <option value="0">All</option>
                                                        <option value="M">Boys</option>
                                                        <option value="F">Girls</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Announcements</label>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="showChidonAnnouncements" name="show_chidon_announcements">
                                                    <label class="form-check-label" for="showChidonAnnouncements">
                                                        Show Chidon Announcements from HQ
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="showChayoleiAnnouncements" name="show_chayolei_announcements">
                                                    <label class="form-check-label" for="showChayoleiAnnouncements">
                                                        Show Chayolei Announcements from HQ
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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

        function showModalError(message) {
            const errorDiv = document.getElementById('modalError');
            const errorMessage = document.getElementById('modalErrorMessage');
            errorMessage.textContent = message;
            errorDiv.style.display = 'block';
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }

        function clearModalError() {
            document.getElementById('modalError').style.display = 'none';
        }



        function openCreateScreenModal() {
            document.getElementById('modalMode').value = 'create';
            document.getElementById('modalTitle').textContent = 'Add New Screen';
            document.getElementById('saveButtonText').textContent = 'Create Screen';
            document.getElementById('screenForm').reset();
            document.getElementById('modalUrl').value = '';
            document.getElementById('modalSchoolId').disabled = false;
            document.getElementById('modalScreenName').disabled = false;
            urlManuallyEdited = false;
            clearModalError();
            document.getElementById('modalPassword').placeholder = ''; // Clear placeholder for new screens
            
            // Reset settings for create mode
            currentSettings = null;
            
            // Hide settings section in create mode
            document.getElementById('settingsSection').style.display = 'none';
            
            // Reset tabs to first tab when creating new screen
            const firstTab = document.querySelector('#settingsTabs .nav-link');
            const firstTabContent = document.querySelector('#settingsTabContent .tab-pane');
            if (firstTab && firstTabContent) {
                // Remove active class from all tabs and content
                document.querySelectorAll('#settingsTabs .nav-link').forEach(tab => tab.classList.remove('active'));
                document.querySelectorAll('#settingsTabContent .tab-pane').forEach(content => content.classList.remove('show', 'active'));
                
                // Add active class to first tab and content
                firstTab.classList.add('active');
                firstTabContent.classList.add('show', 'active');
            }
        }

        function openSettingsModal(schoolId, screenUrl, screenName, screenSize, screenPassword, screenId, settings) {
            document.getElementById('modalMode').value = 'edit';
            document.getElementById('modalTitle').textContent = 'Settings';
            document.getElementById('saveButtonText').textContent = 'Save Settings';
            document.getElementById('editScreenUrl').value = screenUrl;
            document.getElementById('editSchoolId').value = schoolId;
            document.getElementById('editScreenId').value = screenId;
            
            // Store settings in global variable
            currentSettings = {
                show_promotions: parseInt(settings.show_promotions) || 0,
                promotions_days: parseInt(settings.promotions_days) || 7,
                promotions_gender: settings.promotions_gender || '0',
                show_birthdays: parseInt(settings.show_birthdays) || 0,
                birthdays_days: parseInt(settings.birthdays_days) || 7,
                birthdays_gender: settings.birthdays_gender || '0',
                show_chidon: parseInt(settings.show_chidon) || 0,
                show_chayolei: parseInt(settings.show_chayolei) || 0
            };
            
            document.getElementById('modalSchoolId').value = schoolId;
            document.getElementById('modalScreenName').value = screenName;
            document.getElementById('modalScreenSize').value = screenSize;
            document.getElementById('modalUrl').value = screenUrl;
            document.getElementById('modalPassword').value = screenPassword || ''; // Use passed password
            document.getElementById('modalPassword').placeholder = '';
            
            // Disable school field in settings mode, but allow screen name to be edited
            document.getElementById('modalSchoolId').disabled = true;
            document.getElementById('modalScreenName').disabled = false;
            urlManuallyEdited = false;
            clearModalError();
            
            // Show settings section and populate with current settings
            document.getElementById('settingsSection').style.display = 'block';
            document.getElementById('showPromotions').checked = currentSettings.show_promotions == 1;
            document.getElementById('promotionsDays').value = currentSettings.promotions_days;
            document.getElementById('promotionsGender').value = currentSettings.promotions_gender;
            document.getElementById('showBirthdays').checked = currentSettings.show_birthdays == 1;
            document.getElementById('birthdaysDays').value = currentSettings.birthdays_days;
            document.getElementById('birthdaysGender').value = currentSettings.birthdays_gender;
            document.getElementById('showChidonAnnouncements').checked = currentSettings.show_chidon == 1;
            document.getElementById('showChayoleiAnnouncements').checked = currentSettings.show_chayolei == 1;
            
            // Reset tabs to first tab when opening settings
            const firstTab = document.querySelector('#settingsTabs .nav-link');
            const firstTabContent = document.querySelector('#settingsTabContent .tab-pane');
            if (firstTab && firstTabContent) {
                // Remove active class from all tabs and content
                document.querySelectorAll('#settingsTabs .nav-link').forEach(tab => tab.classList.remove('active'));
                document.querySelectorAll('#settingsTabContent .tab-pane').forEach(content => content.classList.remove('show', 'active'));
                
                // Add active class to first tab and content
                firstTab.classList.add('active');
                firstTabContent.classList.add('show', 'active');
            }
        }



        // Auto-generate URL when screen name changes
        let urlManuallyEdited = false;
        let currentSettings = null; // Store settings for current settings session
        
        document.getElementById('modalScreenName').addEventListener('input', function() {
            const screenName = this.value;
            const url = generateSlug(screenName);
            const urlField = document.getElementById('modalUrl');
            
            // Always update URL based on screen name (overwrites manual edits)
            urlField.value = url;
            urlManuallyEdited = false; // Reset the flag since we're auto-updating
        });
        
        // Track if URL is manually edited and convert characters in real-time
        document.getElementById('modalUrl').addEventListener('input', function() {
            urlManuallyEdited = true;
            
            // Convert the input value to proper URL format
            let value = this.value;
            value = value.toLowerCase(); // Convert to lowercase
            value = value.replace(/\s+/g, '-'); // Replace spaces with hyphens
            value = value.replace(/[^a-z0-9-]/g, ''); // Remove non-alphanumeric characters (except hyphens)
            value = value.replace(/-+/g, '-'); // Replace multiple hyphens with single hyphen
            value = value.replace(/^-+/, ''); // Remove leading hyphens only
            
            // Only remove trailing hyphens if they're not the result of a trailing space
            // (i.e., if the original input ended with a space, keep the hyphen)
            if (!this.value.endsWith(' ')) {
                value = value.replace(/-+$/, ''); // Remove trailing hyphens
            }
            
            // Update the field with the converted value
            this.value = value;
        });

        function saveScreen() {
            // Check if form is valid using Bootstrap's native validation
            const form = document.getElementById('screenForm');
            
            if (!form.checkValidity()) {
                // Trigger Bootstrap's validation display
                form.reportValidity();
                return;
            }
            
            const mode = document.getElementById('modalMode').value;
            const password = document.getElementById('modalPassword').value.trim();
            
            const formData = new FormData();
            
            formData.append('school_id', document.getElementById('modalSchoolId').value);
            formData.append('screen_name', document.getElementById('modalScreenName').value);
            formData.append('screen_size', document.getElementById('modalScreenSize').value);
            formData.append('url', document.getElementById('modalUrl').value);
            formData.append('password', password);
            
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
            
            const endpoint = mode === 'edit' ? 'ajax/saveScreenSettings.php' : 'ajax/addScreen.php';
            
            // If in edit mode, add all the screen data and settings to the form
            if (mode === 'edit') {
                formData.append('screen_id', document.getElementById('editScreenId').value);
                formData.append('screen_url', document.getElementById('editScreenUrl').value);
                formData.append('new_url', document.getElementById('modalUrl').value);
                formData.append('show_promotions', document.getElementById('showPromotions').checked ? 1 : 0);
                formData.append('promotions_days', document.getElementById('promotionsDays').value);
                formData.append('promotions_gender', document.getElementById('promotionsGender').value);
                formData.append('show_birthdays', document.getElementById('showBirthdays').checked ? 1 : 0);
                formData.append('birthdays_days', document.getElementById('birthdaysDays').value);
                formData.append('birthdays_gender', document.getElementById('birthdaysGender').value);
                formData.append('show_chidon', document.getElementById('showChidonAnnouncements').checked ? 1 : 0);
                formData.append('show_chayolei', document.getElementById('showChayoleiAnnouncements').checked ? 1 : 0);
            }
            
            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(mode === 'edit' ? 'Screen and settings updated successfully!' : 'Screen created successfully!', 'success');
                    
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
                    // Show error in modal instead of main page
                    showModalError(data.message || 'Failed to save screen');
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
                                            <button onclick="openSettingsModal('${school_id}', '${screen.url}', '${screen.screen_name}', '${screen.screen_size || ''}', '${screen.password || ''}', '${screen.screen_id}', {show_promotions: ${screen.show_promotions || 0}, promotions_days: ${screen.promotions_days || 7}, promotions_gender: '${screen.promotions_gender || '0'}', show_birthdays: ${screen.show_birthdays || 0}, birthdays_days: ${screen.birthdays_days || 7}, birthdays_gender: '${screen.birthdays_gender || '0'}', show_chidon: ${screen.show_chidon || 0}, show_chayolei: ${screen.show_chayolei || 0}})" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#screenModal">
                                                <i class="fas fa-cog me-1"></i>Settings
                                            </button>
                                            <button onclick="openImagesModal('${screen.screen_id}', '${school_id}')" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#imagesModal">
                                                <i class="fas fa-images me-1"></i>Images
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php include 'images_modal.html'; ?>
</body>
</html>
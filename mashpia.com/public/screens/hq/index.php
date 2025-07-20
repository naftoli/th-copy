<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../header.php';

// make sure it's a super admin
if ($admin_user['auth'] != 'super') {
    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements Admin</title>
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
            max-width: 900px;
            overflow: hidden;
        }
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2d3a6a 100%);
            color: white;
            padding: 2.5rem 2rem 2rem 2rem;
            text-align: center;
        }
        .hero-section h1 {
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }
        .hero-section p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 0;
        }
        .content-section {
            padding: 2.5rem 2rem;
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
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .form-group {
            margin-bottom: 1.2rem;
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
        .btn-edit {
            color: #fff;
            background: var(--warning-color);
            border: none;
            border-radius: 6px;
            padding: 0.4rem 1rem;
            font-size: 0.95rem;
            margin-right: 0.5rem;
            margin-bottom: 10px;
        }
        .btn-edit:hover {
            background: #e0a800;
        }
        .btn-delete {
            color: #fff;
            background: var(--danger-color);
            border: none;
            border-radius: 6px;
            padding: 0.4rem 1rem;
            font-size: 0.95rem;
        }
        .btn-delete:hover {
            background: #c82333;
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
        .announcement-image-preview {
            max-width: 100px;
            max-height: 60px;
            border-radius: 6px;
            margin-right: 10px;
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
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="hero-section">
            <h1><i class="fas fa-bullhorn me-2"></i>Announcements Admin</h1>
            <p>Create and manage announcements for all digital screens</p>
        </div>
        <div class="content-section">
            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4" id="announcementTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="create-tab" data-bs-toggle="tab" data-bs-target="#create" type="button" role="tab" aria-controls="create" aria-selected="true">
                        <i class="fas fa-plus-circle me-2"></i>Create Announcement
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="current-tab" data-bs-toggle="tab" data-bs-target="#current" type="button" role="tab" aria-controls="current" aria-selected="false">
                        <i class="fas fa-list me-2"></i>Current Announcements
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="announcementTabsContent">
                <div class="tab-pane fade show active" id="create" role="tabpanel" aria-labelledby="create-tab">
                    <div class="form-card">
                        <h2><i class="fas fa-plus-circle me-2"></i>Create Announcement</h2>
                        <form id="announcementForm" enctype="multipart/form-data">
                            <div class="form-group mb-3">
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-lg-6">
                                        <label for="announcementText" class="form-label">Announcement Text</label>
                                        <textarea id="announcementText" name="announcement_text" class="form-control" rows="2" placeholder="Enter announcement text..."></textarea>
                                    </div>
                                    <div class="col-12 col-lg-3 mt-2 mt-lg-0">
                                        <label for="announcementTextSize" class="form-label">Text Size</label>
                                        <select id="announcementTextSize" name="announcement_text_size" class="form-select w-100">
                                            <option value="16">16 px</option>
                                            <option value="18">18 px</option>
                                            <option value="20">20 px</option>
                                            <option value="22">22 px</option>
                                            <option value="24">24 px</option>
                                            <option value="26">26 px</option>
                                            <option value="28">28 px</option>
                                            <option value="30">30 px</option>
                                            <option value="32">32 px</option>
                                            <option value="34">34 px</option>
                                            <option value="36">36 px</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-lg-3 mt-2 mt-lg-0">
                                        <label for="announcementType" class="form-label">Type</label>
                                        <select id="announcementType" name="announcement_type" class="form-select w-100" required>
                                            <option value="">Select Type</option>
                                            <option value="chidon">Chidon</option>
                                            <option value="chayolei">Chayolei</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="announcementImage" class="form-label">Upload Image</label>
                                        <input type="file" id="announcementImage" name="announcement_image" class="form-control" accept="image/*">
                                        <img id="imagePreview" class="announcement-image-preview mt-2" style="display:none;" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="announcementImageSize" class="form-label">Image Size (Max Height in pixels)</label>
                                        <select id="announcementImageSize" name="announcement_image_size" class="form-select">
                                            <option value="50">50 px</option>
                                            <option value="100">100 px</option>
                                            <option value="150">150 px</option>
                                            <option value="200">200 px</option>
                                            <option value="250">250 px</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="fromDate" class="form-label">From Date</label>
                                        <input type="date" id="fromDate" name="from_date" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="toDate" class="form-label">To Date (inclusive)</label>
                                        <input type="date" id="toDate" name="to_date" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="limitSchool" class="form-label">Limit to School(s)</label>
                                        <select id="limitSchool" name="limit_school[]" class="form-select" multiple size="5">
                                        </select>
                                        <small class="form-text text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple schools</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="limitClass" class="form-label">Limit to Class(es)</label>
                                        <select id="limitClass" name="limit_class[]" class="form-select" multiple size="5" disabled>
                                        </select>
                                        <small class="form-text text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple classes</small>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i>Broadcast Announcement</button>
                        </form>
                    </div>
                    <div id="alertArea"></div>
                </div>
                <div class="tab-pane fade" id="current" role="tabpanel" aria-labelledby="current-tab">
                    <div class="form-card">
                        <h2><i class="fas fa-list me-2"></i>Current Announcements</h2>
                        <div id="announcementsList">
                            <!-- Announcements will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Announcement Modal -->
    <div class="modal fade" id="editAnnouncementModal" tabindex="-1" aria-labelledby="editAnnouncementModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editAnnouncementModalLabel"><i class="fas fa-edit me-2"></i>Edit Announcement</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="editAnnouncementLoading" style="display:none;text-align:center;padding:2rem;">
              <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
            </div>
            <form id="editAnnouncementForm" style="display:none;">
              <div class="mb-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-lg-6">
                        <label for="editAnnouncementText" class="form-label">Announcement Text</label>
                        <textarea id="editAnnouncementText" name="announcement_text" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12 col-lg-3 mt-2 mt-lg-0">
                        <label for="editAnnouncementTextSize" class="form-label">Text Size</label>
                        <select id="editAnnouncementTextSize" name="announcement_text_size" class="form-select w-100">
                            <option value="16">16 px</option>
                            <option value="18">18 px</option>
                            <option value="20">20 px</option>
                            <option value="22">22 px</option>
                            <option value="24">24 px</option>
                            <option value="26">26 px</option>
                            <option value="28">28 px</option>
                            <option value="30">30 px</option>
                            <option value="32">32 px</option>
                            <option value="34">34 px</option>
                            <option value="36">36 px</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-3 mt-2 mt-lg-0">
                        <label for="editAnnouncementType" class="form-label">Type</label>
                        <select id="editAnnouncementType" name="announcement_type" class="form-select w-100" required>
                            <option value="">Select Type</option>
                            <option value="chidon">Chidon</option>
                            <option value="chayolei">Chayolei</option>
                        </select>
                    </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="editAnnouncementImage" class="form-label">Image</label>
                    <input type="file" id="editAnnouncementImage" name="announcement_image" class="form-control" accept="image/*">
                    <div id="editImageContainer" class="mt-2" style="display:none;">
                      <img id="editImagePreview" class="announcement-image-preview" />
                      <button type="button" id="removeImageBtn" class="btn btn-sm btn-danger mt-1">
                        <i class="fas fa-trash"></i> Remove Image
                      </button>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="editAnnouncementImageSize" class="form-label">Image Size (Max Height in pixels)</label>
                    <select id="editAnnouncementImageSize" name="announcement_image_size" class="form-select">
                        <option value="50">50 px</option>
                        <option value="100">100 px</option>
                        <option value="150">150 px</option>
                        <option value="200">200 px</option>
                        <option value="250">250 px</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="editFromDate" class="form-label">From Date</label>
                    <input type="date" id="editFromDate" name="from_date" class="form-control" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="editToDate" class="form-label">To Date (inclusive)</label>
                    <input type="date" id="editToDate" name="to_date" class="form-control" required>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="editLimitSchool" class="form-label">Limit to School(s)</label>
                    <select id="editLimitSchool" name="limit_school[]" class="form-select" multiple size="5"></select>
                    <small class="form-text text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple schools</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="editLimitClass" class="form-label">Limit to Class(es)</label>
                    <select id="editLimitClass" name="limit_class[]" class="form-select" multiple size="5" disabled></select>
                    <small class="form-text text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple classes</small>
                  </div>
                </div>
              </div>
              <input type="hidden" id="editAnnouncementId" name="announcement_id">
              <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Changes</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Populate schools dropdown via AJAX
        function loadSchools(targetId = 'limitSchool', callback) {
            fetch('ajax/getSchools.php', {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            })
                .then(response => response.json())
                .then(data => {
                    const schoolSelect = document.getElementById(targetId);
                    schoolSelect.innerHTML = ''; // Clear existing options
                    // Add 'All Schools' option for edit modal
                    if (targetId === 'editLimitSchool') {
                        const allOpt = document.createElement('option');
                        allOpt.value = '';
                        allOpt.textContent = 'All Schools';
                        schoolSelect.appendChild(allOpt);
                    }
                    data.forEach(school => {
                        const opt = document.createElement('option');
                        opt.value = school.id;
                        opt.textContent = school.name;
                        schoolSelect.appendChild(opt);
                    });
                    if (callback) callback();
                })
                .catch(error => {
                    console.error('Error loading schools:', error);
                    if (callback) callback();
                });
        }
        // Populate classes dropdown via AJAX when a school is selected
        function loadClasses(schoolIds, targetId = 'limitClass', callback) {
            const classSelect = document.getElementById(targetId);
            classSelect.innerHTML = ''; // Clear existing options
            // Add 'All Classes' option for edit modal
            if (targetId === 'editLimitClass') {
                const allOpt = document.createElement('option');
                allOpt.value = '';
                allOpt.textContent = 'All Classes';
                classSelect.appendChild(allOpt);
            }
            if (!schoolIds || schoolIds.length === 0) {
                classSelect.disabled = true;
                if (callback) callback();
                return;
            }
            fetch('ajax/getClasses.php', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ school_id: schoolIds[0] })
            })
                .then(response => response.json())
                .then(data => {
                    data.forEach(cls => {
                        const opt = document.createElement('option');
                        opt.value = cls.id;
                        opt.textContent = cls.name;
                        classSelect.appendChild(opt);
                    });
                    classSelect.disabled = false;
                    if (callback) callback();
                })
                .catch(error => {
                    console.error('Error loading classes:', error);
                    if (callback) callback();
                });
        }
        document.addEventListener('DOMContentLoaded', function() {
            loadSchools('limitSchool');
            loadSchools('editLimitSchool');
            const schoolSelect = document.getElementById('limitSchool');
            const classSelect = document.getElementById('limitClass');
            const editSchoolSelect = document.getElementById('editLimitSchool');
            const editClassSelect = document.getElementById('editLimitClass');

            // Handle multi-select logic for Schools (Create)
            schoolSelect.addEventListener('change', function() {
                const selected = Array.from(this.selectedOptions).map(opt => opt.value);
                loadClasses(selected, 'limitClass');
                if (selected.length === 0) {
                    classSelect.innerHTML = '<option value="">All Classes</option>';
                    classSelect.disabled = true;
                }
            });
            classSelect.addEventListener('change', function() {
                const selected = Array.from(this.selectedOptions).map(opt => opt.value);
            });

            // Handle multi-select logic for Schools (Edit)
            editSchoolSelect.addEventListener('change', function() {
                const selected = Array.from(this.selectedOptions).map(opt => opt.value);
                loadClasses(selected, 'editLimitClass');
                if (selected.length === 0) {
                    editClassSelect.innerHTML = '<option value="">All Classes</option>';
                    editClassSelect.disabled = true;
                }
            });
            editClassSelect.addEventListener('change', function() {
                const selected = Array.from(this.selectedOptions).map(opt => opt.value);
            });
        });

        // Image preview
        document.getElementById('announcementImage').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    preview.src = evt.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        });

        // Handle form submit (AJAX placeholder)
        document.getElementById('announcementForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            fetch('ajax/saveAnnouncement.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showAlert(result.message || 'Announcement created successfully!', 'success');
                    form.reset();
                    document.getElementById('imagePreview').style.display = 'none';
                    document.getElementById('limitClass').innerHTML = '';
                    document.getElementById('limitClass').disabled = true;
                    loadAnnouncements();
                } else {
                    showAlert(result.message || 'Failed to create announcement.', 'danger');
                }
            })
            .catch(() => {
                showAlert('An error occurred while saving the announcement.', 'danger');
            });
        });

        // Edit form submit handler:
        document.getElementById('editAnnouncementForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            
            // Debug: Log form data
            console.log('Edit form submitted');
            console.log('Announcement ID:', formData.get('announcement_id'));
            console.log('Text:', formData.get('announcement_text'));
            console.log('Text Size:', formData.get('announcement_text_size'));
            console.log('Schools:', formData.getAll('limit_school[]'));
            console.log('Classes:', formData.getAll('limit_class[]'));
            
            // Debug: Check what's actually selected in the dropdowns
            const editSchoolSelect = document.getElementById('editLimitSchool');
            const editClassSelect = document.getElementById('editLimitClass');
            console.log('Selected schools:', Array.from(editSchoolSelect.selectedOptions).map(opt => opt.value));
            console.log('Selected classes:', Array.from(editClassSelect.selectedOptions).map(opt => opt.value));
            
            // If no new image is uploaded, send the existing image URL
            const existingImage = document.getElementById('editImagePreview').src;
            if (existingImage && existingImage !== '' && existingImage !== window.location.origin + '/screens/hq/' && !form.announcement_image.value) {
                formData.append('existing_image_url', existingImage);
            }
            
            // Reset remove image field if a new image is selected
            const removeImageField = document.getElementById('removeImage');
            if (removeImageField && form.announcement_image.value) {
                removeImageField.value = '0';
            }
            
            fetch('ajax/saveAnnouncement.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(result => {
                console.log('Response result:', result);
                if (result.success) {
                    showAlert(result.message || 'Announcement updated successfully!', 'success');
                    var modal = bootstrap.Modal.getInstance(document.getElementById('editAnnouncementModal'));
                    modal.hide();
                    loadAnnouncements();
                } else {
                    showAlert(result.message || 'Failed to update announcement.', 'danger');
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                showAlert('An error occurred while updating the announcement.', 'danger');
            });
        });

        function showAlert(message, type) {
            const alertArea = document.getElementById('alertArea');
            alertArea.innerHTML = `<div class="alert alert-${type} mt-3">${message}</div>`;
            setTimeout(() => { alertArea.innerHTML = ''; }, 4000);
        }

        // Load announcements from the database
        function loadAnnouncements() {
            fetch('ajax/getAnnouncements.php', {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            })
                .then(response => response.json())
                .then(result => {
                    if (!result.success) {
                        document.getElementById('announcementsList').innerHTML = `<div class='alert alert-danger'>${result.message}</div>`;
                        return;
                    }
                    const data = result.data;
                    if (!data || data.length === 0) {
                        document.getElementById('announcementsList').innerHTML = `<div class='alert alert-info'>No announcements created.</div>`;
                        return;
                    }
                    let html = '<table class="table table-striped"><thead><tr><th>Text</th><th>Type</th><th>From Date</th><th>To Date</th><th>Image</th><th>Image Size</th><th>Schools</th><th>Classes</th><th>Actions</th></tr></thead><tbody>';
                    data.forEach(a => {
                        html += `<tr>
                            <td>${a.text || '-'}</td>
                            <td>${a.type || '-'}</td>
                            <td>${a.from_date || '-'}</td>
                            <td>${a.to_date || '-'}</td>
                            <td>${a.image ? `<img src="${a.image}" class="announcement-image-preview"/>` : '-'}</td>
                            <td>${a.image_size || '-'}</td>
                            <td>${a.limit_to_schools || '-'}</td>
                            <td>${a.limit_to_classes || '-'}</td>
                            <td>
                                <button class="btn btn-edit btn-sm" onclick="editAnnouncement(${a.screen_announcement_id})"><i class="fas fa-edit"></i> Edit</button>
                                <button class="btn btn-delete btn-sm" onclick="deleteAnnouncement(${a.screen_announcement_id})"><i class="fas fa-trash"></i> Delete</button>
                            </td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    document.getElementById('announcementsList').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('announcementsList').innerHTML = `<div class='alert alert-danger'>Failed to load announcements.</div>`;
                });
        }
        loadAnnouncements();

        // Refactor editAnnouncement JS
        window.editAnnouncement = function(id) {
            document.getElementById('editAnnouncementLoading').style.display = '';
            document.getElementById('editAnnouncementForm').style.display = 'none';
            var modal = new bootstrap.Modal(document.getElementById('editAnnouncementModal'));
            modal.show();
            fetch('ajax/getAnnouncements.php', {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(result => {
                if (!result.success) return;
                const a = result.data.find(x => x.screen_announcement_id == id);
                if (!a) return;
                document.getElementById('editAnnouncementId').value = a.screen_announcement_id;
                document.getElementById('editAnnouncementText').value = a.text || '';
                document.getElementById('editAnnouncementTextSize').value = a.text_size || '18';
                document.getElementById('editAnnouncementType').value = a.type || '';
                document.getElementById('editFromDate').value = a.from_date || '';
                document.getElementById('editToDate').value = a.to_date || '';
                // Schools
                loadSchools('editLimitSchool', function() {
                    const editSchoolSelect = document.getElementById('editLimitSchool');
                    const schoolsArr = a.limit_to_schools ? a.limit_to_schools.split(',') : [];
                    
                    // Clear all selections first
                    for (let i = 0; i < editSchoolSelect.options.length; i++) {
                        editSchoolSelect.options[i].selected = false;
                    }
                    
                    if (!a.limit_to_schools || a.limit_to_schools === '') {
                        // Select 'All Schools' (first option with empty value)
                        editSchoolSelect.options[0].selected = true;
                    } else {
                        // Select specific schools
                        for (let i = 0; i < editSchoolSelect.options.length; i++) {
                            editSchoolSelect.options[i].selected = schoolsArr.includes(editSchoolSelect.options[i].value);
                        }
                    }
                    
                    // Classes
                    loadClasses(schoolsArr, 'editLimitClass', function() {
                        const editClassSelect = document.getElementById('editLimitClass');
                        const classesArr = a.limit_to_classes ? a.limit_to_classes.split(',') : [];
                        
                        // Clear all selections first
                        for (let i = 0; i < editClassSelect.options.length; i++) {
                            editClassSelect.options[i].selected = false;
                        }
                        
                        if (!a.limit_to_classes || a.limit_to_classes === '') {
                            // Select 'All Classes' (first option with empty value)
                            editClassSelect.options[0].selected = true;
                        } else {
                            // Select specific classes
                            for (let i = 0; i < editClassSelect.options.length; i++) {
                                editClassSelect.options[i].selected = classesArr.includes(editClassSelect.options[i].value);
                            }
                        }
                        
                        document.getElementById('editAnnouncementLoading').style.display = 'none';
                        document.getElementById('editAnnouncementForm').style.display = '';
                    });
                });
                if (a.image) {
                    document.getElementById('editImagePreview').src = a.image;
                    document.getElementById('editImageContainer').style.display = 'block';
                } else {
                    document.getElementById('editImagePreview').src = '';
                    document.getElementById('editImageContainer').style.display = 'none';
                }
            });
        };

        // Add logic to deselect all others if 'All Schools' or 'All Classes' is selected in edit modal
        const editSchoolSelect = document.getElementById('editLimitSchool');
        const editClassSelect = document.getElementById('editLimitClass');
        if (editSchoolSelect) {
            editSchoolSelect.addEventListener('change', function() {
                const selected = Array.from(this.selectedOptions).map(opt => opt.value);
                if (selected.includes('')) {
                    // If "All Schools" is selected, deselect everything else
                    for (let i = 0; i < this.options.length; i++) {
                        if (this.options[i].value !== '') this.options[i].selected = false;
                    }
                    // Make sure "All Schools" stays selected
                    this.options[0].selected = true;
                } else {
                    // If specific schools are selected, deselect "All Schools"
                    this.options[0].selected = false;
                }
            });
        }
        if (editClassSelect) {
            editClassSelect.addEventListener('change', function() {
                const selected = Array.from(this.selectedOptions).map(opt => opt.value);
                if (selected.includes('')) {
                    // If "All Classes" is selected, deselect everything else
                    for (let i = 0; i < this.options.length; i++) {
                        if (this.options[i].value !== '') this.options[i].selected = false;
                    }
                    // Make sure "All Classes" stays selected
                    this.options[0].selected = true;
                } else {
                    // If specific classes are selected, deselect "All Classes"
                    this.options[0].selected = false;
                }
            });
        }

        // Handle remove image button in edit modal
        document.getElementById('removeImageBtn').addEventListener('click', function() {
            document.getElementById('editImagePreview').src = '';
            document.getElementById('editImageContainer').style.display = 'none';
            document.getElementById('editAnnouncementImage').value = '';
            // Add a hidden field to indicate image should be removed
            let removeImageField = document.getElementById('removeImage');
            if (!removeImageField) {
                removeImageField = document.createElement('input');
                removeImageField.type = 'hidden';
                removeImageField.id = 'removeImage';
                removeImageField.name = 'remove_image';
                removeImageField.value = '1';
                document.getElementById('editAnnouncementForm').appendChild(removeImageField);
            } else {
                removeImageField.value = '1';
            }
        });

        // TODO: Update deleteAnnouncement to call a real delete endpoint and reload announcements
        window.deleteAnnouncement = function(id) {
            if (confirm('Are you sure you want to delete this announcement?')) {
                fetch('ajax/deleteAnnouncement.php', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: new URLSearchParams({ screen_announcement_id: id })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showAlert(result.message || 'Announcement deleted successfully.', 'success');
                        loadAnnouncements();
                    } else {
                        showAlert(result.message || 'Failed to delete announcement.', 'danger');
                    }
                })
                .catch(() => {
                    showAlert('An error occurred while deleting the announcement.', 'danger');
                });
            }
        };
    </script>
</body>
</html>

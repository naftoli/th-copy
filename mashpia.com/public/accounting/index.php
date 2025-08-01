<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../header.php';

if ($admin_user['auth'] != 'super') {
    echo "Not authorized";
    exit;
}

require_once '../class.adminSchools.php';
$adminSchools = new adminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $adminSchools->getSchools();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting Report</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
            --border-color: #e9ecef;
            --card-shadow: 0 2px 8px rgba(0,0,0,0.08);
            --card-shadow-hover: 0 4px 16px rgba(0,0,0,0.12);
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            color: var(--primary-color);
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 300;
            margin-bottom: 0.5rem;
        }
        
        .page-header p {
            font-size: 1.1rem;
            opacity: 0.7;
            margin: 0;
            color: #6c757d;
        }
        
        .sections-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .card-module {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            border: none;
            overflow: hidden;
            transition: all 0.3s ease;
            height: fit-content;
        }
        
        .card-module:hover {
            box-shadow: var(--card-shadow-hover);
            transform: translateY(-2px);
        }
        
        .card-header-module {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.25rem 1.5rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header-module:hover {
            background: linear-gradient(135deg, #34495e, #2980b9);
        }
        
        .card-header-module h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .toggle-icon {
            transition: transform 0.3s ease;
            font-size: 1.2rem;
        }
        
        .card-header-module.collapsed .toggle-icon {
            transform: rotate(-90deg);
        }
        
        .card-body-module {
            padding: 1.5rem;
            transition: all 0.3s ease;
            max-height: 1000px;
            opacity: 1;
            overflow: hidden;
        }
        
        .card-body-module.collapsed {
            max-height: 0;
            opacity: 0;
            padding-top: 0;
            padding-bottom: 0;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }
        
        .form-select {
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background-color: #fafbfc;
        }
        
        .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.15);
            background-color: white;
        }
        
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .checkbox-grid-two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .checkbox-item {
            background: #f8f9fa;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .checkbox-item:hover {
            background: #e3f2fd;
            border-color: var(--secondary-color);
            transform: translateY(-1px);
        }
        
        .checkbox-item input[type="checkbox"]:checked + label,
        .checkbox-item input[type="radio"]:checked + label {
            color: var(--secondary-color);
            font-weight: 600;
        }
        
        .checkbox-item input[type="checkbox"]:checked,
        .checkbox-item input[type="radio"]:checked {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .checkbox-item label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
            color: var(--primary-color);
            transition: all 0.3s ease;
            flex: 1;
            user-select: none;
        }
        
        .checkbox-item input[type="checkbox"],
        .checkbox-item input[type="radio"] {
            cursor: pointer;
            margin: 0;
            flex-shrink: 0;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .btn-module {
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-expand {
            background: linear-gradient(135deg, var(--success-color), #229954);
            color: white;
        }
        
        .btn-expand:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
            color: white;
        }
        
        .btn-collapse {
            background: linear-gradient(135deg, var(--warning-color), #e67e22);
            color: white;
        }
        
        .btn-collapse:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);
            color: white;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--success-color), #229954);
            color: white;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
            color: white;
        }
        
        .info-badge {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 1rem;
            color: #1565c0;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .form-text {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem 0.5rem;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .sections-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .checkbox-grid {
                grid-template-columns: 1fr;
            }
            
            .checkbox-grid-two-columns {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-module {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="page-header">
            <h1><i class="bi bi-calculator"></i> Accounting Report Generator</h1>
            <p>Generate comprehensive accounting reports.</p>
        </div>
        
        <div class="info-badge">
            <i class="bi bi-info-circle"></i>
            <div>
                <strong>Instructions:</strong> Select schools and options to include in your report. 
                Use Ctrl (or Cmd on Mac) to select multiple schools.
            </div>
        </div>
        
        <div class="action-buttons">
            <button type="button" class="btn-module btn-expand" id="expandAllBtn">
                <i class="bi bi-arrows-expand"></i> Expand All
            </button>
            <button type="button" class="btn-module btn-collapse" id="collapseAllBtn">
                <i class="bi bi-arrows-collapse"></i> Collapse All
            </button>
        </div>
        
        <form action="create_report.php" method="post">
            <div class="sections-grid">
                <div class="card-module">
                    <div class="card-header-module" data-target="schools-section">
                        <h5><i class="bi bi-building"></i> Select Schools</h5>
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </div>
                    <div class="card-body-module" id="schools-section">
                        <label for="school_select" class="form-label">Choose Schools for Report</label>
                        <select name="school_id[]" id="school_select" class="form-select" multiple required size="6">
                            <option value="0">All Schools</option>
                            <?php 
                            if (is_array($schools)) {
                                foreach ($schools as $school_id => $school_name) { 
                                    if (empty($school_name)) continue;
                                    echo '<option value="' . htmlspecialchars($school_id) . '">' . htmlspecialchars($school_name) . '</option>';
                                }
                            } else {
                                echo '<option value="">No schools available</option>';
                            }
                            ?>
                        </select>
                        <div class="form-text">Hold Ctrl (or Cmd on Mac) to select multiple schools</div>
                    </div>
                </div>

                <div class="card-module">
                    <div class="card-header-module" data-target="report-type-section">
                        <h5><i class="bi bi-info-circle"></i> Report Type</h5>
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </div>
                    <div class="card-body-module" id="report-type-section">
                        <div id="report_types" class="checkbox-grid-two-columns"></div>
                    </div>
                </div>
            </div>

            <div class="sections-grid">
                <div class="card-module">
                    <div class="card-header-module" data-target="base-options-section">
                        <h5><i class="bi bi-gear"></i> Base Options</h5>
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </div>
                    <div class="card-body-module" id="base-options-section">
                        <div id="base_options" class="checkbox-grid-two-columns"></div>
                    </div>
                </div>

                <div class="card-module">
                    <div class="card-header-module" data-target="soldier-options-section">
                        <h5><i class="bi bi-gear"></i> Soldier Options</h5>
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </div>
                    <div class="card-body-module" id="soldier-options-section">
                        <div id="soldier_options" class="checkbox-grid-two-columns"></div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn-submit">
                    <i class="bi bi-file-earmark-text"></i> Generate Report
                </button>
            </div>
        </form>
    </div>

    <script>
        // utility function to build a checkbox group
        function build_checkbox_group(options, name, type) {
            let html = '';
            for (const [key, value] of Object.entries(options)) {
                if (type == 'radio') {
                    html += `
                        <div class="checkbox-item">
                            <input class="form-check-input" type="radio" name="${name}" value="${key}" id="${name}_${key}">
                            <label for="${name}_${key}">${value}</label>
                        </div>
                    `;
                } else if (type == 'checkbox') {
                    html += `
                        <div class="checkbox-item">
                            <input class="form-check-input" type="checkbox" name="${name}[]" value="${key}" id="${name}_${key}">
                            <label for="${name}_${key}">${value}</label>
                        </div>
                    `;
                }
            }
            document.getElementById(name).innerHTML = html;
        }

        const report_types = {
            'base' : 'Base Report',
            'soldier' : 'Soldier Report'
        }
        
        const base_options = {
            'school_id': 'School ID',
            'school_number': 'School Number',
            'chayolei_fee': 'Chayolei Base Fee',
            'chayolei_paid': 'Chayolei Base Paid',
            'chidon_fee': 'Chidon Base Fee',
            'chidon_paid': 'Chidon Base Paid',
            'prior_balance': 'Prior Balance',
            'prior_balance_paid': 'Prior Balance Paid',
            'registration_type': 'Registration Type',
            'base_paid': 'Base Paid',
            'base_discount': 'Base Discount',
            'base_balance': 'Base Balance',
        }

        const soldier_options = {
            'school_id': 'School ID',
            'school_number': 'School Number',
            'registration_type': 'Registration Type',
            'user_id': 'User ID',
            'user_serial': 'User Serial',
            'user_name': 'User Name',
            'grade': 'Grade',
            'date_registered': 'Date Registered',
            'soldier_reg_fee': 'Registration Fee',
            'soldier_reg_paid': 'Registration Paid',
            'soldier_discount': 'Discount',
            'soldier_balance': 'Balance'
        }
        
        function toggleSection(header) {
            const targetId = header.getAttribute('data-target');
            const content = document.getElementById(targetId);
            const isCollapsed = content.classList.contains('collapsed');
            
            if (isCollapsed) {
                content.classList.remove('collapsed');
                header.classList.remove('collapsed');
            } else {
                content.classList.add('collapsed');
                header.classList.add('collapsed');
            }
        }
        
        function expandCollapsedSections() {
            const sections = document.querySelectorAll('.card-body-module');
            const headers = document.querySelectorAll('.card-header-module');
            
            sections.forEach(section => {
                if (section.classList.contains('collapsed')) {
                    section.classList.remove('collapsed');
                }
            });
            
            headers.forEach(header => {
                if (header.classList.contains('collapsed')) {
                    header.classList.remove('collapsed');
                }
            });
        }
        
        function collapseAllSections() {
            const sections = document.querySelectorAll('.card-body-module');
            const headers = document.querySelectorAll('.card-header-module');
            
            sections.forEach(section => {
                section.classList.add('collapsed');
            });
            
            headers.forEach(header => {
                header.classList.add('collapsed');
            });
        }
        
        // Initialize all sections
        document.addEventListener('DOMContentLoaded', function() {
            build_checkbox_group(report_types, 'report_types', 'radio');
            build_checkbox_group(base_options, 'base_options', 'checkbox');
            build_checkbox_group(soldier_options, 'soldier_options', 'checkbox');
            
            // Add click event listeners to section headers
            document.querySelectorAll('.card-header-module').forEach(header => {
                header.addEventListener('click', function() {
                    toggleSection(this);
                });
            });
            
            // Add click event listeners to expand/collapse buttons
            document.getElementById('expandAllBtn').addEventListener('click', expandCollapsedSections);
            document.getElementById('collapseAllBtn').addEventListener('click', collapseAllSections);
            
            // close all sections except the first one
            document.querySelectorAll('.card-header-module').forEach((header, index) => {
                if (index > 1) {
                    toggleSection(header);
                }
            });
        });
    </script>
</body>
</html>
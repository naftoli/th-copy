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

require_once '../class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Accounting Report Generator</h1>
        <p>Generate comprehensive accounting reports.</p>
        
        <form action="create_report.php" method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="year" class="form-label">Year</label>
                        <select name="year" id="year" class="form-select">
                            <?php
                            for ($yr = $year; $yr >= 5780; $yr--) {
                                echo '<option value="' . $yr . '">' . $yr . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">Select Schools</h5>
                        </div>
                        <div class="card-body">
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

                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">Report Type</h5>
                        </div>
                        <div class="card-body">
                            <div id="report_type" class="row"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div id="base_options_section" class="card mb-3" style="display: none;">
                        <div class="card-header">
                            <h5 class="mb-0">Base Options (For Base Report)</h5>
                        </div>
                        <div class="card-body">
                            <div id="base_options" class="row"></div>
                        </div>
                    </div>

                    <div id="soldier_options_section" class="card mb-3" style="display: none;">
                        <div class="card-header">
                            <h5 class="mb-0">Soldier Options (For Soldier Report)</h5>
                        </div>
                        <div class="card-body">
                            <div id="soldier_options" class="row"></div>
                        </div>
                    </div>

                    <div id="details_options_section" class="card mb-3" style="display: none;">
                        <div class="card-header">
                            <h5 class="mb-0">Details Options (For Details Report)</h5>
                        </div>
                        <div class="card-body">
                            <div id="details_options" class="row"></div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Generate Report</button>
        </form>
    </div>

    <script>
        // utility function to build a checkbox group
        function build_checkbox_group(options, name, type) {
            let html = '';
            for (const [key, value] of Object.entries(options)) {
                if (type == 'radio') {
                    html += `
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="${name}" value="${key}" id="${name}_${key}">
                                <label class="form-check-label" for="${name}_${key}">${value}</label>
                            </div>
                        </div>
                    `;
                } else if (type == 'checkbox') {
                    html += `
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="${name}[]" value="${key}" id="${name}_${key}">
                                <label class="form-check-label" for="${name}_${key}">${value}</label>
                            </div>
                        </div>
                    `;
                }
            }
            document.getElementById(name).innerHTML = html;
        }

        const report_types = {
            'base' : 'Base Charges Report',
            'soldier' : 'Soldier Charges Report', 
            'summary' : 'Summary Transactions Report', 
            'details' : 'Details Transactions Report',
        }
        
        const base_options = {
            'base_type': 'Registration Type',
            'school_number': 'Base Number',
            'school_name': 'Base Name',
            'date_registered': 'Date Registered',
            'chayolei_fee': 'Chayolei Fee',
            'chayolei_paid': 'Chayolei Fee Paid',
            'chidon_fee': 'Chidon Fee',
            'chidon_paid': 'Chidon Fee Paid',
            'prior_balance': 'Prior Balance',
            'prior_balance_paid': 'Prior Balance Paid',
            'total_owed': 'Total Owed',
            'base_discount': 'Base Discount',
            'total_paid': 'Total Paid',
            'total_balance': 'Base Balance',
            'registered_chayolim': 'Soldiers Registered'
        }

        const soldier_options = {
            'reg_type': 'Registration Type',
            'school_number': 'Base Number',
            'school_name': 'Base Name',
            'user_serial': 'User Serial',
            'grade': 'Grade',
            'user_name': 'User Name',
            'date_registered': 'Date Registered',
            'reg_fee': 'Registration Fee',
            'reg_paid': 'Registration Paid',
            'soldier_discount': 'Coupon Discount',
            'total_balance': 'Balance'
        }

        const details_options = {
            'reg_type': 'Registration Type',
            'school_number': 'Base Number',
            'school_name': 'Base Name',
            'user_serial': 'User Serial',
            'user_name': 'User Name',
            'type': 'Type',
            'code': 'Code',
            'reg_date': 'Registration Date',
            'reg_amount': 'Amount',
            'refunded': 'Refunded'
        }

        function showOptionsForReportType(reportType) {
            // Hide all option sections first
            document.getElementById('base_options_section').style.display = 'none';
            document.getElementById('soldier_options_section').style.display = 'none';
            document.getElementById('details_options_section').style.display = 'none';

            // Show the relevant section based on report type
            if (reportType === 'base') {
                document.getElementById('base_options_section').style.display = 'block';
            } else if (reportType === 'soldier') {
                document.getElementById('soldier_options_section').style.display = 'block';
            } else if (reportType === 'details') {
                document.getElementById('details_options_section').style.display = 'block';
            }
            // summary report type doesn't need options
        }
        
        // Initialize all sections
        document.addEventListener('DOMContentLoaded', function() {
            build_checkbox_group(report_types, 'report_type', 'radio');
            build_checkbox_group(base_options, 'base_options', 'checkbox');
            build_checkbox_group(soldier_options, 'soldier_options', 'checkbox');
            build_checkbox_group(details_options, 'details_options', 'checkbox');

            // Add event listeners for report type radio buttons
            document.querySelectorAll('input[name="report_type"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    showOptionsForReportType(this.value);
                });
            });

            // Add form validation
            document.querySelector('form').addEventListener('submit', function(e) {
                const selectedReportType = document.querySelector('input[name="report_type"]:checked');
                
                if (!selectedReportType) {
                    e.preventDefault();
                    alert('Please select a report type (Base Report, Soldier Report, or Details Report)');
                    return false;
                }
                
                // Check if at least one option is selected for the chosen report type (except summary)
                const reportType = selectedReportType.value;
                if (reportType !== 'summary') {
                    const optionsContainer = document.getElementById(reportType + '_options');
                    const selectedOptions = optionsContainer.querySelectorAll('input[type="checkbox"]:checked');
                    
                    if (selectedOptions.length === 0) {
                        e.preventDefault();
                        alert('Please select at least one option for the ' + report_types[reportType] + ' report');
                        return false;
                    }
                }
                
                // Check if at least one school is selected
                const schoolSelect = document.getElementById('school_select');
                const selectedSchools = Array.from(schoolSelect.selectedOptions);
                if (selectedSchools.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one school');
                    return false;
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
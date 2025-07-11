<?
$admin_auth = array('school'); 
require('db.php');

require_once 'medalsEarned.class.php';
$m = new MedalsEarned;
$report = $m->getReport();
$medals = $m->getMedals();
$subjects = $m->getSubjects();

// Get available years from the report array
$available_years = array_keys($report);
rsort($available_years); // Sort years in descending order

// Get selected year from POST or default to current year
$selected_year = isset($_POST['year']) ? $_POST['year'] : date('Y');

// Get the data for the selected year
$selected_year_data = isset($report[$selected_year]) ? $report[$selected_year] : array();

// Convert report data to JSON for JavaScript
$report_json = json_encode($report);
$medals_json = json_encode($medals);
$subjects_json = json_encode($subjects);

//echo "<pre>"; print_r($report); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medals Earned Summary</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .year-selector {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        .year-selector select {
            padding: 0.5rem 1rem;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 1rem;
            background-color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .year-selector select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .table th {
            border: none;
            font-weight: 600;
            padding: 1rem;
        }
        .table td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .year-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 600;
            font-size: 1.2rem;
        }
        .subject-row {
            background-color: #f8f9fa;
            font-weight: 500;
        }
        .medal-count {
            font-weight: 600;
            color: #667eea;
        }
        .no-data {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
            font-style: italic;
        }
        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        .loading {
            text-align: center;
            padding: 2rem;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="fas fa-medal me-3"></i>Medals Earned Summary
                </h1>
                
                <!-- Year Selector -->
                <div class="year-selector">
                    <label for="year" class="form-label fw-bold me-2">
                        <i class="fas fa-calendar-alt me-1"></i>Select Year:
                    </label>
                    <select name="year" id="year" class="form-select d-inline-block w-auto">
                        <option value="0">Choose Year</option>
                        <?php foreach ($available_years as $year): ?>
                            <option value="<?php echo $year; ?>" <?php echo ($year == $selected_year) ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Data Display Area -->
                <div id="data-display">
                    <?php if (!empty($selected_year_data)): ?>
                        <div class="year-section mb-4 fade-in">
                            <div class="year-header">
                                <i class="fas fa-calendar-alt me-2"></i>Medals Earned in <?php echo $selected_year; ?>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-book me-2"></i>Subject</th>
                                            <?php foreach ($medals as $medal): ?>
                                                <th class="text-center">
                                                    <i class="fas fa-medal me-1"></i><?php echo htmlspecialchars($medal); ?>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subjects as $subject => $id): ?>
                                            <tr class="subject-row">
                                                <td><strong><?php echo htmlspecialchars($subject); ?></strong></td>
                                                <?php foreach ($medals as $ord => $medal): ?>
                                                    <td class="text-center medal-count">
                                                        <?php 
                                                        if (is_array($id)) {
                                                            $s = implode(',', $id);
                                                        } else {
                                                            $s = (string)$id;
                                                        }
                                                        echo isset($selected_year_data[$s][$ord]) ? $selected_year_data[$s][$ord] : 0;
                                                        ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-data fade-in">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h4>No Data Available</h4>
                            <p>No medals data is available for the selected year (<?php echo $selected_year; ?>).</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Store the report data globally
        const reportData = <?php echo $report_json; ?>;
        const medalsData = <?php echo $medals_json; ?>;
        const subjectsData = <?php echo $subjects_json; ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            const yearSelect = document.getElementById('year');
            const dataDisplay = document.getElementById('data-display');
            
            // Function to update the display
            function updateDisplay(year) {
                // Show loading state
                dataDisplay.innerHTML = `
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Loading data for ${year}...</p>
                    </div>
                `;
                
                // Simulate a small delay for better UX
                setTimeout(() => {
                    const yearData = reportData[year] || {};
                    
                    if (Object.keys(yearData).length > 0) {
                        // Build the table header
                        let tableHeader = '<tr><th><i class="fas fa-book me-2"></i>Subject</th>';
                        for (const medal of Object.values(medalsData)) {
                            tableHeader += `<th class="text-center"><i class="fas fa-medal me-1"></i>${medal}</th>`;
                        }
                        tableHeader += '</tr>';
                        
                        // Build the table body
                        let tableBody = '';
                        for (const [subject, id] of Object.entries(subjectsData)) {
                            tableBody += '<tr class="subject-row"><td><strong>' + subject + '</strong></td>';
                            
                            for (const [ord, medal] of Object.entries(medalsData)) {
                                let s;
                                if (Array.isArray(id)) {
                                    s = id.join(',');
                                } else {
                                    s = String(id);
                                }
                                
                                const count = yearData[s] && yearData[s][ord] ? yearData[s][ord] : 0;
                                tableBody += `<td class="text-center medal-count">${count}</td>`;
                            }
                            tableBody += '</tr>';
                        }
                        
                        dataDisplay.innerHTML = `
                            <div class="year-section mb-4 fade-in">
                                <div class="year-header">
                                    <i class="fas fa-calendar-alt me-2"></i>Medals Earned in ${year}
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            ${tableHeader}
                                        </thead>
                                        <tbody>
                                            ${tableBody}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        `;
                    } else {
                        dataDisplay.innerHTML = `
                            <div class="no-data fade-in">
                                <i class="fas fa-exclamation-triangle"></i>
                                <h4>No Data Available</h4>
                                <p>No medals data is available for the selected year (${year}).</p>
                            </div>
                        `;
                    }
                }, 300);
            }
            
            // Event listener for year selection
            yearSelect.addEventListener('change', function() {
                const selectedYear = this.value;
                if (selectedYear === '0') {
                    dataDisplay.innerHTML = `
                        <div class="no-data fade-in">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h4>No Data Available</h4>
                            <p>Please select a year to view the medals earned.</p>
                        </div>
                    `;
                    return;
                }
                updateDisplay(selectedYear);
            });
        });
    </script>
</body>
</html>
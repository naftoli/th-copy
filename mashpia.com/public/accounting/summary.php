<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../header.php';
require_once '../api/header/db.php';
require_once '../chidon_shipping/class.chidonShipping.php';

if ($admin_user['auth'] != 'super') {
    echo "Not authorized";
    exit;
}

$sql = "SELECT * FROM registration_charges WHERE year = :year";
if ($_GET['schools'] > 0) {
    $sql .= " AND school_id IN (" . $_GET['schools'] . ")";
}
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([
    ':year' => $_GET['year']
]);
$registration_charges = $stmt->fetchAll(PDO::FETCH_ASSOC);

$charges = [];
$summary = [];
$grand_total = 0;
foreach ($registration_charges as $charge) {
    $charges[$charge['type']][] = $charge;
    if (!isset($summary[$charge['type']])) {
        $summary[$charge['type']] = floatval($charge['amount']);
    } else {
        $summary[$charge['type']] += floatval($charge['amount']);
    }
    $grand_total += floatval($charge['amount']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary Charges Report</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
            --text-muted: #6c757d;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 2rem auto;
            padding: 2rem;
            max-width: 95%;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .page-header h1 {
            margin: 0;
            font-weight: 600;
            font-size: 2.5rem;
        }

        .page-header .subtitle {
            margin-top: 0.5rem;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: linear-gradient(135deg, var(--success-color), #229954);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }

        .summary-card h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .summary-card .amount {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: var(--light-bg);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .table tbody td {
            padding: 1rem;
            border-color: var(--border-color);
            vertical-align: middle;
        }

        .table tbody td:first-child {
            font-weight: 600;
            color: var(--primary-color);
        }

        .number-cell {
            text-align: right;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: var(--success-color);
        }

        .grand-total-row {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
            color: white;
            font-weight: 700;
        }

        .grand-total-row td {
            color: white !important;
        }

        .dataTables_wrapper {
            padding: 1rem;
        }

        .dataTables_filter input {
            border-radius: 20px;
            border: 2px solid var(--border-color);
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .dataTables_filter input:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            outline: none;
        }

        .dataTables_length select {
            border-radius: 20px;
            border: 2px solid var(--border-color);
            padding: 0.5rem 1rem;
            min-width: 80px;
            width: auto;
        }

        .dataTables_info {
            color: var(--text-muted);
            font-style: italic;
        }

        .dataTables_paginate .paginate_button {
            border-radius: 0;
            border: none;
            margin: 0 0.2rem;
            transition: all 0.3s ease;
            background: white !important;
            color: var(--primary-color) !important;
            padding: 0.5rem 1rem;
            text-decoration: none;
            display: inline-block;
            min-width: 40px;
            text-align: center;
        }

        .dataTables_paginate .paginate_button:hover {
            background: var(--secondary-color) !important;
            border-color: var(--secondary-color);
            color: white !important;
            text-decoration: none;
        }

        .dataTables_paginate .paginate_button.current {
            background: var(--secondary-color) !important;
            border-color: var(--secondary-color);
            color: white !important;
            text-decoration: none;
        }

        .dataTables_paginate .paginate_button.disabled {
            background: var(--light-bg) !important;
            border-color: var(--border-color);
            color: var(--text-muted) !important;
            cursor: not-allowed;
        }

        .dataTables_paginate .paginate_button.disabled:hover {
            background: var(--light-bg) !important;
            border-color: var(--border-color);
            color: var(--text-muted) !important;
        }

        .export-buttons {
            margin-bottom: 1rem;
        }

        .btn-export {
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            margin-right: 0.5rem;
        }

        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        @media (max-width: 768px) {
            .main-container {
                margin: 1rem;
                padding: 1rem;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .summary-cards {
                grid-template-columns: 1fr;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
        }

        @media print {
            body {
                background: white;
            }
            
            .main-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            
            .page-header {
                background: var(--primary-color);
                color: white;
            }
            
            .summary-cards {
                display: none;
            }
            
            .export-buttons {
                display: none;
            }
            
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="page-header">
            <h1><i class="bi bi-calculator"></i> Summary Charges Report</h1>
            <div class="subtitle">Generated on <?=date('F j, Y \a\t g:i A')?> for Year <?=$_GET['year']?></div>
        </div>

        <div class="summary-cards">
            <div class="summary-card">
                <h3><i class="bi bi-list-ul"></i> Charge Types</h3>
                <div class="amount"><?=count($summary)?></div>
            </div>
            <div class="summary-card">
                <h3><i class="bi bi-currency-dollar"></i> Grand Total</h3>
                <div class="amount">$<?=number_format($grand_total, 2)?></div>
            </div>
        </div>

        <div class="export-buttons">
            <button class="btn btn-success btn-export" onclick="exportToCSV()">
                <i class="bi bi-file-earmark-text"></i> Export to CSV
            </button>
            <button class="btn btn-secondary btn-export" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Report
            </button>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table id="summaryTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Charge Type</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($summary as $type => $amount) { ?>
                            <tr>
                                <td><?= ChidonShipping::getDescription($type) ? ChidonShipping::getDescription($type) : $type ?></td>
                                <td class="number-cell">$<?= number_format($amount, 2) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr class="grand-total-row">
                            <th>Grand Total</th>
                            <th class="number-cell">$<?= number_format($grand_total, 2) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#summaryTable').DataTable({
                responsive: true,
                pageLength: -1, // Show all records
                paging: false, // Disable pagination
                order: [[1, 'desc']], // Sort by amount descending
                language: {
                    search: "Search records:",
                    info: "Showing _TOTAL_ records",
                    infoEmpty: "Showing 0 records",
                    infoFiltered: "(filtered from _MAX_ total records)"
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12"i>>',
                initComplete: function() {
                    // Add custom styling after DataTable initialization
                    $('.dataTables_filter input').attr('placeholder', 'Search...');
                }
            });
        });

        function exportToCSV() {
            let table = document.getElementById('summaryTable');
            let csv = [];
            let headers = [];

            // Get headers from the table (only the first two columns)
            table.querySelectorAll('thead th').forEach(function(th, index) {
                if (index < 2) { // Only take first two columns
                    headers.push(th.textContent.trim());
                }
            });
            csv.push(headers.join(','));

            // Get data rows (only from tbody, not tfoot)
            table.querySelectorAll('tbody tr').forEach(function(row) {
                let rowData = [];
                row.querySelectorAll('td').forEach(function(cell, index) {
                    if (index < 2) { // Only take first two columns
                        let cellText = cell.textContent.trim();
                        // Remove commas from numbers (amount field)
                        if (index === 1 && cellText.includes('$')) {
                            cellText = cellText.replace(/,/g, '');
                        }
                        rowData.push(cellText);
                    }
                });
                csv.push(rowData.join(','));
            });

            // Add grand total row (manually, not from tfoot)
            csv.push('Grand Total,$' + <?=number_format($grand_total, 2, '.', '')?>);

            let csvContent = csv.join('\n');
            let blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8' });
            let link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'summary_charges_report.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>
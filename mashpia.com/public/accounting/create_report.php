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

$report_type = $_POST['report_type'];
if ($report_type == 'summary') {
    $schools = '';
    if ($_POST['school_id'] != '0') {
        $schools = implode(',', $_POST['school_id']);
    }
    header('Location: summary.php?year=' . $_POST['year'] . '&schools=' . $schools);
    exit;
}

// echo "<pre>"; print_r($_POST); echo "</pre>";
$tables = [
    's'     => 'schools', 
    'u'     => 'users', 
    'ur'    => 'user_registration', 
    'rc'    => 'registration_charges',
    'sr'    => 'school_registrations', 
    'srd'   => 'school_registration_details', 
    'c'     => 'classes'
];

$fields = [
    'base' => [
        'base_type'     =>  's.reg_type',
        'school_number' => 's.school_number', 
        'school_name'   => 's.school_name', 
        'date_registered' => 'sr.date_paid', 
        'chayolei_fee'  => 's.chayolei_fee', 
        'chayolei_paid' => 'srd.chayolei', 
        'chidon_fee'    => 's.chidon_fee', 
        'chidon_paid'   => 'srd.chidon', 
        'prior_balance' => 's.balance', 
        'prior_balance_paid' => 'srd.past_due', 
        'total_owed'    => 'total_owed',  
        'total_paid'    => 'total_paid', 
        'base_discount' => 'srd.discount',
        'total_balance' => 'total_balance', 
        'registered_chayolim' => 'total_registered'
    ], 
    'soldier' => [
        'reg_type'      => 's.reg_type',
        'school_number' => 's.school_number',
        'school_name'   => 's.school_name',
        'user_serial'   => 'u.user_serial',
        'grade'         => ['c.class_grade', 'c.class_sub'],
        'user_name'     => ['u.first', 'u.last'],
        'date_registered' => 'ur.reg_date',
        'reg_fee'       => 's.chayolei_fee',
        'reg_paid'      => 'ur.paid',
        'soldier_discount' => 'total_discount',
        'total_balance' => 'total_balance'
    ],
    'details' => [
        'reg_type'      => 's.reg_type',
        'school_number' => 's.school_number',
        'school_name'   => 's.school_name',
        'user_serial'   => 'u.user_serial',
        'user_name'     => ['u.first', 'u.last'],
        'type'          => 'rc.type',
        'code'          => 'code',
        'reg_date'      => 'rc.date',
        'reg_amount'    => 'rc.amount',
        'refunded'      => 'rc.refunded'
    ]
];

// echo "<pre>"; print_r($_POST); echo "</pre>"; 
// build sql
$from = [];
$srd_qry = false;
$total_registered_chayolim = false;
// SELECT fields
$sql = "SELECT ";
if ($report_type == 'base') {
    $sql .= "s.school_id, ";
} else if ($report_type == 'soldier') {
    $sql .= "u.user_id, u.school_id, ";
} else if ($report_type == 'details') {
    $sql .= "rc.user_id, rc.school_id, ";
}
foreach ($_POST[$report_type . '_options'] as $option) {
    if ($option == 'code') continue;
    $field = $fields[$report_type][$option];
    if (!is_array($field)) {
        $field = [$field];
    }
    foreach ($field as $f) {
        if (strpos($f, 'total') !== false) {
            if ($f == 'total_registered') {
                $total_registered_chayolim = true;
            }
            continue;
        }
        $pos = strpos($f, '.');
        $table = substr($f, 0, $pos);
        if (!in_array($table, $from)) {
            $from[] = $table;
        }
        if ($table != 'srd') { // for srd we will create a new qry
            $sql .= $f . ", ";
        }
    }
}
// remove last comma
$sql = substr($sql, 0, strlen($sql) - 2);
// FROM tables
$sql .= " FROM ";
if ($report_type == 'base') {
    $sql .= "schools s ";
} else if ($report_type == 'soldier') {
    $sql .= "users u ";
} else if ($report_type == 'details') {
    $sql .= "registration_charges rc ";
}
// LEFT JOIN tables
foreach ($from as $t) {
    if (
        ($report_type == 'base' && $t != 's') ||
        ($report_type == 'soldier' && $t != 'u') ||
        ($report_type == 'details' && $t != 'rc')
    ) {
        if ($t == 'srd') {
            if (!$srd_qry) {
                $srd_qry = "SELECT srd.* FROM school_registration_details srd 
                    JOIN school_registrations sr USING (school_registration_id) 
                    WHERE sr.school_id IN (" . implode(',', $_POST['school_id']) . ") 
                    AND sr.year = :year";
            }
        } else {
            if ($t == 'c') $sql .= ' LEFT JOIN classes c USING (class_id) ';
            else if ($t == 'ur') $sql .= ' LEFT JOIN user_registration ur USING (user_id) ';
            else if ($report_type == 'details' && $t == 'u') $sql .= ' LEFT JOIN users u USING (user_id) ';
            else $sql .= ' LEFT JOIN ' . $tables[$t] . ' ' . $t . ' USING (school_id) ';
        }
    }
}
// WHERE clause
if ($report_type == 'base') {
    $table = 's.';
} else if ($report_type == 'soldier' || $report_type == 'details') {
    $table = 'u.';
}
$sql .= " WHERE ";
if ($_POST['school_id'] != '0' && $_POST['school_id'][0] > 0) {
    $sql .= $table . "school_id IN (" . implode(',', $_POST['school_id']) . ")";
} else {
    $sql .= $table . "school_id != 0";
}
if (in_array('sr', $from) || in_array('ur', $from)) {
    $sql .= " AND ur.year = :year";
} else if ($report_type == 'details') {
    $sql .= " AND rc.year = :year";
}
// echo $sql;
$stmt = $MASHPIA_DB->prepare($sql);
if (in_array('sr', $from) || in_array('ur', $from) || in_array('rc', $from)) {
    $stmt->execute([
        ':year' => $_POST['year']
    ]);
} else {
    $stmt->execute();
}
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// build the report
$report = [];
foreach ($results as $result) {
    $report[$result['school_id']][] = $result;
}

// get the srd results
$srd_results = [];
$srd_report = [];
if ($srd_qry) {
    $srd_stmt = $MASHPIA_DB->prepare($srd_qry);
    $srd_stmt->execute([
        ':year' => $_POST['year']
    ]);
    $srd_results = $srd_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($srd_results as $srd_result) {
        $srd_report[$srd_result['school_id']][$srd_result['type']] = $srd_result['amount'];
    }
}

// get the total registered chayolim
$total_reg = [];
if ($total_registered_chayolim) {
    $sql = "
        SELECT 
            school_id, COUNT(*) AS total_registered
        FROM
            users u 
        WHERE
            u.user_registered > 0 
                AND u.user_id in (
                    SELECT user_id FROM user_registration WHERE year = :year
                )";
    if ($_POST['school_id'] != '0') {
        $sql .= " AND u.school_id IN (" . implode(',', $_POST['school_id']) . ")";
    }
    $sql .= " GROUP BY school_id";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute([
        ':year' => $_POST['year']
    ]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $r) {
        $total_reg[$r['school_id']] = $r['total_registered'];
    }
}

$reg_types = [
    1 => 'Tuition',
    2 => 'Guaranteed',
    3 => 'Regular'
];
?>
<!DOCTYPE html>
<html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting Report</title>
    
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
        }

        .positive-amount {
            color: var(--success-color);
        }

        .negative-amount {
            color: var(--accent-color);
        }

        .zero-amount {
            color: var(--text-muted);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-tuition {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .badge-guaranteed {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
        }

        .badge-regular {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white;
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
            <h1><i class="bi bi-calculator"></i> Accounting <?=ucwords($report_type)?> Report</h1>
            <div class="subtitle">Generated on <?=date('F j, Y \a\t g:i A')?></div>
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
                <table id="accountingTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <?php
                            foreach ($fields[$report_type] as $key => $field) {
                                if (!in_array($key, $_POST[$report_type . '_options'])) continue;
                                if (is_array($field)) {
                                    $desc = explode('_', $key);
                                    $desc = implode(' ', $desc);    
                                    echo '<th>' . ucwords($desc) . '</th>';
                                } else {
                                    if (strpos($field, '.') !== false) {
                                        $details = explode('.', $field);
                                        $table = $details[0];
                                        $field = $details[1];
                                        if ($table == 'srd' && $field != 'discount') {
                                            $field .= '_paid';
                                        }
                                        if ($field == 'balance') $field = 'past_due';
                                    }
                                    $details = explode('_', $field);
                                    $field = implode(' ', $details);
                                    echo '<th>' . ucwords($field) . '</th>';
                                }
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($report as $school_id => $more) {
                            foreach ($more as $result) {
                            // echo "<pre>"; print_r($result); echo "</pre>";
                                $total_owed = 0;
                                $total_paid = 0;
                                echo '<tr>';
                                foreach ($fields[$report_type] as $key => $field) {
                                    if (!in_array($key, $_POST[$report_type . '_options'])) continue;
                                    if (is_array($field)) {
                                        if ($key == 'grade') {
                                            $grade = $result['class_grade'] . ($result['class_sub'] ? ' ' . $result['class_sub'] : '');
                                            $cellContent = $grade;
                                        } else if ($key == 'user_name') {
                                            $cellContent = $result['first'] . ' ' . $result['last'];
                                        }
                                    } else {
                                        if (strpos($field, '.') !== false) {
                                            $details = explode('.', $field);
                                            $table = $details[0];
                                            $field = $details[1];
                                        }

                                        if (in_array($field, ['school_id', 'user_id'])) continue;
                                        
                                        $cellClass = '';
                                        $cellContent = '';
                                    
                                        switch ($field) {
                                            case 'chayolei':
                                            case 'chidon':
                                            case 'past_due':
                                            case 'discount':
                                                $paid = intval($srd_report[$school_id][$field] ?? 0);
                                                if ($field != 'discount') $total_paid += $paid;
                                                $cellContent = number_format($paid, 2);
                                                $cellClass = 'number-cell';
                                                if ($paid > 0) $cellClass .= ' positive-amount';
                                                else if ($paid < 0) $cellClass .= ' negative-amount';
                                                else $cellClass .= ' zero-amount';
                                                break;
                                            case 'reg_type':
                                                $regType = $reg_types[$result[$field]];
                                                $cellContent = '<span class="status-badge badge-' . strtolower($regType) . '">' . $regType . '</span>';
                                                break;
                                            case 'total_registered':
                                                $cellContent = $total_reg[$school_id];
                                                $cellClass = 'number-cell';
                                                break;
                                            case 'total_balance':
                                                $total_balance = $total_owed - $total_paid - intval($result['discount'] ?? 0);
                                                $cellContent = number_format($total_balance, 2);
                                                $cellClass = 'number-cell';
                                                if ($total_balance > 0) $cellClass .= ' negative-amount';
                                                else if ($total_balance < 0) $cellClass .= ' positive-amount';
                                                else $cellClass .= ' zero-amount';
                                                break;
                                            case 'total_paid':
                                            case 'total_owed':
                                                $cellContent = number_format($$field, 2);
                                                $cellClass = 'number-cell';
                                                if ($$field > 0) $cellClass .= ' positive-amount';
                                                else $cellClass .= ' zero-amount';
                                                break;
                                            case 'code':
                                                $cellContent = ChidonShipping::getDescription($field);
                                                break;
                                            default:
                                                $cellContent = $result[$field];
                                                if ($field == 'balance' || strpos($field, 'fee') !== false) {
                                                    $total_owed += intval($result[$field]);
                                                    $cellClass = 'number-cell';
                                                    if (intval($result[$field]) > 0) $cellClass .= ' negative-amount';
                                                    else $cellClass .= ' zero-amount';
                                                }
                                                break;
                                        }
                                    }
                                    echo '<td class="' . $cellClass . '">' . $cellContent . '</td>';
                                }
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
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
            $('#accountingTable').DataTable({
                responsive: true,
                pageLength: 25,
                order: [[0, 'asc']],
                language: {
                    search: "Search records:",
                    lengthMenu: "Show _MENU_ records per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ records",
                    infoEmpty: "Showing 0 to 0 of 0 records",
                    infoFiltered: "(filtered from _MAX_ total records)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                initComplete: function() {
                    // Add custom styling after DataTable initialization
                    $('.dataTables_filter input').attr('placeholder', 'Search...');
                }
            });
        });

        function exportToCSV() {
            let table = document.getElementById('accountingTable');
            let csv = [];
            let headers = [];

            // Get headers from the table
            table.querySelectorAll('th').forEach(function(th) {
                headers.push(th.textContent.trim());
            });
            csv.push(headers.join(','));

            // Get data rows
            table.querySelectorAll('tbody tr').forEach(function(row) {
                let rowData = [];
                row.querySelectorAll('td').forEach(function(cell) {
                    rowData.push(cell.textContent.trim());
                });
                csv.push(rowData.join(','));
            });

            let csvContent = csv.join('\n');
            let blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8' });
            let link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'accounting_report.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function exportToPDF() {
            // Simple PDF export using window.print()
            window.print();
        }
    </script>
</body>
</html>
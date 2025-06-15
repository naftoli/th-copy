<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

// only super users can see all schools
if ($admin_user['auth'] != 'super') {
    die('You are not authorized to view this page');
}

$start_date = 2460448; // May 17, 2024
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

// Query to get medal data since May 5, 2024
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        school_name, subject_name, medal_name, COUNT(*) AS total
    FROM
        medal_marks mm
            JOIN
        subjects s USING (subject_id)
            JOIN
        medals m USING (medal_ord)
            JOIN
        users u USING (user_id)
            JOIN
        schools sc ON sc.school_id = u.school_id
    WHERE
        mm.date_awarded >= :start_date
    GROUP BY sc.school_id , subject_id , medal_ord
");
$stmt->execute(['start_date' => $start_date]);

// Process data for cross-tab format
$schools = [];
$columns = [];
$data = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $column_key = $row['subject_name'] . ' - ' . $row['medal_name'];
    $school = $row['school_name'];
    
    // Add to columns list if not exists
    if (!in_array($column_key, $columns)) {
        $columns[] = $column_key;
    }
    
    // Add to schools list if not exists
    if (!in_array($school, $schools)) {
        $schools[] = $school;
    }
    
    // Store the data
    if (!isset($data[$school])) {
        $data[$school] = [];
    }
    $data[$school][$column_key] = $row['total'];
}

// Sort schools and columns alphabetically
sort($schools);
sort($columns);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Medal Report - Cross Tab Format</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .table-container {
            overflow-x: auto;
            margin: 2rem;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            white-space: nowrap;
            position: sticky;
            top: 0;
        }
        .table td {
            vertical-align: middle;
        }
        .table th:first-child {
            position: sticky;
            left: 0;
            z-index: 2;
            background-color: #f8f9fa;
        }
        .table td:first-child {
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 1;
        }
        @media print {
            .table-container {
                margin: 0;
            }
            .table th:first-child,
            .table td:first-child {
                position: static;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <button class="btn btn-primary m-3 d-print-none" style="float: right;" onClick="window.print()">
            Print Report
        </button>
    </div>
    <div class="table-container">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>School</th>
                    <?php foreach ($columns as $column): ?>
                        <th><?php echo htmlspecialchars($column); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schools as $school): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($school); ?></td>
                        <?php foreach ($columns as $column): ?>
                            <td><?php echo isset($data[$school][$column]) ? $data[$school][$column] : '0'; ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 
<?php
$admin_auth = array();
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$details = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT * FROM user_registration ur 
    JOIN users u USING (user_id) 
    JOIN schools s ON s.school_id = u.school_id 
    WHERE ur.year = :year AND s.test_school = 0 AND u.user_registered > 0 
");
$stmt->execute([':year' => $year]);
$rows = $stmt->fetchAll();

$totals = [];
foreach ($rows as $row) {
    if (isset($totals[$row['school_id']])) $totals[$row['school_id']] += floatval($row['paid']);
    else $totals[$row['school_id']] = floatval($row['paid']);
    $details[$row['school_id']][] = $row;
}

// find out total for any discounts that were used
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        school_id, IFNULL(SUM(discount), 0) AS discount
    FROM
        registration_charges 
    WHERE
        year = :year AND type IN ('chayolei', 'THE') AND discount > 0  
    GROUP BY school_id
");
$stmt->execute([':year' => $year]);
$temp = $stmt->fetchAll();
foreach ($temp as $row) {
    $discounts[$row['school_id']] = $row['discount'];
}

$types = [
    1 => 'Tuition',
    2 => 'Guaranteed',
    3 => 'Regular'
];

$school_info = [];
$stmt = $MASHPIA_DB->query("
    SELECT school_id, reg_type, COUNT(*) as eligible 
    FROM schools s 
    LEFT JOIN users u using (school_id) 
    WHERE u.chayolei_eligible = 1 
    GROUP BY s.school_id
");
$temp = $stmt->fetchAll();
foreach ($temp as $row) {
    $school_info[$row['school_id']] = [
        'type'      => $types[$row['reg_type']],
        'eligible'  => $row['eligible']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Soldier Registration <?=$year?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables Bootstrap 5 -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css"/>
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin: 20px auto;
            padding: 30px;
            max-width: 1800px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .page-header h1 {
            margin: 0;
            font-weight: 300;
            font-size: 2.5rem;
        }
        
        .section-header {
            background: linear-gradient(135deg, #34495e, #2c3e50);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin: 30px 0 20px 0;
            font-weight: 600;
        }
        
        .alert-custom {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid #ffc107;
        }
        
        .alert-custom h5 {
            color: #856404;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .alert-custom p {
            color: #856404;
            margin-bottom: 0;
            line-height: 1.6;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 15px 10px;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
        }
        
        .table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .balance-negative {
            background-color: rgba(231, 76, 60, 0.1) !important;
            color: var(--danger-color);
            font-weight: 600;
        }
        
        .balance-positive {
            background-color: rgba(39, 174, 96, 0.1) !important;
            color: var(--success-color);
            font-weight: 600;
        }
        
        .totals-row {
            background: linear-gradient(135deg, #34495e, #2c3e50);
            color: white;
            font-weight: 600;
        }
        
        .totals-row th {
            border: none;
            padding: 15px 10px;
        }
        
        .dataTables_wrapper {
            padding: 20px;
        }
        
        .dataTables_filter input {
            border: 2px solid var(--border-color);
            border-radius: 6px;
            padding: 8px 12px;
        }
        
        .dataTables_length select {
            border: 2px solid var(--border-color);
            border-radius: 6px;
            padding: 6px 10px;
        }
        
        /* Base Type Badge Colors */
        .badge-tuition {
            background: linear-gradient(135deg, #e74c3c, #c0392b) !important;
            color: white !important;
        }
        
        .badge-guaranteed {
            background: linear-gradient(135deg, #27ae60, #229954) !important;
            color: white !important;
        }
        
        .badge-regular {
            background: linear-gradient(135deg, #3498db, #2980b9) !important;
            color: white !important;
        }
        
        .badge {
            display: inline-block !important;
            min-width: 80px !important;
            text-align: center !important;
            font-size: 0.85rem !important;
            padding: 6px 12px !important;
            line-height: 1.2 !important;
            white-space: nowrap !important;
        }
        
        @media (max-width: 768px) {
            .main-container {
                margin: 10px;
                padding: 15px;
            }
            
            .page-header h1 {
                font-size: 1.8rem;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="page-header">
            <h1><i class="bi bi-people"></i> Soldier Registration <?=$year?></h1>
        </div>
        
        <!-- Important Note -->
        <div class="alert-custom">
            <h5><i class="bi bi-info-circle"></i> Important Note</h5>
            <p>
                The list of schools is based off schools that have any registered children for the current year and are not test schools.
                The balance may not be 100% accurate, since it's based off the fee as it is <strong>today</strong>, and <strong>not</strong> when the child registered.
            </p>
        </div>
        
        <!-- Base Totals Section -->
        <div class="section-header">
            <i class="bi bi-calculator"></i> Base Totals
        </div>
        
        <div class="table-container">
            <div class="table-responsive">
                <table id="table" class="table table-striped table-hover">
                    <thead>
                        <tr>
                           <th>Base Type</th>
                           <th>Base Name</th>
                           <th>Soldiers Registered</th>
                           <th>Fee per Soldier</th>
                           <th>Total Fee</th>
                           <th>Total Discounts</th>
                           <th>Total Owing</th>
                           <th>Total Paid</th>
                           <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $t['soldiers'] = 0;
                    $t['total_fee'] = 0;
                    $t['discounts'] = 0;
                    $t['owing'] = 0;
                    $t['paid'] = 0;
                    $t['balance'] = 0;
                    foreach ($totals as $school_id => $total) {
                        // if there's no name for the base just skip
                        if (! isset($schools[$school_id])) continue;
                        $stmt = $MASHPIA_DB->prepare("
                            SELECT reg_type, child_fee     
                            FROM schools  
                            WHERE school_id = :id 
                        ");
                        $stmt->execute([':id' => $school_id]);
                        $row = $stmt->fetch();
                        
                        $fee = GlobalSettings::calculateChildFee($row['reg_type'], $row['child_fee'], false, true, false, false,
                          $school_id == 61, $school_id == 269);
                        
                        // Determine badge class
                        $badge_class = '';
                        switch($row['reg_type']) {
                            case 1:
                                $badge_class = 'badge-tuition';
                                break;
                            case 2:
                                $badge_class = 'badge-guaranteed';
                                break;
                            case 3:
                                $badge_class = 'badge-regular';
                                break;
                            default:
                                $badge_class = 'bg-secondary';
                        }
                        
                        $total_fee = $fee * count($details[$school_id]);
                        $total_owing = $total_fee - floatval($discounts[$school_id]);
                        $balance = $total_owing - floatval($total);
                        
                        $balance_class = '';
                        if ($balance > 0) {
                            $balance_class = 'balance-negative';
                        } elseif ($balance < 0) {
                            $balance_class = 'balance-positive';
                        }
                        ?>
                        <tr>
                            <td><span class="badge <?= $badge_class ?>"><?= $school_info[$school_id]['type'] ?></span></td>
                            <td><strong><a href="#<?= $school_id ?>" class="text-decoration-none"><?= $schools[$school_id] ?></a></strong></td>
                            <td class="text-center"><?= count($details[$school_id]) ?></td>
                            <td class="text-end">$<?= number_format($fee, 2) ?></td>
                            <td class="text-end">$<?= number_format($total_fee, 2) ?></td>
                            <td class="text-end">$<?= number_format($discounts[$school_id], 2) ?></td>
                            <td class="text-end"><strong>$<?= number_format($total_owing, 2) ?></strong></td>
                            <td class="text-end">$<?= number_format($total, 2) ?></td>
                            <td class="text-end <?= $balance_class ?>">
                                <strong>$<?= number_format($balance, 2) ?></strong>
                            </td>
                        </tr>
                        <?php
                        $t['soldiers'] += count($details[$school_id]);
                        $t['total_fee'] += $total_fee;
                        $t['discounts'] += $discounts[$school_id];
                        $t['owing'] += $total_owing;
                        $t['paid'] += $total;
                        $t['balance'] += $balance;
                    }
                    ?>
                    </tbody>
                    <tfoot>
                        <tr class="totals-row">
                            <th>Totals:</th>
                            <th></th>
                            <th class="text-center"><?= $t['soldiers'] ?></th>
                            <th></th>
                            <th class="text-end">$<?= number_format($t['total_fee'], 2) ?></th>
                            <th class="text-end">$<?= number_format($t['discounts'], 2) ?></th>
                            <th class="text-end">$<?= number_format($t['owing'], 2) ?></th>
                            <th class="text-end">$<?= number_format($t['paid'], 2) ?></th>
                            <th class="text-end">$<?= number_format($t['balance'], 2) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <!-- Soldier Details Section -->
        <div class="section-header">
            <i class="bi bi-person-lines-fill"></i> Soldier Details
        </div>
        
        <div class="table-container">
            <div class="table-responsive">
                <table id="details" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Serial Number</th>
                            <th>Base Number</th>
                            <th>Base Name</th>
                            <th>Grade</th>
                            <th>Soldier</th>
                            <th>Registered</th>
                            <th>Fee</th>
                            <th>Coupon Discount</th>
                            <th>Owes</th>
                            <th>Paid</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $stmt = $MASHPIA_DB->prepare("
                        SELECT 
                            *
                        FROM
                            users u
                                JOIN
                            schools s USING (school_id)
                                JOIN
                            classes c ON c.class_id = u.class_id
                        WHERE
                            u.user_id = :id
                    ");
                    foreach ($details as $school_id => $students) {
                        foreach ($students as $idx => $student) {
                            $stmt->execute([':id' => $student['user_id']]);
                            $row = $stmt->fetch();
                            if (! $row['school_name']) continue;

                            $early = true;
                            $registered = $student['reg_date'];
                            if ($registered) {
                                $early_bird = GlobalSettings::earlyBird();
                                $reg_date = new DateTime($registered);
                                if ($reg_date > $early_bird) $early = false;
                            }
                            $fee = GlobalSettings::calculateChildFee($row['school_type'], $row['child_fee'], false, $early, false, false,
                              $school_id == 61, $school_id == 269);

                            $owes = $fee - $student['discount'];
                            $balance = $owes - $student['paid'];
                            
                            $balance_class = '';
                            if ($balance > 0) {
                                $balance_class = 'balance-negative';
                            } elseif ($balance < 0) {
                                $balance_class = 'balance-positive';
                            }
                            ?>
                            <tr>
                                <td class="text-center"><?= $student['user_id'] ?></td>
                                <td class="text-center"><?= $row['user_serial'] ?></td>
                                <td class="text-center"><?= $row['school_number'] ?></td>
                                <td>
                                    <?php if ($idx == 0): ?>
                                        <strong id="<?= $school_id ?>"><?= $row['school_name'] ?></strong>
                                    <?php else: ?>
                                        <?= $row['school_name'] ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= $row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : '') ?></td>
                                <td><strong><?= $row['first'] . ' ' . $row['last'] ?></strong></td>
                                <td class="text-center">
                                    <?php if ($row['user_registered']): ?>
                                        <i class="bi bi-calendar-check text-success"></i>
                                        <?= date('m/d/Y', strtotime($row['user_registered'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted"><i class="bi bi-calendar-x"></i> Not Registered</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">$<?= number_format($fee, 2) ?></td>
                                <td class="text-end">$<?= number_format($student['discount'], 2) ?></td>
                                <td class="text-end"><strong>$<?= number_format($owes, 2) ?></strong></td>
                                <td class="text-end">$<?= number_format($student['paid'], 2) ?></td>
                                <td class="text-end <?= $balance_class ?>">
                                    <strong>$<?= number_format($balance, 2) ?></strong>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <!-- DataTables -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    
    <script>
        $(function() {
            // Initialize DataTables
            $('#table').DataTable({
                responsive: true,
                paging: false,
                ordering: true,
                info: false,
                order: [[1, 'asc']],
                language: {
                    search: "Search bases:",
                    lengthMenu: "Show _MENU_ bases per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ bases",
                    infoEmpty: "No bases to show",
                    infoFiltered: "(filtered from _MAX_ total bases)"
                }
            });
            
            $('#details').DataTable({
                responsive: true,
                paging: false,
                ordering: true,
                info: false,
                order: [[2, 'asc']],
                language: {
                    search: "Search soldiers:",
                    lengthMenu: "Show _MENU_ soldiers per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ soldiers",
                    infoEmpty: "No soldiers to show",
                    infoFiltered: "(filtered from _MAX_ total soldiers)"
                }
            });
        });
    </script>
</body>
</html>
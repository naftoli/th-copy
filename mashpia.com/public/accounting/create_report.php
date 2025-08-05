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
    if ($_POST['school_id'][0] > 0) {
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

// build sql
$from = [];
$srd_qry = false;
$soldier_discounts = false;
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
            } else if ($f == 'total_discount') {
                $soldier_discounts = true;
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
if ($_POST['school_id'][0] > 0) {
    $sql .= $table . "school_id IN (" . implode(',', $_POST['school_id']) . ")";
} else {
    $sql .= $table . "school_id != 0";
}
if (in_array('sr', $from)) {
    $sql .= " AND sr.year = :year";
} else if (in_array('ur', $from)) {
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
    if ($_POST['school_id'][0] > 0) {
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

// get the soldier discounts
$discounts = [];
if ($soldier_discounts) {
    // find out total for any discounts that were used
    $stmt = $MASHPIA_DB->prepare("
        SELECT 
            user_id, discount
        FROM
            registration_charges 
        WHERE
            year = :year AND type IN ('chayolei', 'THE') AND discount > 0  
    ");
    $stmt->execute([':year' => $_POST['year']]);
    $temp = $stmt->fetchAll();
    foreach ($temp as $row) {
        $discounts[$row['user_id']] = $row['discount'];
    }
}
// echo "<pre>"; print_r($discounts); echo "</pre>"; exit;

$reg_types = [
    1 => 'Tuition',
    2 => 'Guaranteed',
    3 => 'Regular'
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Accounting <?=ucwords($report_type)?> Report</h1>
        <p>Generated on <?=date('F j, Y \a\t g:i A')?></p>

        <div class="table-responsive">
            <table id="accountingTable" class="table table-striped table-bordered">
                <thead class="table-dark">
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
                                            $cellClass = 'text-end';
                                            if ($paid > 0) $cellClass .= ' text-success';
                                            else if ($paid < 0) $cellClass .= ' text-danger';
                                            else $cellClass .= ' text-muted';
                                            break;
                                        case 'reg_type':
                                            $regType = $reg_types[$result[$field]];
                                            $cellContent = $regType;
                                            break;
                                        case 'total_registered':
                                            $cellContent = $total_reg[$school_id] ?? 0;
                                            $cellClass = 'text-end';
                                            break;
                                        case 'total_balance':
                                            $total_balance = $total_owed - $total_paid - intval($result['discount'] ?? 0);
                                            $cellContent = number_format($total_balance, 2);
                                            $cellClass = 'text-end';
                                            if ($total_balance > 0) $cellClass .= ' text-danger';
                                            else if ($total_balance < 0) $cellClass .= ' text-success';
                                            else $cellClass .= ' text-muted';
                                            break;
                                        case 'total_paid':
                                        case 'total_owed':
                                            $cellContent = number_format($$field, 2);
                                            $cellClass = 'text-end';
                                            if ($$field > 0) $cellClass .= ' text-success';
                                            else $cellClass .= ' text-muted';
                                            break;
                                        case 'code':
                                            $cellContent = ChidonShipping::getDescription($field);
                                            break;
                                        case 'total_discount':
                                            $cellContent = number_format($discounts[$result['user_id']] ?? 0, 2);
                                            $cellClass = 'text-end';
                                            if ($cellContent > 0) $cellClass .= ' text-success';
                                            else $cellClass .= ' text-muted';
                                            break;
                                        default:
                                            $cellContent = $result[$field];
                                            if ($field == 'balance' || strpos($field, 'fee') !== false) {
                                                $total_owed += intval($result[$field]);
                                                $cellClass = 'text-end';
                                                if (intval($result[$field]) > 0) $cellClass .= ' text-danger';
                                                else $cellClass .= ' text-muted';
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

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
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
                    $('.dataTables_filter input').attr('placeholder', 'Search...');
                }
            });
        });
    </script>
</body>
</html>
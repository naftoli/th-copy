<?php
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../../header.php';
require_once '../../api/header/db.php';
require_once '../../chidon_shipping/class.chidonShipping.php';

if ($admin_user['auth'] != 'super') {
    echo "Not authorized";
    exit;
}

$debug = $_POST['debug'] ?? false;
if ($debug) {
    echo "<pre>"; print_r($_POST); echo "</pre>";
}

$tables = [
    's'     => 'schools', 
    'u'     => 'users', 
    'ur'    => 'user_registration', 
    'rc'    => 'registration_charges',
    'sr'    => 'school_registrations', 
    'srd'   => 'school_registration_details', 
    'c'     => 'classes',
    'd'     => 'discounts'
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
        'base_discount' => 'd.amount',
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
        'soldier_discount' => 'd.amount',
        'total_balance' => 'total_balance'
    ],
    'details' => [
        'reg_type'      => 's.reg_type',
        'school_number' => 's.school_number',
        'school_name'   => 's.school_name',
        'user_serial'   => 'u.user_serial',
        'user_name'     => ['u.first', 'u.last'],
        'type'          => 'type',
        'code'          => 'rc.type',
        'reg_date'      => 'rc.date',
        'reg_amount'    => 'rc.amount',
        'refunded'      => 'rc.refunded'
    ]
];

$report_type = $_POST['report_type'];

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
    if ($option == 'type') continue;
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
                    WHERE sr.year = :year";
                if ($_POST['school_id'][0] > 0) {
                    $srd_qry .= " AND sr.school_id IN (" . implode(',', $_POST['school_id']) . ")";
                }
            }
        } else {
            if ($t == 'c') $sql .= ' LEFT JOIN classes c USING (class_id) ';
            else if ($t == 'ur') $sql .= ' LEFT JOIN user_registration ur USING (user_id) ';
            else if ($report_type == 'details' && $t == 'u') $sql .= ' LEFT JOIN users u USING (user_id) ';
            else if ($report_type == 'base' && $t == 'd') $sql .= ' LEFT JOIN discounts d USING (school_id, year) ';
            else if ($report_type == 'soldier' && $t == 'd') $sql .= ' LEFT JOIN discounts d USING (user_id, year) ';
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
if ($debug) {
    echo $sql;
}

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
    // $srd_stmt->debugDumpParams();
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

$reg_types = [
    1 => 'Tuition',
    2 => 'Guaranteed',
    3 => 'Regular'
];

// Get schools for payment form (only for base reports)
$schools_for_payment = [];
if ($report_type == 'base') {
    require_once '../../class.adminSchools.php';
    $adminSchools = new adminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
    $schools_for_payment = $adminSchools->getSchools();
}

$headers = [];
$headers_dec = [];
foreach ($fields[$report_type] as $key => $field) {
    if (!in_array($key, $_POST[$report_type . '_options'])) continue;
    if (is_array($field)) {
        $desc = explode('_', $key);
        $desc = implode(' ', $desc);    
        $headers[] = ucwords($desc);
        $headers_dec[$key] = ucwords($desc);
    } else {
        if (strpos($field, '.') !== false) {
            $details = explode('.', $field);
            $table = $details[0];
            $field = $details[1];
            if ($table == 'srd') $field .= '_paid';
            if ($field == 'balance') $field = 'past_due';
            if ($field == 'amount') $field = 'discount';
        }
        $details = explode('_', $field);
        $field = implode(' ', $details);
        $headers[] = ucwords($field);
        $headers_dec[$key] = ucwords($field);
    }
}


$data = [];
foreach ($report as $school_id => $more) {
    foreach ($more as $result) {
    // echo "<pre>"; print_r($result); echo "</pre>";
        $total_owed = 0;
        $total_paid = 0;
        $row = [];
        foreach ($fields[$report_type] as $key => $field) {
            if (!in_array($key, $_POST[$report_type . '_options'])) continue;
            if (is_array($field)) {
                if ($key == 'grade') {
                    $grade = $result['class_grade'] . ($result['class_sub'] ? ' ' . $result['class_sub'] : '');
                    $row[$headers_dec[$key]] = $grade;
                } else if ($key == 'user_name') {
                    $row[$headers_dec[$key]] = $result['first'] . ' ' . $result['last'];
                }
            } else {
                if (strpos($field, '.') !== false) {
                    $details = explode('.', $field);
                    $table = $details[0];
                    $field = $details[1];
                }

                if (in_array($field, ['school_id', 'user_id'])) continue;
            
                switch ($field) {
                    case 'chayolei':
                    case 'chidon':
                    case 'past_due':
                        $paid = intval($srd_report[$school_id][$field] ?? 0);
                        $total_paid += $paid;
                        $row[$headers_dec[$key]] = number_format($paid, 2);
                        break;
                    case 'reg_type':
                        $regType = $reg_types[$result[$field]];
                        $row[$headers_dec[$key]] = $regType;
                        break;
                    case 'total_registered':
                        $row[$headers_dec[$key]] = $total_reg[$school_id] ?? 0;
                        break;
                    case 'total_balance':
                        $total_balance = $total_owed - $total_paid - intval($result['amount'] ?? 0);
                        $row[$headers_dec[$key]] = number_format($total_balance, 2);
                        break;
                    case 'total_paid':
                    case 'total_owed':
                        $row[$headers_dec[$key]] = number_format($$field, 2);
                        break;
                    case 'type':
                        $row[$headers_dec[$key]] = ChidonShipping::getDescription($field);
                        break;
                    default:
                        $row[$headers_dec[$key]] = $result[$field];
                        if ($field == 'balance' || strpos($field, 'fee') !== false) {
                            $total_owed += intval($result[$field]);
                        }
                        break;
                }
            }
        }
        $data[] = $row;
    }
}

echo json_encode([
    'headers' => $headers,
    'data' => $data
]);
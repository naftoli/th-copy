<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

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
        'date_registered' => 'rc.date',
        'reg_fee'       => 'reg_fee',
        'reg_paid'      => 'rc.amount',
        'soldier_discount' => 'rc.discount',
        'total_balance' => 'total_balance'
    ],
    'details' => [
        'reg_type'      => 's.reg_type',
        'school_number' => 's.school_number',
        'school_name'   => 's.school_name',
        'user_serial'   => 'u.user_serial',
        'user_name'     => ['u.first', 'u.last'],
        'type'          => 'type_of_charge',
        'code'          => 'rc.type',
        'reg_date'      => 'rc.date',
        'reg_amount'    => 'rc.amount',
        'refunded'      => 'rc.refunded'
    ], 
    'settings' => [
        'school_number' => 's.school_number',
        'school_name'   => 's.school_name',
        'reg_type'      => 's.reg_type',
        'school_charge_date' => 's.school_charge_date',
        'chayolei_fee'  => 's.chayolei_fee',
        'chidon_fee'    => 's.chidon_fee',
        'balance'       => 's.balance',
        'child_fee'     => 's.child_fee',
        'early_bird'    => 's.early_bird',
        'registration_notes' => 's.registration_notes'
    ]
];

$report_type = $_POST['report_type'];

// build sql
$from = [];
$srd_qry = false;
$total_registered_chayolim = false;
// SELECT fields
$sql = "SELECT ";
if ($report_type == 'base' || $report_type == 'settings') {
    $sql .= "s.school_id, ";
} else if ($report_type == 'soldier') {
    $sql .= "u.user_id, u.school_id, ";
} else if ($report_type == 'details') {
    $sql .= "rc.registration_charge_id, rc.user_id, rc.school_id, ";
}
foreach ($_POST[$report_type . '_options'] as $option) {
    if ($option == 'type_of_charge') continue;
    $field = $fields[$report_type][$option];
    if ($field == 'reg_fee') $field = ['s.child_fee', 'rc.date'];
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
        if ($pos !== false) {
            $table = substr($f, 0, $pos);
            if (!in_array($table, $from)) {
                $from[] = $table;
            }
            if ($table != 'srd') { // for srd we will create a new qry
                $sql .= $f . ", ";
            }
        }
    }
}
// remove last comma
$sql = substr($sql, 0, strlen($sql) - 2);
// FROM tables
$sql .= " FROM ";
if ($report_type == 'base' || $report_type == 'settings') {
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
        ($report_type == 'settings' && $t != 's') ||
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
            else if ($report_type == 'details' && $t == 'u') $sql .= ' LEFT JOIN users u USING (user_id) ';
            else if ($report_type == 'soldier' && $t == 'rc') $sql .= ' LEFT JOIN registration_charges rc USING (user_id) ';
            else $sql .= ' LEFT JOIN ' . $tables[$t] . ' ' . $t . ' USING (school_id) ';
        }
    }
}
// WHERE clause
if ($report_type == 'base' || $report_type == 'settings') {
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
} else if ($report_type == 'soldier' && in_array('rc', $from)) {
    $sql .= " AND rc.year = :year AND rc.type = 'THE'";
} else if ($report_type == 'details') {
    $sql .= " AND rc.year = :year";
} 
if ($debug) {
    echo $sql;
    exit;
}

$stmt = $MASHPIA_DB->prepare($sql);
if (in_array('sr', $from) || in_array('rc', $from)) {
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
            if ($table == 'srd' && $field != 'discount') $field .= '_paid';
            if ($field == 'balance') $field = 'past_due';
            if ($report_type == 'soldier' && $field == 'amount') $field = 'reg_paid';
            else if ($field == 'type') $field = $key;
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
        $total_discount = 0;

        $row = [];
        if ($report_type == 'details') {
            $row['reg_charge_id'] = $result['registration_charge_id'];
        }
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

                if (in_array($field, ['school_id', 'user_id'])) {
                    $row[$field] = $result[$field];
                    continue;
                }
            
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
                        $total_balance = $total_owed - $total_paid - $total_discount;
                        // $row[$headers_dec[$key]] = "Total Owed: $total_owed, Total Paid: $total_paid, Total Discount: $total_discount";
                        $row[$headers_dec[$key]] = number_format($total_balance, 2);
                        break;
                    case 'total_paid':
                    case 'total_owed':
                        $row[$headers_dec[$key]] = number_format($$field, 2);
                        break;
                    case 'type_of_charge':
                        $row[$headers_dec[$key]] = ChidonShipping::getDescription($result['type']);
                        break;
                    case 'discount':
                        if ($table == 'srd') {
                            $value = abs($srd_report[$school_id][$field] ?? 0);
                        } else {
                            $value = $result[$field] ?? 0;
                        }
                        $total_discount = $value;
                        $row[$headers_dec[$key]] = number_format($value, 2);
                        break;
                    case 'reg_fee':
                        $early = true;
                        $registered = $result['date'];
                        if ($registered) {
                            $early_bird = GlobalSettings::earlyBird();
                            $reg_date = new DateTime($registered);
                            if ($reg_date > $early_bird) $early = false;
                        }
                        $fee = GlobalSettings::calculateChildFee(
                            $result['reg_type'],
                            $result['child_fee'],
                            false,
                            $early,
                            false,
                            false,
                            $result['school_id'] == 61, 
                            $result['school_id'] == 269
                        );
                        $total_owed += $fee;
                        $row[$headers_dec[$key]] = number_format($fee, 2);
                        break;
                    default:
                        $row[$headers_dec[$key]] = $result[$field];
                        if ($field == 'balance' || strpos($field, 'fee') !== false) { // balance is past_due
                            $total_owed += floatval($result[$field]);
                        } else if ($key == 'reg_paid') {
                            $total_paid += floatval($result[$field]);
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
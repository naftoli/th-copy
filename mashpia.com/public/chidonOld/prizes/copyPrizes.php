<?php
ini_set('display_errors', 1);
$admin_auth = ['school'];
require '../../header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

require_once './class.schoolExceptions.php';

$from = $_REQUEST['from'];
$to = $_REQUEST['to'];

$prizes = [];
$sql = "select * from chidon_prizes where year = $from";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $prizes[] = $row;
}

$qrys = [];
foreach ($prizes as $row) {
    $qrys[$row['prize_id']] = "insert into chidon_prizes
                set year = $to, 
                prize_picture = '" . $row['prize_picture'] . "', 
                prize_name = '" . $row['prize_name'] . "', 
                quantity = 500, 
                size = '" . $row['size'] . "', 
                color = '" . $row['color'] . "', 
                price = " . $row['price'] . ", 
                our_price = " . $row['our_price'] . ", 
                made_possible_by = '" . $row['made_possible_by'] . "', 
                personalization = '" . $row['personalization'] . "', 
                note = '" . $row['note'] . "', 
                purchased = 0";
}

mysql_query('set autocommit=0');
mysql_query('start transaction');

$success = true;
foreach ($qrys as $old_prize_id => $sql) {
    $school_exceptions = SchoolExceptions::getSchoolExceptionsByPrize($old_prize_id);
    if (! mysql_query($sql)) {
        $success = false;
        break;
    } else {
        $new_prize_id = mysql_insert_id();
        SchoolExceptions::updateSchoolExceptions($new_prize_id, $school_exceptions, true);
    }
}

if ($success) {
    mysql_query('commit');
    mysql_query('set autocommit=1');
    echo json_encode(['success' => true]);
} else {
    mysql_query('rollback');
    mysql_query('set autocommit=1');
    echo json_encode(['success' => false]);
}
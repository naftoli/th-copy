<?php
//$showRegister = array(2,3,4,9,11,21,40,48,54,55,60,61,66,80,81,84,86,87,89,105,106,112,162,194,263,264,265,269,427);
//$tuitionSchools = array(5,7,19,30,33,37,39,42,45,49,50,58,63,110,176,185,255);
//$fortyFive = array(54,48,81,61,269,7,263,58,2,21,37,4,49,192);
//$fifty = array(162);
require_once '../../../db.php';
$australia = array(55,66,110,112,256);

// reg_type of '1' means school pays for children, reg_type of '2' means school pays for children not registered by a certain date
// and therefore the charge is $45
$notRegistered = array();
$tuitionSchools = array();
$tuitionSchoolsNoPay = array();
$sql = "select school_id, reg_type from schools where reg_type in (1, 2)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    if ($row['reg_type'] == 1) {
        $tuitionSchoolsNoPay[] = $row['school_id'];
    } else if ($row['reg_type'] == 2) {
        $tuitionSchools[] = $row['school_id'];
    } else if ($row['reg_type'] == 0) {
        $notRegistered[] = $row['school_id'];
    }
}

$userFee = 50;
if (unixtojd() > 2458011) {
    $userFee = 55;
    $tuitionSchools = array();
    $tuitionSchoolsNoPay = array(265);
}

$extended = array(
    2   =>  50,
    4   =>  45,
    9   =>  45,
    21  =>  45,
    37  =>  45,
    162 =>  50,
    192 =>  50
);
?>
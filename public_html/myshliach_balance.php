<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'db.php';

$info = array();
$sql = "select * from registration where year = 5776";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[$row['school_id']][] = $row;
}

$total = array();
$total[61] = 0;
$total[269] = 0;

$totalPaid = array();
$totalPaid[61] = 0;
$totalPaid[269] = 0;

foreach ($info as $school => $other) {
    foreach ($other as $data) {
        $users = explode(',', $data['users']);
        $numUsers = count($users);
        $approval = explode(':', $data['approval']);
        $paid = $approval[3];
        $totalPaid[$school] += $paid;
        $subtract = 40 * $numUsers;
        $diff = $paid - $subtract;
        $total[$school] += $diff;
    }
}
echo "<pre>";
print_r($total);
print_r($totalPaid);
echo "</pre>";
?>
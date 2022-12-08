<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$ct = new ChidonTests();
$year = GlobalSettings::getChidonYear();

$marks = [];
$info = $_POST['mashpia_form'];
echo "<pre>"; print_r($marks); echo "</pre>"; exit;
foreach ($info as $row) {
    $serial = $row['serial_number'];
    // find out th_chidon_id
    $sql = "select th_chidon_id from th_chidon where year = $year and user_id = (
            select user_id from users where user_serial = $serial)";
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        $id = mysql_fetch_assoc($result)['th_chidon_id'];
        $details = [
            'maven'     => $row['yesod'],
            'pro'       => $row['yediah'],
            'expert'    => $row['havonah'],
            'genius'    => $row['iyun']
        ];
        foreach ($details as $type => $number) {
            $marks[$id][$row['test_number']][$type] = $number;
        }
    }
}
echo $ct->insertScores($marks);
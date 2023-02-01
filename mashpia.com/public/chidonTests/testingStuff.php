<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
$sql = "select u.user_id, u.school_id, u.class_id, tc.th_chidon_id 
        from users u 
        join th_chidon tc using (user_id) 
        where tc.year = 5783 
        and u.user_serial = 7772171";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);

require_once 'class.chidonTests.php';
$ct = new ChidonTests($row);
$trackInfo = $ct->getHighestTrackPassed($row);
$trackInfo2 = $ct->getHighestTrackPassed($row, 1);
echo "<pre>";
print_r($trackInfo);
print_r($trackInfo2);
echo "</pre>";

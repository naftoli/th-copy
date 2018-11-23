<?php
// create vars for shutting down marking page
$shutdown1 = false;
$shutdown2 = false;
$shutdown3 = false;
$today = date("Y-m-d H:i:s"); // date internal is 4 or 5 hrs ahead
//echo $today . "<br />";
/***************************************************
    After the deadline, only HQ can enter in marks.
    
 ***************************************************/

$date1 = "2018-11-21 05:00:00";
//$date1 = "2018-12-23 05:00:00";
$date2 = "2019-01-03 05:00:00";
$date3 = "2019-02-08 05:00:00";

if ($today > $date1) {
    $shutdown1 = true;
}
if ($today > $date2) {
    $shutdown2 = true;
}
if ($today > $date3) {
    $shutdown3 = true;
}

/* 
$date = "2017-02-16 05:00:00";
if ($today >= $date) {
    $shutdown = true;
}

$exceptions = array();
// allow OT until thursday at 5pm
if ($today < "2017-02-17 00:00:00") {
    $exceptions[] = 255;
}

// allow MyShliach until Mon. 02/21
if ($today < "2017-02-21 05:00:00") {
    $exceptions[] = 61;
}
*/
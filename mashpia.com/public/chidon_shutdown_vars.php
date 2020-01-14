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
$date1 = "2019-12-09 05:00:00";
$date2a = "2020-01-10 05:00:00";
$date2b = "2020-01-17 17:00:00";
$date3a = "2020-02-11 05:00:00";
$date3b = "2020-02-12 05:00:00";

if ($today > $date1) {
    $shutdown1 = true;
}
if ($today < $date2a || $today > $date2b) {
    $shutdown2 = true;
}
if ($today < $date3a || $today > $date3b) {
    $shutdown3 = true;
}

// close other marking until further notice
// $shutdown2 = true;
// $shutdown3 = true;

/* 
$exceptions = [];
// allow OT until thursday at 5pm
if ($today < "2017-02-17 00:00:00") {
    $exceptions[] = 255;
}
*/
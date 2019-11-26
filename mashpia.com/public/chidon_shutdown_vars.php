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
$date1 = "2019-11-30 05:00:00";
$date2 = "2020-01-26 05:00:00";
$date3 = "2020-03-26 05:00:00";

if ($today > $date1) {
    $shutdown1 = true;
}
if ($today > $date2) {
    $shutdown2 = true;
}
if ($today > $date3) {
    $shutdown3 = true;
}

// close other marking until further notice
$shutdown2 = true;
$shutdown3 = true;

/* 
$exceptions = [];
// allow OT until thursday at 5pm
if ($today < "2017-02-17 00:00:00") {
    $exceptions[] = 255;
}
*/
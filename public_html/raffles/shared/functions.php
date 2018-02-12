<?php
// get the parshas
require_once $_SERVER["DOCUMENT_ROOT"].'/class.globalSettings.php'; // import the global settings file

// checks for YYYY/MM/DD or MM/DD/YY
function check_valid_date($date_string){
    return  preg_match('/^(0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])[- \/.](19|20)\d\d$/', $date_string) ||
            preg_match('/^(19|20)\d\d[- \/.](0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])$/', $date_string);
}
// formats julian dates
function formatJdToDate($jd, $format="Y-m-d"){
    return date($format, jdtounix($jd)); // cast to unix timestamp and get the date
}

function get_parshos($year=false) { // set the year to be an optional paramater
    if(!$year) $year = GlobalSettings::getCurrentYear(); // if a year is not passed in get it from the global settings
    $result = []; // the parshios
    
    $sql = "SELECT * FROM parshos WHERE year = $year;"; // get the parshios for the year
    $query = mysql_query($sql);
    while($row = mysql_fetch_assoc($query)){
        $result[$row['id']] = $row; // set the associative array to look like parsha_id => parsha info
    }
    return $result;
}
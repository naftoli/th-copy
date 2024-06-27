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
function parseDateToJd($date, $format="Y-m-d"){
    $parsed_date = DateTime::createFromFormat($format, $date);
    if (!$parsed_date) return null;
    $unix_date = $parsed_date->getTimestamp();
    if (!$unix_date) return null;
    $jd_date = unixtojd($unix_date);
    if (!$jd_date) return null;
    return $jd_date;
}

function get_parshos($year=false) { // set the year to be an optional paramater
    $year = GlobalSettings::getCurrentYear();
    $nextYr = $year + 1;
    $result = []; // the parshios
    $dates = GlobalSettings::getCurYearDates();
    $sql = "SELECT * FROM parshos WHERE (start >= " . $dates['start'] . " AND end <= " . $dates['end'] . ") OR year IN ($year, $nextYr) ORDER BY id";
    $query = mysql_query($sql);
    while($row = mysql_fetch_assoc($query)){
        $result[$row['id']] = $row; // set the associative array to look like parsha_id => parsha info
    }
    return $result;
}
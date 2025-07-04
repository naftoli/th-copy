<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require('../../header.php');

if ($admin_user['auth'] != 'super') {
    echo "You are not authorized to access this page.";
    exit;
}

function getDates($year) {
    $dates = [];
    $sql = "SELECT * FROM system_dates WHERE year = $year order by jd_date";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $dates[] = jdtogregorian($row['jd_date']);
    }
    return $dates;
}

function convertDateToJd($date) {
    $date_info = explode('-', $date);
    if (count($date_info) !== 3) {
        return false;
    }
    $yr = isset($date_info[0]) ? intval($date_info[0]) : 0;
    $mm = isset($date_info[1]) ? intval($date_info[1]) : 0;
    $dd = isset($date_info[2]) ? intval($date_info[2]) : 0;
    
    if ($yr <= 0 || $mm <= 0 || $dd <= 0) {
        return false;
    }
    
    return gregoriantojd($mm, $dd, $yr);
}

function addDate($year, $date) {
    $jd = convertDateToJd($date);
    if ($jd === false) {
        return false;
    }
    $sql = "INSERT INTO system_dates (year, jd_date) VALUES (" . $year . ", " . $jd . ")";
    $result = mysql_query($sql);
    return $result;
}

function deleteDate($year, $date) {
    $jd = convertDateToJd($date);
    if ($jd === false) {
        return false;
    }
    $sql = "DELETE FROM system_dates WHERE year = " . $year . " AND jd_date = " . $jd;
    $result = mysql_query($sql);
    return $result;
}

function updateDate($year, $date, $old_date) {
    $jd = convertDateToJd($date);
    $old_jd = convertDateToJd($old_date);
    if ($jd === false || $old_jd === false) {
        return false;
    }
    $sql = "UPDATE system_dates SET jd_date = " . $jd . " WHERE year = " . $year . " AND jd_date = " . $old_jd;
    $result = mysql_query($sql);
    return $result;
}

$action = $_GET['action'];
if ($action == 'getDates') {
    $dates = getDates($_GET['year']);
    echo json_encode([
        'success' => true,
        'dates' => $dates
    ]);
} else if ($action == 'addDate') {
    $result = addDate($_GET['year'], $_GET['date']);
    if ($result) {
        $dates = getDates($_GET['year']);
        echo json_encode([
            'success' => true,
            'dates' => $dates
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to add date'
        ]);
    }
} else if ($action == 'deleteDate') {
    $result = deleteDate($_GET['year'], $_GET['date']);
    if ($result) {
        $dates = getDates($_GET['year']);
        echo json_encode([
            'success' => true,
            'dates' => $dates
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to delete date'
        ]);
    }
} else if ($action == 'updateDate') {
    $result = updateDate($_GET['year'], $_GET['date'], $_GET['old_date']);
    if ($result) {
        $dates = getDates($_GET['year']);
        echo json_encode([
            'success' => true,
            'dates' => $dates
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to update date'
        ]);
    }
}
<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 300);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . "/header.php";

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/class.bpSummary.php';
require $_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php";
$year = GlobalSettings::getCurrentYear();

function updateLines($obj) {
    global $year;

    $user_id = $obj->soldier_pk;
    $mishna = $obj->LINES_mishna;
    $tanya = $obj->LINES_tanya;

    // get school and class id's
    $sql = "select * from users where user_id = " . $user_id;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $school_id = $row['school_id'];
    $class_id = $row['class_id'];

    // get correct campaign id's
    $campaigns = [];
    $sql = 'select * from line_campaigns where year = ' . $year;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $campaigns[strtolower($row['type'])] = $row['id'];
    }

    foreach ($campaigns as $type => $campaign_id) {
        $sql = "select * from lines_learned where user_id = " . $user_id . " and campaign_id = " . $campaign_id;
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
            // update table
            $sql2 = "update lines_learned set lines_learned = " . $$type . " where campaign_id = " . $campaign_id . " 
                    and user_id = " . $user_id;
        } else {
            // create new row
            $sql2 = "insert into lines_learned set user_id = " . $user_id . ", campaign_id = " . $campaign_id . ", 
                    lines_learned = " . $$type . ", school_id = " . $school_id . ", class_id = " . $class_id;
        }
        echo $sql2 . "<br /><br />";
    }
}

function updateSummary($campaign_id, $user_id) {
    $bps = new BpSummary( $campaign_id, 'user' );
    $bps->updateSummary( $user_id );
}

$info = json_decode(file_get_contents("https://chabadkid.com/getuser.php?mashpia=mashpia_mbp_all"));
foreach ($info as $obj) {
    updateLines($obj);
}
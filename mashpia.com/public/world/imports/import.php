<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 300);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . "/header.php";
require $_SERVER['DOCUMENT_ROOT'] . '/class.bpSummary.php';
require $_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php";
$year = GlobalSettings::getCurrentYear();

// get correct campaign id's
$campaigns = [];
$sql = 'select * from line_campaigns where year = ' . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $campaigns[strtolower($row['type'])] = $row['id'];
}

function createUpdates($obj) {
    global $year, $campaigns;

    $user_id = $obj->soldier_pk;
    $mishna = $obj->LINES_mishna;
    $tanya = $obj->LINES_tanya;

    // get school and class id's
    $sql = "select * from users where user_id = " . $user_id;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $school_id = $row['school_id'];
    $class_id = $row['class_id'];

    $qrys = [];
    foreach ($campaigns as $type => $campaign_id) {
        if ($$type > 0) {
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
            $qrys[] = $sql2;
        }
    }

    return $qrys;
}

function updateSummary($campaign_id, $user_id) {
    $bps = new BpSummary( $campaign_id, 'user' );
    $bps->updateSummary( $user_id );
}

function updateUsersSummary() {
    global $user_ids, $campaigns;
    foreach ($user_ids as $id) {
        foreach ($campaigns as $campaign_id) {
            updateSummary($campaign_id, $id);
        }
    }
}

if (isset($_GET['school'])) {
    $allQrys = [];
    $user_ids = [];
    $info = json_decode(file_get_contents("https://chabadkid.com/getuser.php?mashpia=mashpia_mbp_all&school=" . $_GET['school']));

    if (empty($info)) {
        echo json_encode([
            'success'   => 'false',
            'error'     => 'There are no lines learned available from chabadkid.com'
        ]);
        exit;
    }

    foreach ($info as $obj) {
        $user_ids[] = $obj->soldier_pk;
        $qrys = createUpdates($obj);
        array_push($allQrys, $qrys);
    }

    echo "<pre>";
    print_r($allQrys);
    echo "</pre>";
    exit;
    $success = true;
    mysql_query('set autocommit=0');
    mysql_query('begin');
    foreach ($qrys as $qry) {
        if (!mysql_query($qry)) {
            $success = false;
            break;
        }
    }
    if ($success) {
        mysql_query('commit');
        mysql_query('set autocommit=1');
        updateUsersSummary();
        echo json_encode([
            'success'   => true
        ]);
    } else {
        mysql_query('rollback');
        mysql_query('set autocommit=1');
        echo json_encode([
            'success'   => false,
            'error'     => 'Error updating the database.'
        ]);
    }
} else {
    echo json_encode([
        'success'   => false,
        'error'     => 'No school number was found.'
    ]);
}

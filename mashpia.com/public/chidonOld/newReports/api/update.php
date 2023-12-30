<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$input = json_decode(file_get_contents('php://input'), true);
$field = $input['field'];
$serial = $input['serial'];
$value = $input['value'];

if ($field == 'passing_avg') updateAvg($serial, $value);
else updateField($serial, $field, $value);

function updateAvg($serial, $value) {
    global $year;

    $tracks = ['maven', 'pro', 'expert', 'genius'];
    $sql = "UPDATE chidon_passing_avgs 
            SET  
                avg = '$value' 
            WHERE 
                user_id = (SELECT user_id FROM users WHERE user_serial = '$serial') AND 
                track IN ('" . implode("','", $tracks) . "') AND 
                year = $year";
    $result = mysql_query($sql);

    echo json_encode([
        'success'   => $result,
        'sql'       => $sql,
        'error'     => 'Could not update avg for ' . $serial . ' to ' . $value . '.'
    ]);
}

function updateField($serial, $field, $value) {
    global $year;

    $table_fields = [
        'track' => 'test_type',
        'reward' => 'reward_type',
        'award' => 'award_type',
        'dropped_out' => 'dropped_out',
        'reason'    => 'reason',
    ];
    $field = $table_fields[$field];

    $sql = "UPDATE th_chidon 
            SET $field = '$value'
            WHERE user_id = (SELECT user_id FROM users WHERE user_serial = '$serial') AND year = " . $year;
    $result = mysql_query($sql);

    echo json_encode([
        'success'   => $result,
        'sql'       => $sql,
        'error'     => 'Could not update ' . $field . ' for ' . $serial . ' to ' . $value . '.'
    ]);
}
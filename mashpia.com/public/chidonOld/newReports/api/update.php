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

    mysql_query('SET AUTOCOMMIT=0');
    mysql_query('START TRANSACTION');

    $tracks = ['maven', 'pro', 'expert', 'genius'];
    foreach ($tracks as $track) {
        $sql = "INSERT IGNORE INTO chidon_passing_avgs 
            SET  
                user_id = (SELECT user_id FROM users WHERE user_serial = '$serial'), 
                track = '$track',
                avg = '$value', 
                year = $year 
            ON DUPLICATE KEY UPDATE avg = '$value'";
        $result = mysql_query($sql);
        if (!$result) {
            echo json_encode([
                'success'   => false,
                'sql'       => $sql,
                'error'     => 'Could not update avg for ' . $serial . ' to ' . $value . '.'
            ]);
            mysql_query('ROLLBACK');
            mysql_query('SET AUTOCOMMIT=1');
            exit;
        }
    }

    mysql_query('COMMIT');
    mysql_query('SET AUTOCOMMIT=1');

    echo json_encode([
        'success'   => $result,
        'sql'       => $sql,
        'error'     => 'Could not update avg for ' . $serial . ' to ' . $value . '.'
    ]);
}

function updateField($serial, $field, $value) {
    global $year;

    $table_fields = [
        'track'         => 'test_type',
        'reward'        => 'reward_type',
        'award'         => 'award_type',
        'dropped_out'   => 'dropped_out',
        'reason'        => 'reason',
        'khk_experience' => 'khk_experience',
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
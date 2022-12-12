<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

$year = mysql_real_escape_string($_POST['year']);

// get all the schools connected to this admin
$schools = $admin_user['auths']['school'];

$success = true;
if (count($schools) > 1) {
    mysql_query('set autocommit=0');
    mysql_query('begin');
}
foreach ($schools as $school_id) {
    $sql = "insert ignore into chidon_confirmations set year = $year, school_id = " . $school_id;
    if (! mysql_query($sql)) {
        $success = false;
        break;
    }
}
if (count($schools) > 1) {
    if ($success) mysql_query('commit');
    else mysql_query('rollback');
    mysql_query('set autocommit=1');
}

echo json_encode([
    'success'   => $success,
    'msg'       => 'Your school(s) has been confirmed. Your children will now be able to register for the Chidon Experience.',
    'error'     => 'There was an error saving your confirmation.',
    'info'      => $sql . "\n" . mysql_error()
]);
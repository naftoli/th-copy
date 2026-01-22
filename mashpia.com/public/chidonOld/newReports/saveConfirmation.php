<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';

$year = mysql_real_escape_string($_POST['year']);
// get all the schools connected to this admin
$schools = $admin_user['auths']['school'];

$MASHPIA_DB->beginTransaction();
$success = true;
$sql = "INSERT IGNORE INTO chidon_confirmations (year, school_id) VALUES (:year, :school_id)";
$stmt = $MASHPIA_DB->prepare($sql);
foreach ($schools as $school_id) {
    $res = $stmt->execute([
        ':year' => $year,
        ':school_id' => $school_id,
    ]);
    if (! $res) {
        $success = false;
        break;
    }
}
if ($success) $MASHPIA_DB->commit();
else $MASHPIA_DB->rollBack();

echo json_encode([
    'success'   => $success,
    'msg'       => 'Your school(s) has been confirmed. Your children will now be able to register for the Chidon Experience.',
    'error'     => 'There was an error saving your confirmation.',
    'info'      => $sql . "\n" . $stmt->errorInfo()[2]
]);
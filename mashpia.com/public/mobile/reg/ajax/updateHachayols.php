<?php
$admin_auth = ['user'];
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = intval(GlobalSettings::getCurrentYear());

if ($year < 5786) {
    $stmt = $MASHPIA_DB->prepare("update users set hachayol = :val where user_id = :user");
} else {
    $stmtUpdate = $MASHPIA_DB->prepare("
        INSERT IGNORE INTO hachayols_to_give (user_id, year) VALUES (:user, :year)
    ");
    $stmtDelete = $MASHPIA_DB->prepare("
        DELETE FROM hachayols_to_give WHERE user_id = :user AND year = :year
    ");
}
$users = $_POST['list'];

$MASHPIA_DB->beginTransaction();
foreach ($users as $user) {
    if ($year < 5786) {
        $res = $stmt->execute([
            'user'  => $user['user_id'],
            'val'   => intval($user['checked'])
        ]);
    } else {
        $checked = intval($user['checked']);
        if ($checked) {
            $res = $stmtUpdate->execute([
                'user'  => $user['user_id'],
                'year'  => $year
            ]);
        } else {
            $res = $stmtDelete->execute([
                'user'  => $user['user_id'],
                'year'  => $year
            ]);
        }
    }
    if (!$res) {
//        $stmt->debugDumpParams();
        $MASHPIA_DB->rollBack();
        echo json_encode([
            'success'   => false,
            'error'     => 'There was an error updating you hachayol settings.'
        ]);
    }
}
// if we get here all is good
$MASHPIA_DB->commit();
echo json_encode([
    'success'   => true
]);

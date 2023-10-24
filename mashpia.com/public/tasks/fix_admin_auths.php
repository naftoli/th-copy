<?php
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';


$insertStmt = $MASHPIA_DB->prepare("
    insert into admin_auths 
    set admin_id = :admin, 
    auth = :auth, 
    id = :id, 
    role_id = :role, 
    position = :position");

$sql = "SELECT * FROM mashpia_backup.admin_auths where id not in (select user_id from users) and auth != 'user'";
$stmt = $MASHPIA_DB->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $insertStmt->execute([
        ':admin' => $row['admin_id'],
        ':auth' => $row['auth'],
        ':id' => $row['id'],
        ':role' => $row['role_id'],
        ':position' => $row['position']
    ]);
}
echo "done";
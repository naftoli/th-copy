<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$id = $_GET['id'];
if (!$id) {
    echo "No ID provided.";
    exit;
}

$stmt = $MASHPIA_DB->prepare("SELECT admin_id FROM admin_auths WHERE id = ? and auth = 'user'");
$stmt->execute([$id]);
$result = $stmt->fetch();
if (!$result) {
    echo "No user found.";
    exit;
}

$admin_id = $result['admin_id'];
$sql = "SELECT * FROM admin_auths aa 
        LEFT JOIN users u ON u.user_id = aa.id
        WHERE admin_id = ? AND auth = 'user'";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([$admin_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$deleted = [];
foreach ($rows as $row) {
    if (!$row['user_id']) {
        $deleted[] = $row['id'];
    }
}

$sql = "DELETE FROM admin_auths WHERE id IN (" . implode(',', $deleted) . ") AND admin_id = ?";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([$admin_id]);

echo "Deleted: " . implode(', ', $deleted);
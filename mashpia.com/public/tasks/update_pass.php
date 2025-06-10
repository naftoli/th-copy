<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once '../header.php';

if ($admin_user['auth'] != 'super') {
    die('Unauthorized');
}

require_once '../api/header/db.php';
require_once '../../includes/globals.php';

// $encrypted = encryptPassword($password, $key);
// $decrypted = decryptPassword($encrypted, $key);

$key = ENCRYPTION_KEY;

$stmt = $MASHPIA_DB->query("SELECT * FROM admins");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($admins as $admin) {
    $stmt = $MASHPIA_DB->prepare("UPDATE admins SET password = :password WHERE admin_id = :admin_id");
    $stmt->execute([
        ':password' => encryptPassword($admin['password'], $key),
        ':admin_id' => $admin['admin_id']
    ]);
    echo $admin['admin_id'] . " updated\n";
}
echo "Done.";
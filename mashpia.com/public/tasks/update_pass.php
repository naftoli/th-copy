<?php
$admin_auth = ['school'];
require_once '../../header.php';

if ($admin_user['auth'] != 'super') {
    die('Unauthorized');
}

require_once '../../api/header/db.php';
require_once '../../includes/globals.php';

// Encryption functions
function encryptPassword($password, $key) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($password, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function decryptPassword($encryptedPassword, $key) {
    list($encrypted_data, $iv) = explode('::', base64_decode($encryptedPassword), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

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
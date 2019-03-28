<?php
require __DIR__ . '/../encrypt.php';
$admin = $_COOKIE['chidon_admin'];
$admin_id = encrypt_decrypt('decrypt', $admin);
echo $admin_id;
<?php
// require __DIR__ . '/../encrypt.php';

// $admin = $_POST['admin'];
// $admin_id = encrypt_decrypt('decrypt', $admin);

setcookie('chidon_admin',  $_GET['admin'], time() + 86400, "/");
header("Location: chidondrive.com/site/enroll.html");
exit;
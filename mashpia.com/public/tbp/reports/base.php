<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Base TBP Report</title>
    <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
<h1>Base TBP Report</h1>
<?php
require '../class.tbp.php';
$t = new TanyaBalPeh();
$t->setQuota('base', 255);
$t->setDone('base', 255);
$quota = $t->getQuota();
$done = $t->getDone();
echo "Quota: " . $quota;
echo "Done: " . $done;
?>
</body>
</html>
<?php

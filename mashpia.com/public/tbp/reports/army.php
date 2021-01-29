<?php
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
    <title>Army Wide TBP Report</title>
    <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
<h1>Army Wide TBP Report</h1>
<?php
require '../class.tbp.php';
$t = new TanyaBalPeh();
$t->setQuota('army');
$quota = $t->getQuota();
echo $quota;
?>
</body>
</html>

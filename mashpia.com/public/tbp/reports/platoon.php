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
    <title>Platoon TBP Report</title>
    <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
<h1>Platoon TBP Report</h1>

</body>
</html>
<?php

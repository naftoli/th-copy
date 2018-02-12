<?php
ini_set('display_errors',1);
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Shabbos Mevorchim Tehillim Report</title>
<style type='text/css'>
@media all {
    .page-break {
        display: none;
    }
    .hayomYom {
        float: right;
        width: 300px;
        padding-right: 10px; 
        line-height: 1.5em;
    }
    .logo {
        float: left;
        margin-right: 20px;
    }
    .top {
        margin-left: auto;
        margin-right: auto;
        text-align: center;
    }
    .main {
        margin-left: auto;
        margin-right: auto;
    }
}
@media print {
    .page-break {
        display: block;
        page-break-after: always;
    }
    tr, th, td {
        font-size: 14px;
    }
    .no-print {
        display: none;
    }
    hr {
        display: none;
    }
}
tr, th, td {
    border: 1px solid black;
    padding: 10px;
    font-size: 12px;
}
</style>
</head>

<body>
<? 
require_once 'admin_header.php';
require_once 'class.shabbosMevorchim.php';

$sm = new ShabbosMevorchim();
$sm->setReportDates($_GET['date']);
$sm->setAccomplishedOnly();
$reportDates = $sm->getReportDates();
$date = end($reportDates);
$key = key($reportDates);
	
//$sm->setArmyResults();
?>
<p style="font-size: 36px;">שבת מברכים כסליו</p>
<? 
require_once 'class.adminSchools.php';      
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$ids = $as->getSchools();

foreach ( $ids as $id => $name ) {
    $sm->setSchool( $id );
	$sm->setClassResults();
    echo "<h2>" . $name . "</h2>";
    $sm->generateClassSummary( $key, $date );
}
?>
</body>
</html>
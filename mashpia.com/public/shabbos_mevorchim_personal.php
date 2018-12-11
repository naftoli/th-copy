<? 
$admin_auth = array('school','user'); 
require('header.php');

require_once 'class.adminSchools.php';      
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools(); 
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
    .user {
    	float: left;
    	margin-right: 25px;
    	margin-bottom: 25px;
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
    border: 1px dashed black;
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
$sm->setReportDates();
?>
<div class='no-print'>
    <h1>Shabbos Mevorchim Tehillim Report</h1>
    <div align='center'>
        <input type='button' value='Print' onclick='window.print()'>
    </div>
</div>
<br />
<? 
require_once 'class.adminSchools.php';      
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$ids = $as->getSchools();

foreach ( $ids as $id => $name ) {
	if (count($ids) > 1) {
		echo "<h2>" . $name . "</h2>";
		echo "<div class='page-break'></div>";
	}
    $sm->setSchool( $id );
    $sm->setUsersResults();
	$sm->generateUsersReport();
    ?>
<? } ?>
</body>
</html>
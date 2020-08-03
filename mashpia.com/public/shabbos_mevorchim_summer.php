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
tr, th, td {
    padding: 10px;
    border: 1px solid black;
    font-size: 12px;
}
</style>
</head>

<body>
<? 
require_once 'admin_header.php';
require_once 'class.shabbosMevorchim.php';
$sm = new ShabbosMevorchim();
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

$info = [];
foreach ( $ids as $id => $name ) {
	// if (count($ids) > 1) {
	// 	echo "<h2>" . $name . "</h2>";
	// 	echo "<div class='page-break'></div>";
    // }
    foreach ([2459049,2459077] as $date) {
        $sm->setReportDates($date);
        $sm->setSchool($id);
        $sm->setStudentResults();
        $quotas = $sm->getStudentResults();
        $done = $sm->getStudentDoneResults();
        echo "<pre>"; print_r($quotas); print_r($done); echo "</pre>";
        // foreach ($quotas as $date => $other) {
        //     foreach ($other as $grade => $more) {
        //         foreach ($more as $user_id => $values) {
        //             foreach ($values as $task => $quota) {
        //                 $result = intval($done[$date][$grade][$user_id][$task]);
        //                 if ($result && $result >= intval($quota)) {
        //                     $info[$id][$user_id][$date] = [
        //                         'quota' =>  $quota, 
        //                         'done'  =>  $result
        //                     ];
        //                 }
        //             }
        //         }
        //     }
        // }
    }
} 
echo "<pre>"; 
print_r( $info );
echo "</pre>";
?>
</body>
</html>
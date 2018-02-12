<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Hachayol Report</title>
<style type='text/css'>
.page-break {
    page-break-after: always;
}
@media print {
    .hide {
        display: none;
    }
}
</style>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>
<h1 class='hide'>Hachayol Report</h1>
<?
require_once 'class.hachayol.php';
$h = new Hachayol;
$h->setSchools();
$schools = $h->getSchools();

//variables for grand totals
$grandTotal = 0;
$totals['pickup'] = 0;
$totals['deliver'] = 0;

echo "<div align='center' class='hide'><input type='button' value='Print' onclick='window.print()' /></div>";

echo "<h2 class='hide'>For Pickup</h2>";
foreach( $schools['pickup'] as $school ) {
    echo "For Pickup<br />"; 
    echo $school['name'] . "<br />";
    echo $school['address'] . "<br />";
    foreach( $school['admins'] as $admin ) {
        echo "Admin: " . $admin . "<br />";
    }
    echo "Total Teachers: " . $school['teachers'] . "<br />";
    echo "Total Registered children: " . $school['total'] . "<br />";
    $total = $school['teachers'] + $school['total'];
    echo "Total: " . $total . "<br /><br />";
    $grandTotal += $total;
    $totals['pickup'] += $total;
    echo "<div class='page-break'></div>";
}

echo "<h2>For Delivery</h2>"; 
foreach ( $schools['deliver'] as $school ) {
    echo "For Delivery<br />";
    echo $school['name'] . "<br />";
    echo $school['address'] . "<br />";
    foreach( $school['admins'] as $admin ) {
        echo "Admin: " . $admin . "<br />";
    }
    echo "Total Teachers: " . $school['teachers'] . "<br />";
    echo "Total Registered children: " . $school['total'] . "<br />";
    $total = $school['teachers'] + $school['total'];
    echo "Total: " . $total . "<br /><br />";
    $grandTotal += $total;
    $totals['deliver'] += $total;
    echo "<div class='page-break'></div>";
}

echo "<h2>Totals</h2>";
echo "Total for Pickup: " . $totals['pickup'] . "<br />";
echo "Total for Delivery: " . $totals['deliver'] . "<br />";
echo "Grand Total: " . $grandTotal;
 
else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>

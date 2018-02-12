<?php
ini_set('display_errors', 1);
$admin_auth = array('school','user'); 
require('header.php');

$posters = array(
	269	=> 0,
	162	=> 2,
	30	=> 2,
	176	=> 2,
	45	=> 2,
	2	=> 2,
	54	=> 5,
	7	=> 2,
	66	=> 0,
	112	=> 0,
	105	=> 1,
	63	=> 2,
	55	=> 0,
	81	=> 2,
	192	=> 2,
	49	=> 2,
	89	=> 1,
	106	=> 2,
	470	=> 1,
	5	=> 2,
	50	=> 1,
	21	=> 1,
	37	=> 1,
	263	=> 1,
	4	=> 2,
	60	=> 1,
	21	=> 1,
	264	=> 1,
	33	=> 1,
	185	=> 2,
	80	=> 0,
	110	=> 0,
	194	=> 1,
	3	=> 1,
	265	=> 1,
	19	=> 2,
	42	=> 2,
	39	=> 1,
	9	=> 4,
	61	=> 0,
	255	=> 2,
	471	=> 2,
	48	=> 1,
	58	=> 2,
	84	=> 1,
	427	=> 1,
	87	=> 1,
	11	=> 1,
	40	=> 1
);

$types = array(
	269	=> 'mixed',
	162	=> 'girls',
	30	=> 'girls',
	176	=> 'girls',
	45	=> 'girls',
	2	=> 'girls',
	54	=> 'girls',
	7	=> 'girls',
	66	=> 'girls',
	112	=> 'boys',
	105	=> 'girls',
	63	=> 'boys',
	55	=> 'mixed',
	81	=> 'mixed',
	192	=> 'girls',
	49	=> 'boys',
	89	=> 'mixed',
	106	=> 'mixed',
	470	=> 'mixed',
	5	=> 'boys',
	50	=> 'girls',
	21	=> 'boys',
	37	=> 'girls',
	263	=> 'mixed',
	4	=> 'boys',
	60	=> 'boys',
	21	=> 'mixed',
	264	=> 'boys',
	33	=> 'boys',
	185	=> 'mixed',
	80	=> 'mixed',
	110	=> 'mixed',
	194	=> 'mixed',
	3	=> 'boys',
	265	=> 'girls',
	19	=> 'boys',
	42	=> 'girls',
	39	=> 'mixed',
	9	=> 'boys',
	61	=> 'mixed',
	255	=> 'boys',
	471	=> 'boys',
	48	=> 'boys',
	58	=> 'boys',
	84	=> 'mixed',
	427	=> 'mixed',
	87	=> 'mixed',
	11	=> 'boys',
	40	=> 'girls'
);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Hachayol Report</title>
<style type='text/css'>
.info {
	font-size: 14px;
}
.page-break {
    page-break-after: always;
}
.students {
    margin-left: 50px;
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
<h1 class='hide'>Hachayol Report</h1>
<?

require_once 'class.hachayol.php';
$h = new Hachayol;

//find out if admin is super
if ( $admin->auth == 'super' ) {
    $h->setSchools();    
} else {
    $h->setSchools( $admin->school_id );
}

$schools = $h->getSchools();

//variables for grand totals
$grandTotal = 0;
$totals['pickup'] = 0;
$totals['deliver'] = 0;

// hard coded totals
$exceptions = array(
    //54	=> 550,
    54	=> 580, // update requested via E-mail on 1/3/2018
    176	=> 84 
);


echo "<div align='center' class='hide'><input type='button' value='Print' onclick='window.print()' /></div>";

if ( isset( $schools['pickup'] ) ) {
    foreach( $schools['pickup'] as $id => $school ) {
    	echo "<h2>For Pickup</h2>";
		echo "<div class='info'>";
        echo $school['name'] . "<br />";
        echo $school['address'] . "<br /><br />";
		echo "Type of School: " . $types[$id] . "<br />";
		echo "Principal: " . $school['principal'] . "<br />";
        foreach( $school['admins'] as $admin ) {
			$admin = trim($admin);
			if (!empty($admin)) echo "Admin: " . $admin . "<br />";
        }
        echo "Total Teachers: " . $school['teachers'] . "<br />";
        echo "Total Registered children: " . $school['total'] . "<br />";
		if (array_key_exists($id, $exceptions)) $total = $exceptions[$id];
        else $total = $school['teachers'] + $school['total'];
        echo "Total: " . $total . "<br />";
		echo "Already Registered for Chidon: " . $school['chidonReg'] . "<br />";
		echo "Number of posters: " . $posters[$id] . "<br />";
		echo "Shipping Requests: " . $school['shipping_requests'] . "<br /><br />";
        $grandTotal += $total;
        $totals['pickup'] += $total;
        echo "</div><div class='page-break'></div>";
    }
}

if ( isset( $schools['deliver'] ) ) {
    foreach ( $schools['deliver'] as $id => $school ) {
    	echo "<h2>For Delivery</h2>";
		echo "<div class='info'>";
        echo $school['name'] . "<br />";
        echo $school['address'] . "<br /><br />";
		echo "Type of School: " . $types[$id] . "<br />";
        foreach( $school['admins'] as $admin ) {
            echo "Admin: " . $admin . "<br />";
        }
        echo "Total Teachers: " . $school['teachers'] . "<br />";
        echo "Total Registered children: " . $school['total'] . "<br />";
		if (array_key_exists($id, $exceptions)) $total = $exceptions[$id];
        else if ($id != 162) $total = $school['teachers'] + $school['total'];
		else $total = $school['total'];
        echo "Total: " . $total . "<br />";
		echo "Already Registered for Chidon: " . $school['chidonReg'] . "<br />";
		echo "Number of posters: " . $posters[$id] . "<br />";
		echo "Shipping Requests: " . $school['shipping_requests'] . "<br /><br />";
        $grandTotal += $total;
        $totals['deliver'] += $total;
        echo "</div><div class='page-break'></div>";
    }
}

echo "<h2>For Pickup</h2>";
echo "<div class='info'>";
echo "Warehouse - 300<br />";
echo "Shterna Karp - 75<br />";
echo "Shuls - 100";
echo "</div><div class='page-break'></div>";
$totals['pickup'] += 475;

echo "<h2>Totals</h2>";
echo "Total for Pickup: " . $totals['pickup'] . "<br />";
echo "Total for Delivery: " . $totals['deliver'] . "<br />";
echo "Grand Total: " . $grandTotal;
?>
</body>
</html>

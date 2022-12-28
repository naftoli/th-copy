<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Hachayol Detail Report</title>
<style type='text/css'>
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
<h1 class='hide'>Hachayol Detail Report</h1>
<?
require_once 'class.hachayol.php';
$h = new Hachayol;

//find out if admin is super
if ( $admin->auth == 'super' ) {
    $h->setSchools();    
    $h->setSchoolDetails();
} else {
    $h->setSchools( $admin->school_id );
    $h->setSchoolDetails( $admin->school_id );
}

$schools = $h->getSchools();
$schoolDetails = $h->getSchoolDetails();
//echo "<pre>";
//print_r( $schoolDetails );
//echo "</pre>";

//variables for grand totals
$grandTotal = 0;
$totals['pickup'] = 0;
$totals['deliver'] = 0;

echo "<div align='center' class='hide'><input type='button' value='Print' onclick='window.print()' /></div>";

if ( isset( $schools['pickup'] ) ) {
    echo "<h2>For Pickup</h2>";
    foreach( $schools['pickup'] as $id => $school ) {
        echo $school['name'] . "<br />";
        echo $school['address'] . "<br />";
        foreach( $school['admins'] as $admin ) {
            echo "Admin: " . $admin . "<br />";
        }
        echo "Total Teachers: " . $school['teachers'] . "<br />";
        echo "Total children getting Hachayol: " . $school['total'] . "<br />";
        $total = $school['teachers'] + $school['total'];
        echo "Total: " . $total . "<br /><br />";
        $grandTotal += $total;
        $totals['pickup'] += $total;
        
        foreach ( $schoolDetails[$id] as $class => $users ) {
            echo "School: " . $school['name'] . "<br />";    
            echo "Grade: " . $class . "<br />";
            echo "<div class='students'>";
            foreach ( $users as $user ) {
                echo $user . "<br />";
            }
            echo "</div><br />";
        }
        echo "<div class='page-break'></div>";
    }
}

if ( isset( $schools['deliver'] ) ) {
    echo "<h2>For Delivery</h2>"; 
    foreach ( $schools['deliver'] as $id => $school ) {
        echo $school['name'] . "<br />";
        echo $school['address'] . "<br />";
        foreach( $school['admins'] as $admin ) {
            echo "Admin: " . $admin . "<br />";
        }
        echo "Total Teachers: " . $school['teachers'] . "<br />";
        echo "Total children getting Hachayol: " . $school['total'] . "<br />";
        $total = $school['teachers'] + $school['total'];
        echo "Total: " . $total . "<br /><br />";
        $grandTotal += $total;
        $totals['deliver'] += $total;
        
        foreach ( $schoolDetails[$id] as $class => $users ) {
            echo "School: " . $school['name'] . "<br />";    
            echo "Grade: " . $class . "<br />";
            echo "<div class='students'>";
            foreach ( $users as $user ) {
                echo $user . "<br />";
            }
            echo "</div><br />";
        }
        echo "<div class='page-break'></div>";
    }
}

echo "<h2>Totals</h2>";
echo "Total for Pickup: " . $totals['pickup'] . "<br />";
echo "Total for Delivery: " . $totals['deliver'] . "<br />";
echo "Grand Total: " . $grandTotal;
?>
</body>
</html>

<?php
ini_set('display_errors', 1);
$admin_auth = array('school','user'); 
require('header.php');

$posters = array(
    2	=> 2,   3	=> 1,   4	=> 2,   5	=> 2,   7	=> 2,   9	=> 4,   11	=> 1,
    19	=> 2,   21	=> 1,   30	=> 0,   33	=> 1,   37	=> 1,   39	=> 1,   40	=> 1,
    42	=> 2,   45	=> 2,   48	=> 1,   49	=> 2,   50	=> 1,   54	=> 5,   55	=> 0,
    58	=> 2,   60	=> 1,   61	=> 0,   63	=> 2,   66	=> 0,   80	=> 0,   81	=> 2,
    84	=> 1,   86  => 0,   87	=> 1,   89	=> 1,   105	=> 1,   106	=> 2,   110	=> 0,   
    112	=> 0,   162	=> 2,   176	=> 2,   185	=> 2,   192	=> 2,   194	=> 1,   255	=> 2,  
    263	=> 1,   264	=> 1,   265	=> 1,   269	=> 0,   427	=> 1,   470	=> 1,   471	=> 2,   
    472 => 0,   474 => 0,   475 => 0,   480 => 0,   517 => 1,   542 => 1,   554 => 1,   
    560 => 0
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
    line-height: 1.3;
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
    <?php include('admin_header.php');?>
    <h1 class='hide'>Hachayol Report</h1>

<?php
    require_once($_SERVER['DOCUMENT_ROOT'].'/class.hachayol.php');
    require_once($_SERVER['DOCUMENT_ROOT'].'/reports/shipping/functions/get_hachayols.php'); // load the new hachayol shipping functions....
    $h = new Hachayol;
    
    //find out if admin is super
    if ( $admin->auth == 'super' ) {
        $h->setSchools();    
    } else {
        $h->setSchools( $admin->school_id );
    }

    $schools = $h->getSchools();
    $h->setChidonNumbers(); // find out the chidon eligible children 

    //variables for grand totals
    $grandTotal = 0;
    $totals['pickup'] = 0;
    $totals['deliver'] = 0;

    // hard coded totals
//    $exceptions = array(
        //54	=> 580, // update requested via E-mail on 1/3/2018
//        176	=> 84,
//        9   => 400,
//        265 => 70
//    );
    // order schools by total
    $orderedSchools = [];
    foreach ($schools as $type => $info) {
        foreach ($info as $id => $school) {
            if ($id != 162) $total = $school['total'] + $school['teachers'] + get_extra_hachayols($id, $school['total'] + $school['teachers']);
            else $total = $school['total'] + get_extra_hachayols($id, $school['total']);
            $orderedSchools[$total][$type][] = $school;
        }
    }
    // sort by total
    krsort($orderedSchools);
//    echo "<pre>"; print_r($orderedSchools); echo "</pre>";
?>
    <div align='center' class='hide'>
        <input type='button' value='Print' onclick='window.print();' />
    </div>
    <?php
    foreach ($orderedSchools as $total => $more) {
        $grandTotal += $total;
        foreach ($more as $type => $other) {
            $totals[$type] += $total;
            foreach ($other as $school) {
                $chidonNum = $h->getChidonNumber( $id );
                if ($type == 'pickup') echo "<h2>For Pickup</h2>";
                else if ($type == 'deliver') echo "<h2>For Delivery</h2>";
                echo "<div class='info'>";
                echo $school['shipping_name'] . "<br />";
                echo $school['name'] . "<br />";
                echo $school['address'] . "<br />";
                echo "Type of school: " . $school['type'] . "<br />";
                echo "Principal: " . $school['principal'] . "<br />";
                foreach( $school['admins'] as $admin ) {
                    $admin = trim($admin);
                    if (!empty($admin)) echo "Admin: " . $admin . "<br />";
                }
                echo "Total Teachers: " . $school['teachers'] . "<br />";
                echo "Total Registered children: " . $school['total'] . "<br />";
                if ($id == 162) $extra = get_extra_hachayols($id, $school['total']);
                else $extra = get_extra_hachayols($id, $school['teachers'] + $school['total']);
                if ($extra != 0) echo "Extra: " . $extra . "<br />";
                ?>
                <span style="font-size: 50px; font-weight: bold;">Total: <?=$total?></span><br />
                Already Registered for Chidon: <?=$school['chidonReg']?><br />
                Number of posters: <?=$posters[$id] ?? 0?><br />
                Number of boys posters: <?=$school['chidon_posters_boys'] ?? 0?><br />
                Number of girls posters: <?=$school['chidon_posters_girls'] ?? 0?><br />
                Possible Chidon Children: <?=$chidonNum?><br />
                Shipping Requests: <?=$school['shipping_requests']?><br /><br />
                </div>
                <div class='page-break'></div>
                <?php
            }
        }
    }
//    if ( isset( $schools['pickup'] ) ) {
//        foreach( $schools['pickup'] as $id => $school ) { ?>
<!--            --><?php //$chidonNum = $h->getChidonNumber( $id ); ?>
<!--            <h2>For Pickup</h2>-->
<!--            <div class='info'>-->
<!--                --><?//=$school['shipping_name']?><!--<br />-->
<!--                --><?//=$school['name']?><!--<br />-->
<!--                --><?//=$school['address']?><!--<br /><br />-->
<!--                Type of School: --><?//=$school['type']?><!--<br />-->
<!--                -->
<!--                Principal: --><?//=$school['principal']?><!--<br />-->
<!--                --><?php
//                foreach( $school['admins'] as $admin ) {
//                    $admin = trim($admin);
//                    if (!empty($admin)) echo "Admin: " . $admin . "<br />";
//                } ?>
<!--                Total Teachers: --><?//=$school['teachers']?><!--<br />-->
<!--                Total Registered children: --><?//=$school['total']?><!--<br />-->
<!--                -->
<!--                --><?php
//                if ($id === 162) { // Bais Chaya Mushka LA does not want chayoleis for teachers....
//                    $extra = get_extra_hachayols($id, $school['total']);
//                    $total = $school['total'] + $extra;
//                } else { // for all other schools add the extras to the total
//                    $extra = get_extra_hachayols($id, $school['teachers'] + $school['total']);
//                    $total = $school['teachers'] + $school['total'] + $extra;
//                    // find out if total should be overrided
////                    if ( isset( $exceptions[$id] ) ) $total = $exceptions[$id];
//                }
//
//                if ( $extra !== 0 ) { ?>
<!--                Extra: --><?//=$extra?><!--<br />-->
<!--                --><?php //} ?>
<!---->
<!--                <span style="font-size: larger; font-weight: bold;">Total: --><?//=$total?><!--</span><br />-->
<!--                Already Registered for Chidon: --><?//=$school['chidonReg']?><!--<br />-->
<!--                Number of posters: --><?//=$posters[$id] ?? 0?><!--<br />-->
<!--                Number of boys posters: --><?//=$school['chidon_posters_boys'] ?? 0?><!--<br />-->
<!--                Number of girls posters: --><?//=$school['chidon_posters_girls'] ?? 0?><!--<br />-->
<!--                Possible Chidon Children: --><?//=$chidonNum?><!--<br />-->
<!--                Shipping Requests: --><?//=$school['shipping_requests']?><!--<br /><br />-->
<!--                --><?php
//                $grandTotal += $total;
//                $totals['pickup'] += $total;
//                ?>
<!--            </div>-->
<!--            <div class='page-break'></div>-->
<!--        --><?php
//        }
//    }
//
//    if ( isset( $schools['deliver'] ) ) {
//        foreach ( $schools['deliver'] as $id => $school ) { ?>
<!--            --><?php //$chidonNum = $h->getChidonNumber( $id ); ?>
<!--            <h2>For Delivery</h2>-->
<!--            <div class='info'>-->
<!--                --><?//=$school['shipping_name']?><!--<br />-->
<!--                --><?//=$school['name'];?><!--<br />-->
<!--                --><?//=$school['address'];?><!--<br /><br />-->
<!--                Type of School: --><?//=$school['type']?><!--<br />-->
<!--                --><?php
//                foreach( $school['admins'] as $admin ) {
//                    $admin = trim($admin);
//                    if (!empty($admin)) echo "Admin: " . $admin . "<br />";
//                } ?>
<!--                Total Teachers: --><?//=$school['teachers']?><!--<br />-->
<!--                Total Registered children: --><?//=$school['total']?><!--<br />-->
<!--                --><?php
//                if ( in_array($id,  [54, 265] ) ) { // Beis Rivkah only wants the total specified in this file.
//                    $extra = $total = get_extra_hachayols($id);
//                } else if ($id != 162) { // for all other schools add the extras to the total
//                    $extra = get_extra_hachayols($id, $school['teachers'] + $school['total']);
//                    $total = $school['teachers'] + $school['total'] + $extra;
//                    // find out if total should be overrided
////                    if ( isset( $exceptions[$id] ) ) $total = $exceptions[$id];
//                } else {
//                    $extra = get_extra_hachayols($id, $school['total']);
//                    $total = $school['total'] + $extra;
//                }
//
//                if ( $extra !== 0 ) { ?>
<!--                    Extra: --><?//=$extra?><!--<br />-->
<!--                --><?php //} ?>
<!---->
<!--                <span style="font-size: larger; font-weight: bold;">Total: --><?//=$total?><!--</span><br />-->
<!--                Already Registered for Chidon: --><?//=$school['chidonReg']?><!--<br />-->
<!--                Number of posters: --><?//=$posters[$id] ?? 0?><!--<br />-->
<!--                Number of boys posters: --><?//=$school['chidon_posters_boys'] ?? 0?><!--<br />-->
<!--                Number of girls posters: --><?//=$school['chidon_posters_girls'] ?? 0?><!--<br />-->
<!--                Possible Chidon Children: --><?//=$chidonNum?><!--<br />-->
<!--                Shipping Requests: --><?//=$school['shipping_requests']?><!--<br /><br />-->
<!--                --><?php
//                $grandTotal += $total;
//                $totals['deliver'] += $total;
//                ?>
<!--            </div>-->
<!--            <div class='page-break'></div>-->
<!--        --><?php
//        }
//    } ?>
?>
    <h2>For Pickup</h2>
    <div class='info'>
        Warehouse - 300<br />
        Shterna Karp - 75<br />
        Shuls - 100
    </div>
    <div class='page-break'></div>
    
    <?php $totals['pickup'] += 475; ?>
    
    <h2>Totals</h2>
    Total for Pickup: <?=$totals['pickup']?><br />
    Total for Delivery: <?=$totals['deliver']?><br />
    Grand Total: <?=$grandTotal?>
</body>
</html>

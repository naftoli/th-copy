<?php
ini_set('display_errors', 1);
$admin_auth = array('school','user'); 
require('header.php');

$posters = array(
    2	=> 2,   3	=> 1,   4	=> 2,   5	=> 2,   7	=> 2,   9	=> 4,   11	=> 1,
    19	=> 2,   21	=> 1,   30	=> 2,   33	=> 1,   37	=> 1,   39	=> 1,   40	=> 1,
    42	=> 2,   45	=> 2,   48	=> 1,   49	=> 2,   50	=> 1,   54	=> 5,   55	=> 0,
    58	=> 2,   60	=> 1,   61	=> 0,   63	=> 2,   66	=> 0,   80	=> 0,   81	=> 2,
    84	=> 1,   86  => 0,   87	=> 1,   89	=> 1,   105	=> 1,   106	=> 2,   110	=> 0,   
    112	=> 0,   162	=> 2,   176	=> 2,   185	=> 2,   192	=> 2,   194	=> 1,   255	=> 2,  
    263	=> 1,   264	=> 1,   265	=> 1,   269	=> 0,   427	=> 1,   470	=> 1,   471	=> 2,
    472 => 0,   474 => 0,   475 => 0,   480 => 0
);

$types = array(
    2	=> 'girls', 3	=> 'boys',  4	=> 'boys',  5	=> 'boys',  7	=> 'girls', 9	=> 'boys',  11	=> 'boys',
    19	=> 'boys',  21	=> 'mixed', 30	=> 'girls', 33	=> 'boys',  37	=> 'girls', 39	=> 'mixed', 40	=> 'girls',
    42	=> 'girls', 45	=> 'girls', 48	=> 'boys',  49	=> 'boys',  50	=> 'girls', 54	=> 'girls', 55	=> 'mixed',
    58	=> 'boys',  60	=> 'boys',  61	=> 'mixed', 63	=> 'boys',  66	=> 'girls', 80	=> 'mixed', 81	=> 'mixed',
    84	=> 'mixed', 86	=> 'mixed', 87	=> 'mixed', 89	=> 'mixed', 106	=> 'mixed', 105	=> 'girls', 110	=> 'mixed', 
    112	=> 'boys',  162	=> 'girls', 176	=> 'girls', 185	=> 'mixed', 192	=> 'girls', 194	=> 'mixed', 255	=> 'boys',
	263	=> 'mixed', 264	=> 'boys',  265	=> 'girls', 269	=> 'mixed', 427	=> 'mixed', 470	=> 'mixed', 471	=> 'boys',
    472 => 'mixed', 474 => 'mixed', 475 => 'mixed', 480 => 'mixed'
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

?>
    <div align='center' class='hide'>
        <input type='button' value='Print' onclick='window.print();' />
    </div>
    <?php
    if ( isset( $schools['pickup'] ) ) {
        foreach( $schools['pickup'] as $id => $school ) { ?>
            <h2>For Pickup</h2>
            <div class='info'>
                <?=$school['name']?><br />
                <?=$school['address']?><br /><br />
                Type of School: <?=$types[$id]?><br />
                Principal: <?=$school['principal']?><br />
                <?php
                foreach( $school['admins'] as $admin ) {
                    $admin = trim($admin);
                    if (!empty($admin)) echo "Admin: " . $admin . "<br />";
                } ?>
                Total Teachers: <?=$school['teachers']?><br />
                Total Registered children: <?=$school['total']?><br />
                <?php
                if ($id === 162) { // Bais Chaya Mushka LA does not want chayoleis for teachers....
                    $total = $school['total'] + get_extra_hachayols($id, $school['total']);
                } else { // for all other schools add the extras to the total
                    $total = $school['teachers'] + $school['total'] + get_extra_hachayols($id, $school['teachers'] + $school['total']); 
                }
                ?>
                Total: <?=$total?><br />
                Already Registered for Chidon: <?=$school['chidonReg']?><br />
                Number of posters: <?=$posters[$id]?><br />
                Shipping Requests: <?=$school['shipping_requests']?><br /><br />
                <?php
                $grandTotal += $total;
                $totals['pickup'] += $total;
                ?>
            </div>
            <div class='page-break'></div>
        <?php
        }
    }
    
    if ( isset( $schools['deliver'] ) ) {
        foreach ( $schools['deliver'] as $id => $school ) { ?>
            <h2>For Delivery</h2>
            <div class='info'>
                <?=$school['name']?><br />
                <?=$school['address']?><br /><br />
                Type of School: <?=$types[$id]?><br />
                <?php
                foreach( $school['admins'] as $admin ) {
                    $admin = trim($admin);
                    if (!empty($admin)) echo "Admin: " . $admin . "<br />";
                } ?>
                Total Teachers: <?=$school['teachers']?><br />
                Total Registered children: <?=$school['total']?><br />
                <?php
                if ( $id === 54 ) $total = get_extra_hachayols($id); // Beis Rivkah only wants the total specified in this file.
                else if ($id != 162) $total = $school['teachers'] + $school['total'] + get_extra_hachayols($id); // for all other schools add the extras to the total
                else $total = $school['total'] + get_extra_hachayols($id);
                ?>
                Total: <?=$total?><br />
                Already Registered for Chidon: <?=$school['chidonReg']?><br />
                Number of posters: <?=$posters[$id]?><br />
                Shipping Requests: <?=$school['shipping_requests']?><br /><br />
                <?php
                $grandTotal += $total;
                $totals['pickup'] += $total;
                ?>
            </div>
            <div class='page-break'></div>
        <?php
        }
    } ?>

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

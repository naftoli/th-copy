<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>5774 Orders Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>5774 Orders Report</h1>
        <? 
        require_once 'class.adminSchools.php';       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        
        $orders = array();
        $sql = "select * from 5774_orders 
                join schools using (school_id) 
                join admins using (admin_id)";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $orders[$row['school_id']][] = $row;
        }
        
        $grandTotal['posters'] = 0;
        $grandTotal['brochures'] = 0;
        foreach ($schools as $id => $school) {
            if (isset($orders[$id])) {
                $totals[$id]['posters'] = 0;
                $totals[$id]['brochures'] = 0;
                ?>
                <h2><?=$school?></h2>
                <table>
                <tr>
                    <th>Date Ordered</th>
                    <th>Ordered By</th>
                    <th>Posters Ordered</th>
                    <th>Brochures Ordered</th>
                    <th>Shipping Address</th>
                    <th>Shipping Method</th>
                </tr>
                <? 
                $school = $orders[$id]; 
                foreach ($school as $row) {
                    $totals[$id]['posters'] += $row['posters'];
                    $totals[$id]['brochures'] += $row['brochures'];
                    ?>
                    <tr>
                        <td valign="top"><?=$row['order_date']?></td>
                        <td valign="top"><?=$row['first'] . ' ' . $row['last']?></td>
                        <td valign="top"><?=$row['posters']?></td>
                        <td valign="top"><?=$row['brochures']?></td>
                        <td valign="top">
                            <?
                            echo $row['shipping_address1'] . "<br />";
                            echo (empty($row['shipping_address2']) ? '' : $row['shipping_address2'] . "<br />");
                            echo $row['shipping_city'] . ', ';
                            echo $row['shipping_state'] . ' ';
                            echo $row['shipping_postal'] . "<br />";
                            echo (empty($row['shipping_country']) ? '' : $row['shipping_country']);
                            ?>
                        </td>
                        <td valign="top"><?=$row['shipping_method']?></td>
                    </tr>
                    <?
                }
                echo "<tr><th>Totals:<th><th>" . $totals[$id]['posters'] . "</th><th>" . $totals[$id]['brochures'] . "</th><th></th><th></th></tr></table>";
                $grandTotal['posters'] += $totals[$id]['posters'];
                $grandTotal['brochures'] += $totals[$id]['brochures'];
            }
        }
        if (!empty($totals)) {
            echo "<h2>Grand Totals</h2>";
            echo "<table>";
            echo "<tr><th>School</th><th>Posters</th><th>Brochures</th></tr>";
            foreach ($totals as $school => $info) {
                echo "<tr><td>" . $schools[$school] . "</td><td>" . $info['posters'] . "</td><td>" . $info['brochures'] . "</td></tr>";
            }
            echo "<tr><th>Grand Total:</th><th>" . $grandTotal['posters'] . "</th><th>" . $grandTotal['brochures'] . "</th></tr></table>";
        }
    ?>
    </body>
</html>
<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Haggadas Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Haggadas Report</h1>
        <? 
        require_once 'class.adminSchools.php';
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $totals['students'] = 0;
        $totals['teachers'] = 0;
        $totals['commanders'] = 0;
        $totals['extra'] = 0;
        $totals['total'] = 0;
        
        ?>
        <table>
            <tr>
                <th>School</th>
                <th>Chayolim</th>
                <th>Teachers</th>
                <th>Base Commander</th>
                <th>Extra purchased</th>
                <th>Total</th>
            </tr>
        <?
        $toSkip = array( 82, 79, 198, 199, 193 ); //schools to skip from report
        $price = 14.99;
        foreach ( $schools as $school_id => $school ) {
            if ( in_array( $school_id, $toSkip ) ) 
                continue; 
            include 'haggada.inc.php';
            //find out if school has already purchased any extra haggadas
            $sql = "select sum(paid) as total
                    from haggada_purchases 
                    where school_id = " . $school_id;
            $result = mysql_query( $sql );
            $row = mysql_fetch_assoc( $result );
            $qty = $row['total'] / $price;
            $total = $registered + $teachers + 1 + $qty;
            $totals['students'] += $registered;
            $totals['teachers'] += $teachers;
            $totals['commanders']++;
            $totals['extra'] += $qty;
            $totals['total'] += $total;
            echo "<tr><td>$school</td><td>$registered</td><td>$teachers</td><td>1</td><td>$qty</td>
                <td>$total</td></tr>";
        }       
        ?>
        <tr>
            <th align="right">Grand Total:</th>
            <th><?=number_format( $totals['students'] )?></th>
            <th><?=number_format( $totals['teachers'] )?></th>
            <th><?=number_format( $totals['commanders'] )?></th>
            <th><?=number_format( $totals['extra'] )?></th>
            <th><?=number_format( $totals['total'] )?></th>
        </tr>
        </table>
    </body>
</html>
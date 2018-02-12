<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tanya / Mishna Lines Report</title>
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
        <h1>Tanya / Mishna Lines Report</h1>
        <? 
        require_once 'class.adminSchools.php';
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        
        $totals['tanya'] = 0;
        $totals['mishna'] = 0;
        echo "<table>";
        echo "<tr><th>School</th><th>Total Tanya</th><th>Total Mishna</th></tr>";
        foreach ( $schools as $id => $school ) {
            $sql = "select total_tanya, total_mishna from tanya_totals where school_id = $id";
            $result = mysql_query( $sql );
            if ( mysql_num_rows($result) > 0 ) {
                while ( $row = mysql_fetch_assoc( $result ) ) {
                    $totals['tanya'] += $row['total_tanya'];
                    $totals['mishna'] += $row['total_mishna']; 
                    echo "<tr><td>" . $school . "</td><td>" . 
                        ($row['total_tanya'] ? $row['total_tanya'] : 0) . "</td><td>" . 
                        ($row['total_mishna'] ? $row['total_mishna'] : 0) . "</td></tr>";
                }
            } else {
                echo "<tr><td>" . $school . "</td><td>0</td><td>0</td></tr>";
            }
        }
        echo "<tr><th align='right'>Grand Total</th><th>" . $totals['tanya'] . "</th><th>" . $totals['mishna'] . "</th></tr>";
        echo "</table>";
        ?>
    </body>
</html>
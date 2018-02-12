<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>ID Cards Report</title>
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
        <h1>ID Cards Report</h1>
        
        <?
        require_once 'class.adminSchools.php';
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $cards = array();
        
        foreach ( $schools as $id => $name ) {
            $sql = "select i.card_id, i.user_id, i.printed, i.type, u.first, u.last, c.class_grade, c.class_sub  
                    from ID_cards i  
                    join users u using (user_id) 
                    join schools s using (school_id) 
                    join classes c on (c.class_id = u.class_id)
                    where s.school_id = " . $id . "
                    and s.inst_id = 4 
                    order by s.school_id, c.class_grade, c.class_sub, u.last, u.first";
            //echo $sql;
            $result = mysql_query( $sql );
            while ( $row = mysql_fetch_assoc( $result ) ) {
                $grade = $row['class_grade'] . ($row['class_sub'] == '' ? '' : '-' . $row['class_sub']);
                $user = $row['first'] . ' ' . $row['last']; 
                $cards[$name][$grade][$user][$row['type']][] = $row['printed'];
            }
        }
        
        foreach ( $cards as $school => $info ) {
            echo "<h2>" . $school . "</h2>";
            echo "<table>";
            echo "<tr><th>Grade</th><th>Student</th><th>Card Type</th><th>Printed</th></tr>";
            foreach ( $info as $grade => $users ) {
                echo "<tr><td colspan=4></td></tr>";
                foreach ( $users as $user => $card ) {
                    foreach ( $card as $type => $printedCards ) {
                        foreach ( $printedCards as $printed ) { 
                            echo "<tr><td>" . $grade . "</td>";
                            echo "<td>" . $user . "</td>";
                            echo "<td>" . $type . "</td>"; 
                            echo "<td>" . $printed . "</td></tr>";
                        }
                    }
                }
            }
            echo "</table>";
        }
        ?>
        
    </body>
</html>
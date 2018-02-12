<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>
    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    
    <body>
    <?
    $users = array();
    $barcodes = array();
    $sql = "select u.user_code, u.first, u.last, s.school_name, c.class_grade, c.class_sub 
            from users u 
            join schools s using (school_id) 
            join classes c on (c.class_id = u.class_id) 
            where u.user_registered > 0 
            #and u.user_id = 6713   
            order by s.school_name, c.class_grade, c.class_sub, u.last, u.first";
    $result = mysql_query( $sql );
    while ( $row = mysql_fetch_assoc( $result ) ) {
        $grade = $row['class_grade'] . ( empty( $row['class_sub'] ) ? '' : '-' . $row['class_sub'] );
        $name = $row['first'] . ' ' . $row['last'];
        $barcodes[] = '3' . $row['user_code'];
        $users[$row['school_name']][$grade][$name] = '3' . $row['user_code'];
    }
    
    $bcodes = header_v2_missions( array( 'arrUserCodes' => $barcodes ) );   
    foreach ( $users as $school => $info ) {
        foreach ( $info as $grade => $students ) {
            foreach ( $students as $name => $barcode ) {
                $users[$school][$grade][$name] = array_key_exists($barcode, $bcodes) ? $bcodes[$barcode] : 0;
            }
        }
    }
    
    echo "<pre>";
    //print_r( $users );
    print_r( $bcodes );
    echo "</pre>";
    ?>
    </body>
</html>
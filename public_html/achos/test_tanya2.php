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
    $noBcode = 0;
    
    //get all users including barcodes
    $users = array();
    $barcodes = array();
    $sql = "select user_id, user_code from users where user_registered > 0";
    $result = mysql_query( $sql );
    while ( $row = mysql_fetch_assoc( $result ) ) {
        $users[$row['user_id']] = '3' . $row['user_code'];
        $barcodes[] = '3' . $row['user_code'];
    }
    
    //get tanya missions done
    $total = header_v2_missions( array( 'arrUserCodes' => $barcodes ) );
    //print_r( $total );
    
    //get tanya medal info
    $medals = array(); 
    $needed = 0;   
    $sql = "select medal_name, missions_required from medals_subjects 
            join medals using (medal_ord)    
            where subject_id = 27 
            order by medal_ord";    
    $result = mysql_query( $sql );
    while ( $row = mysql_fetch_assoc( $result ) ) {
        $needed += (int)$row['missions_required'];
        $medals[$row['medal_name']] = $needed;
    }
    
    //loop through users updating medals/ranks
    require_once 'class.newSubjectsUpdater.php';
    $n = new NewSubjectsUpdater( 27 );
    foreach ( $users as $id => $bcode ) {
        if ( isset( $total[$bcode] ) ) {
            $n->updateMedals( $id, $total[$bcode] );
            $n->updateRanks( $id );
        } else {
            $noBcode++;
        }
    }
    
    echo "Total No Barcodes: " . $noBcode;
    ?>
    </body>
</html>
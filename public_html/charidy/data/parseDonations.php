<?php
ini_set('display_errors',1);
require '../../db.php';
require 'classes/parseDonation.php';

$info = array();
$sql = "select * from charidy_temp_donations";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $info[] = $row;
}

function parse( $data, $info ) {
    $donation = json_decode( $data );
    $p = new ParseDonation( $donation, $info );
    $p->createDonation();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
    </head>
    <body>
        <pre>
        <?php
        $totalNum = count( $info );
        for ($i = 0; $i < $totalNum; $i++) {
            // get json object from charidy_temp_data 
            $sql = "select * from charidy_temp_data where id = " . $info[$i]['relation_id'];
            $result = mysql_query( $sql );
            $row = mysql_fetch_assoc( $result );
            parse( $row['data'], (object)$info[$i] );
            echo "<hr />";
        }
        ?>
        </pre>
    </body>
</html>
<?php
require '../../db.php';
require 'classes/parseDonation.php';

$info = array();
$sql = "select * from charidy_temp_data";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $info[] = $row;
}

function parse( $data ) {
    $donation = json_decode( $data );
    $p = new ParseDonation( $donation );
    $p->parse();
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
        for ($i = 0; $i < 10; $i++) {
            parse( $info[$i]['data'] );
            echo "<hr />";
        }
        ?>
        </pre>
    </body>
</html>
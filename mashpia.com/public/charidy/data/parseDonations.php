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

function parse( $info, $data = array() ) {
    if ( !empty( $data ) ) {
        $donation = json_decode( $data );
        $p = new ParseDonation( $info, $donation );
    } else {
        $p = new ParseDonation( $info );
    }
    $p->createDonation();
    return $p->getErrors();
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
        mysql_query('set autocommit = 0');
        mysql_query('begin');

        $errors = array();
        $totalNum = count( $info );
        for ($i = 0; $i < $totalNum; $i++) {
            // get json object from charidy_temp_data 
            if ($info[$i]['relation_id'] > 0) {
                $sql = "select * from charidy_temp_data where id = " . $info[$i]['relation_id'];
                $result = mysql_query( $sql );
                $row = mysql_fetch_assoc( $result );
                $error = parse( (object)$info[$i], $row['data'] );
                if ($error) $errors[] = $error;
            } else {
                $error = parse( (object)$info[$i] );
                if ($error) $errors[] = $error;
            }
        }

        if (empty( $errors )) {
            mysql_query('commit');
        } else {
            mysql_query('rollback');
            echo "<pre>";
            print_r( $errors );
            echo "</pre>";
            // foreach ($errors as $error) {
            //     echo $error . "<br />";
            // }
        }
        mysql_query('set autocommit=1');
        echo "done.";
        ?>
        </pre>
    </body>
</html>
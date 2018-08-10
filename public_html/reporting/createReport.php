<?php
echo "<pre>"; print_r( $_POST ); echo "</pre>";
require '../db.php';
require 'classes/class.reportingEngine.php';

// build array to pass into engine
$info = array();
foreach ( $_POST as $k => $v ) {
    if ( $k != 'submit' ) {
        // find table and column info
        $pos = strpos($k, '|');
        if ( $pos !== false ) {
            $table = substr($k, 0, $pos++);
            $field = substr($k, $pos);
            if ( $table != 'calc' ) {
                $info[$table][] = $field;
            } else {
                switch ($v) {
                    case 'store_points':
                    case 'auction_points':
                        $info['miles'][] = $v;
                        break;
                }
            }
        }
    }
}

$engine = new ReportingEngine( $info );
$engine->createQry();
if ( $engine->runQry() ) {
    $result = $engine->getResult();
    echo "<pre>"; print_r( $result ); echo "</pre>";
} else {
    echo $engine->getError();
}
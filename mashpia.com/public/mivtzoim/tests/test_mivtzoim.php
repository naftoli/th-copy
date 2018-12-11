<?php
ini_set('display_errors',1);
$admin_auth = ['school'];
require __DIR__ . '/../classes/mivtzoim.php';

echo "<pre>"; 
try {
    $m = new Mivtzoim( 1 );

    $name = $m->getName();
    $dates = $m->getDates();
    $start = $dates['start'];
    $end = $dates['end'];

    $ms = new MivtzoimSetup( $name, $start, $end );
    $namesAvailable = $ms->availableShortNames();
    print_r( $namesAvailable );
    $m->saveShortNames( $namesAvailable );

    $names = $m->getShortNames();
    print_r( $names ); 

    $grids = $m->getTasks();
    print_r( $grids );
} catch ( \Exception $e ) {
    echo $e->getMessage();
}
echo "</pre>";
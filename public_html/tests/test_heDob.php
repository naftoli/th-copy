<?php
ini_set('display_errors', 1);
require '../db.php';
require '../class.heDob.php';

$h = new HeDob( 8273 );
$h->setHeDob();
//$h->syncToWp();
echo "done";
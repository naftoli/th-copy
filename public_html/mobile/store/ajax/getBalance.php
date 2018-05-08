<?php
require_once( dirname(__FILE__) . '/../../../db.php' );
require_once( dirname(__FILE__) . '/../../../class.points.php' );

$user = mysql_real_escape_string($_POST['user']);

$p = new Points( $user );
$balance['tpoints'] = $p->getTotalPoints();
$balance['earned'] = $p->getTotalThisYear();
$balance['available'] = $p->getStorePoints();

echo json_encode($balance);
?>
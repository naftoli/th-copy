<?
require_once 'db.php';
require_once 'class.achosPoints.php';

$ap = new AchosPoints( 1 );
$ap->setDebug( true );
$p = $ap->calcPoints( 2456970, 2456977 );
echo $p;
?>
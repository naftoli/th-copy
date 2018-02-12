<?
require_once 'db.php';
require_once 'class.bpSummary.php';

$b = new BpSummary(5, 'school');
$b->updateSummary( 82 );
echo $b->getSummary( 82 );
?>
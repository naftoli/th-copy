<?
require 'db.php';
require 'class.bpSummary.php';
require 'class.balPehCampaign.php';

$b = BalPehCampaign::getInstance( 5 );
$bp = new BpSummary(5, 'school');
$pledged = $b->getTotalPledged( 'school', 61 );
$learned = $bp->getSummary( 61 );

echo "Pledged: " . $pledged . "<br />";
echo "Learned: " . $learned;
?>
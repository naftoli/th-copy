<?
require_once 'db.php';
require_once 'class.balPehCampaign.php';

$bp = BalPehCampaign::getInstance(5);
$bp->setStartDate( 2457113 );

echo time() . "<br />";
$pledged = $bp->getTotalPledged( 'school', 82 );
echo "Pledged: " . $pledged . "<br />" . time() . "<br /><br />";

echo time() . "<br />";
$learned = $bp->getTotalLearned( 'school', 82 );
echo "Learned: " . $learned . "<br />" . time() . "<br /><br />";
?>
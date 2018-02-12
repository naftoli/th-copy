<?
require_once 'db.php';
require_once 'class.balPehCampaign.php';
$bp = BalPehCampaign::getInstance( 5 );
$bp->setStartDate( 2457113 );
echo $bp->getTotalPledged( 'school', 58 );
?>
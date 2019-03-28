<?
require_once 'db.php';
//$_POST['user_id'] = 8273;
//$_POST['medal_name'] = 'Red';
$user_id = $_POST['user_id'];
$medal = $_POST['medal_name'];
require_once 'class.newSubjectsUpdater.php';
$n = new NewSubjectsUpdater( 27 );
$n->updateTanyaMedals( $user_id, $medal );
$n->updateRanks( $user_id );
?>
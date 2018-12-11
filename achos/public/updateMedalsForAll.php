<?
require_once 'db.php';

require_once 'class.parshos.php';
$p = new Parshos;
$parshos = $p->getParshos();
	
require_once("classes/medal_updater.php");
$medal_updater = new medal_updater();

$users = array();
$sql = "select user_id from users";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row['user_id'];
}

foreach ($parshos as $parsha) {
	if ($parsha['start'] > 2456634) break;
	foreach ($users as $user_id) {	    	 
		$medal_updater->update_medal_two($user_id);
	}
}
?>
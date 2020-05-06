<?php
// ini_set('display_errors', 1);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<?php
require_once('db.php');
$ids = [];
$sql = "select user_id from users were user_registered > 0";
$result = mysql_query( $sql ); 
while ( $row = mysql_fetch_assoc( $result ) ) {
    $ids[] = $row['user_id'];
}

require_once('classes/medal_updater.php');
require_once('classes/rank_updater.php');

$mupdater = new medal_updater();
$rupdater = new rank_updater();

echo "Started: " . time() . "<br />";
foreach ( $ids as $user ) {
    $mupdater->update_medal_two($user);
    $rupdater->update_rank_two($user);
}
echo "Ended: " . time();
?>
</html>
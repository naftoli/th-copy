<?php
ini_set('display_errors', 1);
require_once(__DIR__ . '/db.php');

$ids = [];
$sql = "select user_id from users where user_registered > 0";
$result = mysql_query( $sql ); 
while ( $row = mysql_fetch_assoc( $result ) ) {
    $ids[] = $row['user_id'];
}

require_once(__DIR__ . '/classes/medal_updater.php');
require_once(__DIR__ . '/classes/rank_updater.php');

$mupdater = new medal_updater();
$rupdater = new rank_updater();

echo "Running Medals / Ranks Updater....\n";
echo "Started: " . time() . "\n";
foreach ( $ids as $user ) {
    echo "Updating medals...\n";
    $mupdater->update_medal_two($user);
    echo "Updating ranks...\n";
    $rupdater->update_rank_two($user);
}
echo "Ended: " . time();
?>
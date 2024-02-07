<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$ids = [];
$sql = "SELECT 
            achievement_card_id, COUNT(*) AS num
        FROM
            pointsDB.achievement_cards_scanned
        GROUP BY achievement_card_id
        HAVING num > 1";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $ids[] = $row['achievement_card_id'];
}

foreach ($ids as $id) {
    $sql = "delete from pointsDB.achievement_cards_scanned where achievement_card_id = $id";
    mysql_query($sql) or die(mysql_error());
}
echo "done";
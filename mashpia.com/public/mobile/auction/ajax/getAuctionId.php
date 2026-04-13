<?php
require_once __DIR__ . '/../../../db.php';

$sql = "SELECT auction_id FROM auctions WHERE auction_ran = 0 ORDER BY auction_id DESC LIMIT 1";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$auction_id = $row['auction_id'];

echo json_encode(['auction_id' => $auction_id]);
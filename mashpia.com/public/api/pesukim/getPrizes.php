<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$stmt = $MASHPIA_DB->query("SELECT * FROM auctions ORDER BY auction_id DESC LIMIT 1");
$auction = $stmt->fetch();
$auction_id = $auction['auction_id'];
$auction_date = jdtogregorian($auction['auction_run_date']);

$info = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT * FROM auction_prizes ap 
    JOIN prizes_auction pa USING (prize_id) 
    WHERE ap.auction_id = :auction_id 
    ORDER BY pa.category, pa.prize_name
");
$stmt->execute([':auction_id' => $auction_id]);
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    $info[$row['category']][] = $row;
}

$i = 0;
$prizes = [];
foreach ($info as $category => $prizesArr) {
    $prizes['categories'][$i] = [
        'name' => strtoupper($category),
        'gridClass' => 'col-' . count($prizesArr)
    ];
    $catPrizes = [];
    foreach ($prizesArr as $prize) {
        $catPrizes[] = [
            'title' => $prize['prize_name'],
            'img' => $prize['prize_image_id'],
            'alt' => $prize['prize_name'],
            'description' => $prize['prize_description'],
            'width' => '100px', 
            'miles' => $prize['prize_points'], 
            'auction_date' => $auction_date
        ];
    }
    $prizes['categories'][$i++]['prizes'] = $catPrizes;
}
ksort($prizes['categories']);
echo json_encode($prizes);
?>
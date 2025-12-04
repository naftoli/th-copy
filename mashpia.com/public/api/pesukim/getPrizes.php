<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';

$sql = "SELECT * FROM auction_prizes ap 
        JOIN prizes_auction pa USING (prize_id) 
        WHERE ap.auction_id = (select auction_id from auctions order by auction_id desc limit 1)
        ORDER BY pa.category, pa.prize_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[$row['ap_category']][] = $row;
}

$i = 0;
$prizes = [];
foreach ($info as $category => $prizes) {
    $prizes['categories'][$i] = [
        'name' => strtoupper($category),
        'gridClass' => 'col-' . count($prizes)
    ];
    $catPrizes = [];
    foreach ($prizes as $prize) {
        $catPrizes[] = [
            'title' => $prize['prize_name'],
            'img' => $prize['prize_image_id'],
            'alt' => $prize['prize_name'],
            'description' => $prize['prize_description'],
            'width' => '100px'
        ];
    }
    $prizes['categories'][$i++]['prizes'] = $catPrizes;
}
echo json_encode($prizes);
?>
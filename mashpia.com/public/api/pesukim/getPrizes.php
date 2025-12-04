<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';

$sql = "SELECT * FROM prizes_auction ORDER BY category, prize_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[$row['category']][] = $row;
}

$i = 0;
$prizes = [];
foreach ($info as $category => $prizes) {
    $prizes['categories'][$i] = [
        'name' => $category,
        'gridClass' => 'col-' . count($prizes)
    ];
    foreach ($prizes as $prize) {
        $prizes['categories'][$i]['prizes'][] = [
            'title' => $prize['prize_name'],
            'img' => $prize['prize_image_id'],
            'alt' => $prize['prize_name'],
            'description' => $prize['prize_description'],
            'width' => '100px',
        ];
    }
    $i++;
}
echo json_encode($prizes);
?>
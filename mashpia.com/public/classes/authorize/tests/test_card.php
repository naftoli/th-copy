<?php
require_once '../Card.php';
use classes\authorize\Card as Card;

$cc_info = [
    'num'   => '1234343323255466',
    'exp'   => '0327',
    'cvv'   => '034'
];
$card = new Card();
$response = $card->charge($cc_info, 50, 'test', 'auth');
echo "<pre>";
print_r($response);
echo "</pre>";
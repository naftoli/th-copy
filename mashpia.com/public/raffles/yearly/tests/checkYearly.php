<?php
ini_set('display_errors', 1);
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
$user_id = 51313;
require "../classes/YearlyRaffle.php";
$r = new \raffles\yearly\YearlyRaffle();
$info = $r->set_user_eligibility($user_id);
echo "<pre>"; print_r($info); echo "</pre>";
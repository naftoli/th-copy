<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/rank_updater.php';
require_once $_SERVER['DOCUMENT_ROOT']."/blog/wp-load.php";

$rank = 7;
$user = 8273;

$r = new rank_updater();
$post_id = $r->updateWP($rank, $user);
echo "Post ID: " . $post_id;
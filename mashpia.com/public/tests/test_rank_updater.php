<?php
ini_set('display_errors',1);
require '../db.php';
require '../classes/rank_updater.php';

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

$r = new rank_updater();
$r->update_rank_two( 8273 );
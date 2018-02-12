<?php
ini_set('display_errors',1);
require '../db.php';
require '../classes/rank_updater.php';

$r = new rank_updater();
$r->update_rank_two( 8273 );
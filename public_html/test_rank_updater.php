<?php
require 'db.php';
require 'classes/rank_updater.php';
$user_id = 10641;
$rank_updater = new rank_updater();
$rank_updater->update_rank_two($user_id, true);

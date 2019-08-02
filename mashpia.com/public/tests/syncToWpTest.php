<?php
ini_set('display_errors',1);
chdir('../');
require_once 'db.php';
require_once 'class.wpBirthday.php';
$wpb = new WpBirthday( 8273 );
$wpb->syncToWp();
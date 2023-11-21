<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "<h1>Access Denied</h1>";
    exit;
}

require '../class.chidonTests.php';
$ct = new ChidonTests();
$types = $ct->getTypes();
$ct->setStudents(9, 5583, 60704);
$ct->setScores();
$ct->calculateMarks();
$marks = $ct->getMarks();

// get highest passing avg
$highest = $ct->getHighestTrackUsingMarks($marks, 60704);
echo $highest;
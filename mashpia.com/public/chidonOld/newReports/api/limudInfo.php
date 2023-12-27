<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

$input = json_decode(file_get_contents('php://input'), true);
$year = $input['year'];
$school_id = $input['school'];
$class_id = $input['grade'];

require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests($year);
$ct->setStudents($school_id, $class_id);
$ct->setScores();
$ct->calculateMarks();

$students = $ct->getStudents();
$marks = $ct->getMarks();

echo json_encode([
    'success'   => true,
    'info'      => $students,
    'marks'     => $marks,
]);
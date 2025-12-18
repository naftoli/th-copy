<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pesukim/class.pesukim.php';

$pesukim = new Pesukim(10000);
$recruiter = $pesukim->getRecruiter();
echo 'recruiter: ' . $recruiter . "\n";
if ($recruiter) {
    $pesukim->deletePoints(intval($points) * 5, $recruiter);
    echo 'deleted points from recruiter: ' . $recruiter . "\n";
    while ($recruiter) {
        $pesukim = new Pesukim($recruiter);
        $nextRecruiter = $pesukim->getRecruiter();
        echo 'next recruiter: ' . $nextRecruiter . "\n";
        if (!$nextRecruiter) break;
        $pesukim->deletePoints(intval($points) * 2.5, $nextRecruiter);
        echo 'deleted points from next recruiter: ' . $nextRecruiter . "\n";
        $recruiter = $nextRecruiter;
    }
}
echo 'here';
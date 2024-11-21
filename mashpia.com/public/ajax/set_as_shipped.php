<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

// make sure it's hq
if ($admin_user['auth'] != 'super') {
    echo 'You are not authorized to view this page.';
    exit;
}

$stmtMedals = $MASHPIA_DB->prepare("
    UPDATE medal_marks 
    SET date_shipped = NOW(), 
        date_received = NOW() 
    WHERE user_id = ? 
      AND subject_id = ? 
      AND medal_ord = ?");
$stmtBook = $MASHPIA_DB->prepare("INSERT IGNORE INTO rank_books_shipped (user_id, book) VALUES (?, ?)");
$stmtRankMedals = $MASHPIA_DB->prepare("INSERT IGNORE INTO rank_medals_shipped (user_id, rank_ord) VALUES (?, ?)");

$data = json_decode(file_get_contents('php://input'), true);
$info = $data['info'];

$MASHPIA_DB->beginTransaction();
$success = true;

$error = '';
foreach ($info as $user_id => $other) {
    $book = $other['book'];
    $medals = $other['medals'];
    $rank_medals = $other['rank_medals'];
    $res = $stmtBook->execute([$user_id, $book]);
    if (!$res) {
        $success = false;
        $error = 'Failed to update book for user ' . $user_id . ' and book ' . $book. '\nNothing was set as shipped.';
        break;
    }
    foreach ($rank_medals as $rank_ord) {
        $res = $stmtRankMedals->execute([$user_id, $rank_ord]);
        if (!$res) {
            $success = false;
            $error = 'Failed to update rank medal for user ' . $user_id . ' and rank ' . $rank_ord. '\nNothing was set as shipped.';
            break;
        }
    }
    foreach ($medals as $subject_id => $more) {
        foreach ($more as $medal_ord) {
            $res = $stmtMedals->execute([$user_id, $subject_id, $medal_ord]);
            if (!$res) {
                $success = false;
                $error = 'Failed to update medal for user ' . $user_id . ' and subject ' . $subject_id . ' and medal ' . $medal_ord . '\nNothing was set as shipped.';
                break;
            }
        }
    }
}

if ($success) {
    $MASHPIA_DB->commit();
    echo json_encode([
        'success'       => true
    ]);
} else {
    $MASHPIA_DB->rollBack();
    echo json_encode([
        'success'   => false,
        'error'     => $error
    ]);
}
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if (!$id) {
  echo json_encode(['success' => false, 'error' => 'Missing id']);
  exit;
}

global $MASHPIA_DB;
$stmt = $MASHPIA_DB->prepare("delete from th_chidon_attendance_times where att_time_id = :id");
if ( $stmt->execute([':id' => $id]) ) {
  echo json_encode([
    'success' =>  true
  ]);
} else {
  echo json_encode([
    'success' =>  false, 
    'error'   =>  'Delete failed'
  ]);
}
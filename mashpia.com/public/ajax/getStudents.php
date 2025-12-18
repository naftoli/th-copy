<?php
$admin_auth = ['school'];
require_once '../header.php';
require_once '../api/header/db.php';

$school_id = (int)$_POST['school'];
$class_id = isset($_POST['class']) ? (int)$_POST['class'] : 0;

$users = [];

if ($class_id > 0) {
	$sql = "
		SELECT user_id, first, last 
		FROM users 
		WHERE user_registered > 0 
		AND school_id = :school_id 
		AND class_id = :class_id 
		ORDER BY last, first";
	$stmt = $MASHPIA_DB->prepare($sql);
	$stmt->execute([
		':school_id' => $school_id,
		':class_id' => $class_id
	]);
} else {
	$sql = "
		SELECT user_id, first, last 
		FROM users 
		WHERE user_registered > 0 
		AND school_id = :school_id 
		ORDER BY class_grade, class_sub, last, first";
	$stmt = $MASHPIA_DB->prepare($sql);
	$stmt->execute([
		':school_id' => $school_id
	]);
}

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$users[' ' . $row['user_id']] = $row['first'] . ' ' . $row['last'];
}

echo json_encode($users);
?>
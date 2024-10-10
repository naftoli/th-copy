<?php
$admin_auth = ['user'];
require_once $_SERVER['DOCUMENT_ROOT'] . "/api/header/db.php";

if (! isset($_GET['user'])) {
    die('No user specified');
}

$user_id = $_GET['user'];
// find out user gender, grade and school_type_id
$stmt = $MASHPIA_DB->prepare("
    SELECT gender, school_type_id, class_grade 
    FROM users u 
    JOIN classes c ON u.class_id = c.class_id 
    WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $gender = $user['gender'];
    $grade = $user['class_grade'];
    $school_type_id = $user['school_type_id'];

    if (strtoupper($gender) == 'F') {
        $gender = 'G';
    } else {
        $gender = 'B';
    }

    if (in_array($school_type_id, [2, 3])) {
        $file = $gender . $grade . '.png';
    } else {
        $file = $gender . $grade . ' Non Chabad.png';
    }

    // check if file exists
    $file_path = "https://mashpia.com/certs/" . $file;
    header('Location: https://mashpia.com/certs/cert.html?url=' . urlencode($file));
    /*
    if (file_exists($file_path)) {
        header('Location: https://mashpia.com/certs/cert.html?url=' . urlencode($file));
    } else {
        die('File not found');
    }
    */
}
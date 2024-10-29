<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . "/api/header/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php";

if (! isset($_GET['user'])) {
    die('No user specified');
}

$user_id = $_GET['user'];
$year = GlobalSettings::getChidonRegYear();
// find out user gender, grade and school_type_id
$stmt = $MASHPIA_DB->prepare("
    SELECT first_he, last_he, gender, school_type_id, class_grade, book  
    FROM users u 
    JOIN classes c ON u.class_id = c.class_id 
    JOIN th_schools tc USING(user_id) 
    WHERE user_id = :user_id 
    AND year = :year");
$stmt->execute([
    'user_id' => $user_id,
    'year' => $year
]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($user);

if ($user) {
    $book = $user['book'];
    $gender = $user['gender'];
    $school_type_id = $user['school_type_id'];

    if (strtoupper($gender) == 'F') {
        $gender = 'G';
    } else {
        $gender = 'B';
    }

    if (in_array($school_type_id, [2, 3])) {
        $file = $gender . $book . '.png';
    } else {
        $file = $gender . $book . ' Non Chabad.png';
    }

    // check if file exists
    $file_path = "https://mashpia.com/certs/" . $file;
    $he_name = $user['first_he'] . ' ' . $user['last_he'];
    header('Location: https://mashpia.com/certs/cert.html?url=' . urlencode($file) . '&he_name=' . urlencode($he_name));
    /*
    if (file_exists($file_path)) {
        $he_name = $user['first_he'] . ' ' . $user['last_he'];
        header('Location: https://mashpia.com/certs/cert.html?url=' . urlencode($file) . '&he_name=' . urlencode($he_name));
    } else {
        die('File not found');
    }
    */
}
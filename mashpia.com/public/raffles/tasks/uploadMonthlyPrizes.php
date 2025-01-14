<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ( $admin_user['auth'] != 'super' ) {
    echo "No Permission.";
    exit;
}

if (isset($_GET['id'])) {
    $i = '_' . $_GET['id'];
    switch (intval($_GET['id'])) {
        case 2:
            $raffle_id = 445;
            break;
        case 3:
            $raffle_id = 446;
            break;
        case 4:
//            $raffle_id = 215;
            break;
    }
} else {
    // stop here
    echo "You need to provide a 60m number ID (?id=) as a GET parameter.";
    exit;
}

$stmt = $MASHPIA_DB->prepare("
    INSERT INTO raffles_monthly SET 
    raffle_id = :raffle, 
    prize_id = :prize, 
    school_id = :school
");

// load csv file 
$info = [];
if (isset($_POST['submit'])) {
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $handle = fopen($file['tmp_name'], 'r');
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $prize_id = $data[0];
                $school_id = $data[1];
                $info[$school_id][] = $prize_id;
            }
            fclose($handle);
        } else {
            echo "Error uploading file.";
            exit;
        }
    } else {
        echo "No file uploaded.";
        exit;
    }
}

echo "<pre>"; print_r($info); echo "</pre>"; exit;

$success = true;
$MASHPIA_DB->beginTransaction();
foreach ($info as $school_id => $prizes) {
    foreach ($prizes as $prize) {
        if ($prize > 0) {
            $res = $stmt->execute([
                ':raffle' => $raffle_id,
                ':prize'  => $prize,
                ':school' => $school_id
            ]);
            if (!$res) {
                $success = false;
                break 2;
            }
        }
    }
}
if ( $success ) {
    $MASHPIA_DB->commit();
    echo "done.";
} else {
    $MASHPIA_DB->rollBack();
    echo "errors.";
}
?>
<!DOCTYPE html>
<!-- create form for uploading csv file -->
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Upload Monthly Prizes</title>
</head>
<body>
    <form method="POST" enctype="multipart/form-data" action="">
        <input type="file" name="monthly_prizes" />
        <input type="submit" value="Submit" />
    </form>
</body>
</html>
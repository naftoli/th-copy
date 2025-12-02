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

// load csv file
if (isset($_POST['submit'])) {
    if (isset($_FILES['prizes'])) {
        $info = [];
        $raffle_id = 449;
        $file = $_FILES['prizes'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $handle = fopen($file['tmp_name'], 'r');
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $school_id = $data[0];
                $prize_id = $data[1];
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

    $stmt = $MASHPIA_DB->prepare("
        DELETE FROM raffles_monthly 
        WHERE raffle_id = :raffle
        AND prize_id = :prize
        AND school_id = :school
    ");

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
} else {
?>
<!DOCTYPE html>
<!-- create form for uploading csv file -->
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Remove Prizes</title>
</head>
<body>
    <form method="POST" enctype="multipart/form-data" action="">
        <input type="file" name="prizes" />
        <input type="submit" name="submit" value="Submit" />
    </form>
</body>
</html>
<?php } ?>
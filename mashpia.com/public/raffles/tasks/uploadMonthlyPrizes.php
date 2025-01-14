<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

if ( $admin_user['auth'] != 'super' ) {
    echo "No Permission.";
    exit;
}

// get 60m raffles
$raffles = [];
$stmtRaffles = $MASHPIA_DB->prepare("SELECT * FROM `raffles` WHERE `type` = 'monthly' and `year` = ? ORDER BY `raffle_id`");
$stmtRaffles->execute([$year]);
$rows = $stmtRaffles->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $raffle) {
    $raffles[$raffle['raffle_id']] = $raffle['name'];
}

// load csv file
if (isset($_POST['submit'])) {
    if (isset($_FILES['monthly_prizes'])) {
        $info = [];
        $file = $_FILES['monthly_prizes'];
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

    $stmt = $MASHPIA_DB->prepare("
        INSERT INTO raffles_monthly SET 
        raffle_id = :raffle, 
        prize_id = :prize, 
        school_id = :school
    ");

    $raffle_id = $_POST['raffle_id'];
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
    <title>Upload Monthly Prizes</title>
</head>
<body>
    <form method="POST" enctype="multipart/form-data" action="">
        <select name="raffle_id">
            <?php
            foreach ($raffles as $raffle_id => $raffle) {
                echo "<option value='{$raffle_id}'>{$raffle}</option>";
            }
            ?>
        </select><br /><br />
        <input type="file" name="monthly_prizes" />
        <input type="submit" name="submit" value="Submit" />
    </form>
</body>
</html>
<?php } ?>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';

if (isset($_FILES['file'])) {
    $stmt = $MASHPIA_DB->prepare("
        INSERT INTO school_auction_prizes 
        SET auction_id = :auction, 
            school_id = :school, 
            prize_id = :prize
    ");
    $file = $_FILES['file']['tmp_name'];
    if (($handle = fopen($file, "r")) !== FALSE) {
        $success = true;
        $MASHPIA_DB->beginTransaction();
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $auction_id = $data[0];
            $school_id = $data[1];
            $prize_id = $data[2];
            $res = $stmt->execute([
                'auction'  => $auction_id,
                'school'  => $school_id,
                'prize'   => $prize_id
            ]);
            if (!$res) {
                $success = false;
                break;
            }
        }
        fclose($handle);
        if ($success) {
            $MASHPIA_DB->commit();
            echo "done";
        } else {
            $MASHPIA_DB->rollBack();
            echo $stmt->errorInfo();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Add School Prizes</title>
    <style>
      body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 14px;
      }
    </style>
</head>
<body>
<form action="school_auction_prizes.php" method="post" enctype="multipart/form-data">
    <input type="file" name="file" /> <input type="submit" name="submit" value="upload" />
</form>
</body>
</html>
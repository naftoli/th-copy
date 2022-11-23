<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

if (isset($_FILES['file'])) {
    $qrys = [];
    $file = $_FILES['file']['tmp_name'];
    if (($handle = fopen($file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $school_id = $data[0];
            $prize_id = $data[1];
            $raffle_id = $data[2];
            $qry = "insert into raffles_monthly  
                    set raffle_id = $raffle_id, 
                    prize_id = $prize_id, 
                    school_id = $school_id";
            $qrys[] = $qry;
        }
        fclose($handle);
    }

    mysql_query('set autocommit=0');
    mysql_query('begin');
    $success = true;
    foreach ($qrys as $qry) {
        if (!mysql_query($qry)) {
            echo $qry . "<br />" . mysql_error();
            $success = false;
            break;
        }
    }
    if ($success) {
        mysql_query('commit');
        echo "done.";
    }
    else mysql_query('rollback');
    mysql_query('set autocommit=1');
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
        <form action="school_prizes.php" method="post" enctype="multipart/form-data">
            <input type="file" name="file" /> <input type="submit" name="submit" value="upload" />
        </form>
    </body>
</html>
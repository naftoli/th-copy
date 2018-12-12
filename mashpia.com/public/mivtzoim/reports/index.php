<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim/classes/mivtzoim.php';
$mivtzoim = Mivtzoim::getAll();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mivtzoim Report</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="../mivtzoim.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
        <h1>Mivtzoim Leaderboard</h1>

        <form action="report.php" method="post">
            <select name="mivtzoim_id">
                <option value='0'>Choose Mivtzoim for Report</option>
                <?php
                foreach ( $mivtzoim as $row ) {
                    echo "<option value='" . $row['mivtzoim_id'] . "'>" . $row['name'] . "</option>";
                }
                ?>
            <select>
            <br /><br />
            <input type="submit" name="submit" value="submit" />
        </form>
    </body>
</html>
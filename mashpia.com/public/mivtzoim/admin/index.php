<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ( isset( $_POST['submit'] ) ) {
    //echo "<pre>"; print_r( $_POST ); echo "</pre>";
    $name = $_POST['name'];
    $startArr = explode('-', $_POST['start']);
    $endArr = explode('-', $_POST['end']);
    $start = gregoriantojd( $startArr[1], $startArr[2], $startArr[0] );
    $end = gregoriantojd( $endArr[1], $endArr[2], $endArr[0] );

    if ( $start > $end ) echo "Start Date cannot be after End Date."; exit;

    $sth = $MASHPIA_DB->prepare("insert into mivtzoim set name = :name, start = :start, end = :end");
    $sth->execute([
        ':name'     =>  $name, 
        ':start'    =>  $start, 
        ':end'      =>  $end
    ]);
}

// retrieve all mivtzoim rows from dbs
$sth = $MASHPIA_DB->query("select * from mivtzoim");
$mivtzoim = $sth->fetchAll();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Setup Mivtzoim</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="../mivtzoim.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <?php require $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
        <h1>Setup Mivtzoim</h1>

        <form action="index.php" method="post">
            <table>
                <thead>
                    <tr>
                        <th>Mivtzoim Name</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ( $mivtzoim as $mivtza ) {
                        echo "<tr><td><a href='tasks.php?id=" . $mivtza['mivtzoim_id'] . "'>" . $mivtza['name'] . "</a></td><td>" . jdtogregorian( $mivtza['start'] ) . "</td><td>" . jdtogregorian( $mivtza['end'] ) . "</td></tr>";
                    }
                    ?>
                    <tr>
                        <td><input type="text" name="name" /></td>
                        <td><input type="date" name="start" /></td>
                        <td><input type="date" name="end" /></td>
                    </tr>
                </tbody>
            </table>
            <input type="submit" name="submit" value="Save" />
        </form>
    </body>
</html>

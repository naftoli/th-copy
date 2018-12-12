<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim/classes/mivtzoim.php';

if ( isset( $_POST['submit'] ) ) {
    //echo "<pre>"; print_r( $_POST ); echo "</pre>"; 

    // all info with corresponding id is an update qry
    // info without corresponding id is an insert qry
    $qrys = [];
    for ($i = 0; $i < count($_POST['name']); $i++) {
        $id = isset( $_POST['mivtzoim_id'][$i] ) ? $_POST['mivtzoim_id'][$i] : 0;
        $name = trim( $_POST['name'][$i] );
        if ( $name && $_POST['start'][$i] && $_POST['end'][$i] ) {
            $startArr = explode('-', $_POST['start'][$i]);
            $endArr = explode('-', $_POST['end'][$i]);
            $start = gregoriantojd( $startArr[1], $startArr[2], $startArr[0] );
            $end = gregoriantojd( $endArr[1], $endArr[2], $endArr[0] );
            if ( $start > $end ) {
                echo "Start Date cannot be after End Date."; 
                exit;
            }
        } else if ( $name ) {
            echo "You cannot leave the date fields blank.";
            exit;
        }

        if ( $id ) {
            $sth = $MASHPIA_DB->prepare("update mivtzoim set name = :name, start = :start, end = :end where mivtzoim_id = :id");
            $sth->execute([
                ':name'     =>  $name, 
                ':start'    =>  $start, 
                ':end'      =>  $end, 
                ':id'       =>  $id
            ]);
        } else if ( $name && $start && $end ) {
            $sth = $MASHPIA_DB->prepare("insert into mivtzoim set name = :name, start = :start, end = :end");
            $sth->execute([
                ':name'     =>  $name, 
                ':start'    =>  $start, 
                ':end'      =>  $end
            ]);
        }
    }    
}

// retrieve all mivtzoim rows from dbs
$mivtzoim = Mivtzoim::getAll();

function inputDate( $julian ) {
    $gregorian = jdtogregorian( $julian );
    $dateArr = explode('/', $gregorian);
    foreach ( $dateArr as $key => $val ) {
        // make sure that single digits get filled with 0 to have two place holders
        if ( strlen( $val ) == 1 ) $dateArr[$key] = '0' . $val;
    }
    return $dateArr[2] . '-' . $dateArr[0] . '-' . $dateArr[1];
}
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
                        $start_date = inputDate( $mivtza['start'] );
                        $end_date = inputDate( $mivtza['end'] );
                        echo "<input type='hidden' name='mivtzoim_id[]' value='" . $mivtza['mivtzoim_id'] . "' />";
                        echo "<tr><td><input type='text' name='name[]' value='" . $mivtza['name'] . "' /></td><td>";
                        echo "<input type='date' name='start[]' value='" . $start_date . "' /></td><td>";
                        echo "<input type='date' name='end[]' value='" . $end_date . "' /></td><td>";
                        echo "<a href='tasks.php?id=" . $mivtza['mivtzoim_id'] . "'>choose tasks</a></td></tr>";
                    }
                    ?>
                    <tr>
                        <td><input type="text" name="name[]" /></td>
                        <td><input type="date" name="start[]" /></td>
                        <td><input type="date" name="end[]" /></td>
                    </tr>
                </tbody>
            </table>
            <input type="submit" name="submit" value="Save" />
        </form>
    </body>
</html>
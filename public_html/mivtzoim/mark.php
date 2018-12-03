<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim/classes/mivtzoim.php';

if ( isset( $_POST['action'] ) && $_POST['action'] == 'mark' ) {
    //echo "<pre>"; print_r( $_POST ); echo "</pre>"; exit;
    $mivtzoim_id = $_POST['mivtzoim'];
    $parsha = $_POST['parsha'];

    $m = new Mivtzoim( $mivtzoim_id );
    $arrParsha = explode('|', $parsha);
    $start = $arrParsha[0];
    $end = $arrParsha[1];
    $m->setDates( $start, $end );

    // build marks array
    $marks = [];
    foreach ( $_POST as $k => $v ) {
        if ( strpos( $k, '|' ) === false ) continue;
        $info = explode('|', $k);
        $grid_id = $info[0];
        $user_id = $info[1];
        if ( $v == 'on' ) $marks[$grid_id][$user_id] = 1;
        else if ( $v != ''  ) $marks[$grid_id][$user_id] = $v;
    }
    $m->markTasks( $marks );
}

//echo "<pre>"; print_r( $_POST ); echo "</pre>"; exit;
$grade = $_POST['grade'];
$mivtzoim_id = $_POST['mivtzoim'];
$parsha = $_POST['parsha'];

if ( $grade && $mivtzoim_id && $parsha ) {
    try {
        $m = new Mivtzoim( $mivtzoim_id );
    } catch ( \Exception $e ) {
        echo $e->getMessage();
        exit;
    }
    $arrParsha = explode('|', $parsha);
    $start = $arrParsha[0];
    $end = $arrParsha[1];
    $parsha_name = $arrParsha[2];
    $m->setDates( $start, $end );

    $sth = $MASHPIA_DB->prepare("
        SELECT 
            user_id, first, last 
        FROM
            users 
        WHERE
            user_registered > 0 AND class_id = :grade 
        ORDER BY last, first
    ");
    $sth->execute([
        ':grade'    =>  $grade
    ]);
    $users = $sth->fetchAll();
} else {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mark Mivtzoim</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="mivtzoim.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <?php require $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
        <h1>Mark Mivtzoim</h1>

        <h2><?= $parsha_name; ?></h2>

        <form action="mark.php" method="post">
            <input type="hidden" name="action" value="mark" />
            <input type="hidden" name="mivtzoim" value="<?=$mivtzoim_id?>" />
            <input type="hidden" name="grade" value=<?=$grade?>" />
            <input type="hidden" name="parsha" value="<?=$parsha?>" />
            <?php
            // find out how many tasks we need to setup
            try {
                $tasks = $m->getTasks();
            }  catch ( \Exception $e ) {
                echo $e->getMessage();
                exit;
            }
            // find out what the grid ids are
            $grid_ids = [];
            foreach ( $tasks as $short_name => $details ) {
                foreach ( $details as $task ) {
                    $grid_ids[] = $task['grid_id'];
                }
            }
            // find out the user ids
            $user_ids = [];
            foreach ( $users as $user ) {
                $user_ids[] = $user['user_id'];
            }
            $marks = $m->getMarks( $grid_ids, $user_ids );

            echo "<table><thead><tr><th>Student</th>";
            foreach ( $tasks as $short_name => $details ) {
                foreach ( $details as $task ) {
                    echo "<th>" . $task['name'] . "</th>";
                } 
            }
            echo "</tr></thead><tbody>"; 
            foreach ( $users as $user ) {
                echo "<tr><td>" . $user['first'] . ' ' . $user['last'] . "</td>";
                foreach ( $tasks as $short_name => $details ) {
                    foreach ( $details as $task ) {
                        $identifier = $task['grid_id'] . '|' . $user['user_id'];
                        echo "<td>";
                        // figure out if input is checkbox or value
                        if ( $task['quantity'] >= 1 ) {
                            echo "<input type='text' name='" . $identifier . "' size='4' ";
                            if ( isset( $marks[$task['grid_id']][$user['user_id']] ) ) echo "value='" . $marks[$task['grid_id']][$user['user_id']] . "' ";
                            echo "/>";
                        } else {
                            echo "<input type='checkbox' name='" . $identifier . "'";
                            if ( isset( $marks[$task['grid_id']][$user['user_id']] ) ) echo " checked";
                            echo " />";
                        }
                        echo "</td>";
                    } 
                }
                echo "</tr>";
            }
            echo "</tbody></table>";
            ?>
            <br />
            <input type="submit" name="submit" value="Save" />
        </form>
    </body>
</html>
<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim/classes/mivtzoim.php';

if ( !isset( $_REQUEST['id'] ) ) {
    header("Location: index.php");
    exit;
}

$id = $_REQUEST['id'];
$m = new Mivtzoim( $id );
$names = $m->getShortNames();

$name = $m->getName();
$dates = $m->getDates();
$start = $dates['start'];
$end = $dates['end'];
$ms = new MivtzoimSetup( $name, $start, $end );
$short_names = $ms->availableShortNames();
$namesWithTasks = $ms->availableShortNamesWithTasks();

if ( isset( $_POST['submit'] ) ) {
    $tasks = [];
    foreach ( $_POST['name'] as $index => $val ) {
        $tasks[] = $short_names[$index];
    }
    $m->saveShortNames( $tasks );
    // reload short names
    $m = new Mivtzoim( $id );
    $names = $m->getShortNames();
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

        <form action="tasks.php" method="post">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>Task Category</th>
                        <th>Tasks Included</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // foreach ( $short_names as $index => $name ) {
                    //     echo "<tr><td><input type='checkbox' name='name[" . $index . "]'";
                    //     if ( $names && in_array( $name, $names ) ) echo " checked";
                    //     echo " /></td><td>" . $name . "</td></tr>";
                    // }
                    $i = 0;
                    foreach ( $namesWithTasks as $name => $rows ) {
                        echo "<tr><td><input type='checkbox' name='name[" . $i++ . "]'";
                        if ( $names && in_array( $name, $names ) ) echo " checked";
                        echo " /></td><td>" . $name . "</td><td>";
                        foreach ( $rows as $name ) {
                            echo $name . "<br />";
                        }
                        echo "</td></tr>";
                    }
                    ?>
                    <tr>
                        <td><input type="hidden" name="id" value="<?= $id ?>" /></td>
                        <td><input type="submit" name="submit" value="Save" /></td>
                    </tr>
                </tbody>
            </table>
        </form>
    </body>
</html>

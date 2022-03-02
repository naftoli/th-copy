<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
//require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once 'classes/mivtzoim.php';

if ( !isset( $_POST['school'] ) || !isset( $_POST['grade'] ) || !isset( $_POST['mivtzoim'] ) ) {
    header("Location: index.php");
    exit;
} 

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

if ( isset( $_POST['submit'] ) && $_POST['submit'] == 'Save' ) {
    $mivtzoim_id = $_POST['mivtzoim'];
    $m = new Mivtzoim( $mivtzoim_id );

    // build marks array
    $marks = [];
    foreach ( $_POST as $k => $more ) {
        if ( strpos( $k, '|' ) === false ) continue;
        $info = explode('|', $k);
        $grid_id = $info[0];
        $user_id = $info[1];
        $start_date = $info[2];
        $end_date = $info[3];

        if ( is_array( $more ) ) {
            // only get first value of $more 
            // if there's 2 values, the second one will be discarded so that it won't overwrite the first one
            $v = $more[0];
            if ( $v == 'on' ) $marks[$grid_id][$user_id][$start_date][$end_date] = 1;
            else if ( $v != '' ) $marks[$grid_id][$user_id][$start_date][$end_date] = $v;
        }
    }
    $m->markTasks( $marks );
}

//echo "<pre>"; print_r( $_POST ); echo "</pre>"; exit;
$school = $_POST['school'];
$grade = $_POST['grade'];
$mivtzoim_id = $_POST['mivtzoim'];
$task = isset( $_POST['task'] ) ? $_POST['task'] : 0;

if ( $school && $grade && $mivtzoim_id ) {
    try {
        $m = new Mivtzoim( $mivtzoim_id );
    } catch ( \Exception $e ) {
        echo $e->getMessage();
        exit;
    }
    
    $qry = "
        SELECT 
            class_grade,
            class_sub,
            user_id,
            first,
            last
        FROM
            users u
                JOIN
            classes c ON c.class_id = u.class_id
        WHERE
            user_registered > 0
                AND u.school_id = :school";
    if ( $grade > 0 ) $qry .= " AND u.class_id = :grade";
    $qry .= " ORDER BY class_grade , class_sub , last , first";

    $sth = $MASHPIA_DB->prepare( $qry );
    if ( $grade > 0 ) {
        $sth->execute([
            ':school'   => $school,
            ':grade'    => $grade
        ]);
    } else {
        $sth->execute([':school' => $school]);
    }
    $rows = $sth->fetchAll();
    foreach ( $rows as $row ) {
        $class_name = $row['class_grade'] . (empty( $row['class_sub'] ) ? '' : '-' . $row['class_sub']);
        $users[$class_name][] = $row;
    }
} 
//echo "<pre>"; print_r( $users ); echo "</pre>"; exit;
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

        <div class="infobox">
            Syncs from parent accounts to this page. Whoever (parent/bc) enters an amount last, will be the amount that will show/save.<br />
            <span style="color: red; font-weight: bold;">If trying to delete a child's amount, you must write the number 0. Simply deleting the number, will not save as 0.</span>
        </div>

        <form action="" method="post" enctype="multipart/form-data">
            <input type='submit' name='submit' value='Save' />
            <input type="hidden" name="action" value="mark" />
            <input type="hidden" name="mivtzoim" value="<?=$mivtzoim_id?>" />
            <input type="hidden" name="school" value="<?=$school?>" />
            <input type="hidden" name="grade" value="<?=$grade?>" />
            <input type="hidden" name="task" value="<?=$task?>" />
            <h2><?=$schools[$school]?></h2>
            <?php
            // find out how many tasks we need to setup
            try {
                $tasks = $m->getTasks();
                $names = $m->getShortNames();
            }  catch ( \Exception $e ) {
                echo $e->getMessage();
                exit;
            }
            // find out what the grid ids are
            $grid_ids = [];
            foreach ( $tasks as $short_name => $details ) {
                if ( $task > 0 && $names[$task] != $short_name ) continue; // if bc has identified a specific task category to mark, only show those tasks relevant to that task category
                foreach ( $details as $row ) {
                    $grid_ids[] = $row['grid_id'];
                }
            }
            $marks = $m->getMarks( $grid_ids );
            //echo "<pre>"; print_r( $marks ); echo "</pre>";

            echo "<table><thead><tr><th>Grade</th><th>Student</th>";
            foreach ( $tasks as $short_name => $details ) {
                if ( $task > 0 && $names[$task] != $short_name ) continue;
                $numTasksWithQty = 0; // only show total if we have more than one task with qty
                $numTasks = count( $details );
                foreach ( $details as $index => $row ) {
                    echo "<th>" . $row['name'];
                    if ( $numTasks > 1 ) {
                        echo "<br /><span style='color: blue'><i>Week " . ($index + 1) . "</i></span>";
                    }
                    echo "</th>";
                    if ( $row['quantity'] >= 1 ) $numTasksWithQty++;
                } 
                if ( $numTasksWithQty > 1 ) echo "<th>Total</th>";
            }
            echo "</tr></thead><tbody>"; 
            foreach ( $users as $grade => $students ) {
                foreach ( $students as $user ) {
                    echo "<tr><td>" . $grade . "</td><td>" . $user['first'] . ' ' . $user['last'] . "</td>";
                    foreach ( $tasks as $short_name => $details ) {
                        if ( $task > 0 && $names[$task] != $short_name ) continue;
                        $numTasksWithQty = 0; // only show total if we have more than one task with qty
                        $totalQty = 0;
                        foreach ( $details as $row ) {
                            $mark_id = $row['start_date'] . '|' . $row['end_date']; // for finding corrent mark in marks array
                            $identifier = $row['grid_id'] . '|' . $user['user_id'] . '|' . $mark_id;
                            echo "<td>";
                            // figure out if input is checkbox or value
                            if ( $row['quantity'] >= 1 ) {
                                $numTasksWithQty++;
                                echo "<input type='text' name='" . $identifier . "[]' size='4' ";
                                if ( isset( $marks[$row['grid_id']][$user['user_id']][$mark_id] ) ) {
                                    echo "value='" . $marks[$row['grid_id']][$user['user_id']][$mark_id] . "' ";
                                    $totalQty += $marks[$row['grid_id']][$user['user_id']][$mark_id];
                                }
                                echo "/>";
                            } else {
                                echo "<input type='checkbox' name='" . $identifier . "[]'";
                                if ( isset( $marks[$row['grid_id']][$user['user_id']][$mark_id] ) ) echo "value='1' checked";
                                echo " />";
                                echo "<input type='hidden' name='" . $identifier . "[]' value='0' />";
                            }
                            echo "</td>";
                        } 
                        if ( $numTasksWithQty > 1 ) {
                            echo "<td>" . $totalQty . "</td>";
                        }
                    }
                    echo "</tr>";
                }
            }
            echo "</tbody></table>";
            ?>
            <br />
            <input type='submit' name='submit' value='Save' />
        </form>
    </body>
</html>

<?php
// syncing issues are related to duplicate marks so we need to delete the duplicate marks
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim/classes/mivtzoim.php';

if ( $admin_user['auth'] != 'super' ) {
    echo "You don't have permission to view this page.";
    exit;
}

// retrieve all mivtzoim rows from dbs
$sth = $MASHPIA_DB->query("select * from mivtzoim");
$mivtzoim = $sth->fetchAll();
//echo "<pre>"; print_r( $parshos ); echo "</pre>";
if ( isset( $_POST['submit'] ) ) {
    $mivtzoim_id = $_POST['mivtzoim'];
    $task = isset( $_POST['task'] ) ? $_POST['task'] : 0;

    try {
        $m = new Mivtzoim( $mivtzoim_id );
    } catch ( \Exception $e ) {
        echo $e->getMessage();
        exit;
    }

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

    // get task ids
    $task_ids = [];
    $grids = implode(',', $grid_ids);
    $stmt = $MASHPIA_DB->query("
        SELECT 
            date_task_id
        FROM
            date_tasks 
        WHERE
            grid_id IN ($grids) 
    ");
    $rows = $stmt->fetchAll();
    foreach ( $rows as $row ) {
        $task_ids[] = $row['date_task_id'];
    }
    //echo "<pre>"; print_r( $task_ids ); echo "</pre>";

    // get marks
    $marks = [];
    $strTaskIds = implode(',', $task_ids);
    $stmt = $MASHPIA_DB->query("
        SELECT 
            *
        FROM
            date_tasks_marks
        WHERE
            date_task_id IN ($strTaskIds)
    ");
    $rows = $stmt->fetchAll();
    foreach ( $rows as $row ) {
        $marks[$row['user_id']][$row['date_task_id']][] = [
            'date'  =>  $row['mark_date'],
            'qty'   =>  $row['done_qty']
        ];
    }
    //echo "<pre>"; print_r( $marks ); echo "</pre>";
    // find duplicates
    $duplicates = [];
    foreach ( $marks as $user => $more ) {
        foreach ( $more as $task_id => $other ) {
            if ( count( $other ) > 1 ) {
                $duplicates[$user][$task_id] = $other[0];
            }
        }
    }
    //echo "<pre>"; print_r( $duplicates ); echo "</pre>";

    // delete duplicates by deleting all tasks and then reentering it one time
    $stmt1 = $MASHPIA_DB->prepare("delete from date_tasks_marks where user_id = :user and date_task_id = :task");
    $stmt2 = $MASHPIA_DB->prepare("insert into date_tasks_marks set date_task_id = :task, user_id = :user, mark_date = :date, done_qty = :qty, mark_points = 0.50");
    foreach ( $duplicates as $user => $more ) {
        foreach ( $more as $task_id => $details ) {
            $stmt1->execute([
                ':user' =>  $user, 
                ':task' =>  $task_id
            ]);
            $stmt2->execute([
                ':task' =>  $task_id, 
                ':user' =>  $user, 
                ':date' =>  $details['date'],
                ':qty'  =>  $details['qty']
            ]);
        }
    }
    echo "done.";
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Fix Duplicates</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <?php require $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
        <h1>Fix Duplicates</h1>

        <form action="fixSyncing.php" method="post">
            <select name="mivtzoim" id="mivtzoim">
                <option value="0">Select Mivtzoim</option>
                <?php
                foreach ( $mivtzoim as $row ) {
                    echo "<option value='" . $row['mivtzoim_id'] . "'>" . $row['name'] . "</option>";
                }
                ?>
            </select>
            <br /><br />

            <div id="taskDisplay" style="display: none;">
                <select name="task" id="task"></select>
                <br /><br />
            </div>

            <input type="submit" name="submit" value="Submit" id="submit" class="disabled" disabled />
        </form>
    </body>

    <script>                
        $("#mivtzoim").change( function() {
            $("#submit").addClass('disabled');
            var id = $(this).val();
            $.post('../ajax/mivtzoim.php', { id : id }, function( success ) {
                var mivtzoim = JSON.parse( success );
                console.log( mivtzoim );
                if ( !mivtzoim.error ) {
                    var count = 0; // keep track of how many tasks are actually being output
                    var html = "<option value='0'>All Tasks</option>";
                    for ( var m in mivtzoim.data ) {
                        html += "<option value='" + m +  "'>" + mivtzoim.data[m] + "</option>";
                        count++;
                    }
                    $("#task").empty();
                    $("#task").append( html );
                    if ( count > 1 ) {
                        $("#taskDisplay").show();
                        $("#submit").attr('disabled', false);
                        $("#submit").removeClass('disabled');
                    } else {
                        $("#taskDisplay").hide();
                        var html = "<input type='hidden' name='task' value='" + m + "' />";
                        $("#taskDisplay").after(html);
                        $("#submit").attr('disabled', false);
                        $("#submit").removeClass('disabled');
                    }
                } else {
                    alert( mivtzoim.data );
                    $("#submit").attr('disabled', false);
                    $("#submit").removeClass('disabled');
                }
            });
        });
    </script>
</html>
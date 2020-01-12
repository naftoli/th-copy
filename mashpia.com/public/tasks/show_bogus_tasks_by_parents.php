<?php
ini_set('display_errors',1);
require '../db.php';

$tasks = [];
$parents = [];
$sql = "select a.admin_id, a.first, a.last, a.admin_address1, a.admin_city, dt.name as task from date_tasks dt 
        join date_tasks_missions dtm using (date_tasks_mission_id) 
        join admins a on a.admin_id = dtm.created_by_parent 
        where dtm.start_date >= 2458670 
        and dtm.created_by_parent in (
        select admin_id from admin_auths where id in (
        select user_id from users where school_id = 5)) 
        group by a.admin_id, dt.name";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $id = $row['admin_id'];
    $parents[$id] = [
        'name'  =>  $row['first'] . ' ' . $row['last'], 
        'address'   =>  $row['admin_address1'] . ' ' . $row['admin_city']
    ];
    $tasks[$id][] = $row['task'];
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                padding: 5px;
                font-size: 14px;
                font-family: Verdana;
                vertical-align: top;
            }
            table {
                width:75%; 
                border-collapse: collapse;
                margin: auto;
            }
            tr {
                border-bottom: 1px solid grey;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <th>Parent</th>
                <th>Address</th>
                <th>Tasks Created</th>
            </tr>
            <?php
            foreach ( $parents as $id => $parent ) {
                echo "<tr><td>" . $parent['name'] . "</td><td>" . $parent['address'] . "</td><td>";
                foreach ( $tasks[$id] as $idx => $task ) {
                    echo ( $idx + 1 ) . ". " . $task . "<br />";
                }
            }
            ?>
        </table>
    </body>
</html>
<?php
$admin_auth = array('school'); 
require('header.php');

if (isset($_POST['submit'])) {
    $campaign = intval(mysql_real_escape_string($_POST['subject']));
    $task = mysql_real_escape_string($_POST['task']);
    $points = intval(mysql_real_escape_string($_POST['points']));
    $base = $admin_user['auth'] == 'super' ? 1 : $admin_user['auths']['school'][0];
    
    $sql = "insert into achievement_tasks
            set subject_id = " . $campaign . ",
            task = '" . $task . "',
            points = " . $points . ",
            base = " . $base . ",
            platoon = 1";
    if (mysql_query($sql)) {
        $msg = "You have successfully created this task.";
    } else {
        $msg = "There was an error creating this task.";
    }
}

$campaigns = array();
$sql = "select * from mashpiadb.subjects where subject_type in ('', 'WWTC', 'Tanya', 'achievement')";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $campaigns[$row['subject_id']] = $row['subject_name'];
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Create Achievement Card Task</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="scripts/jquery-ui-1.9.2.custom.min.css" />
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript" src="scripts/jquery-ui-1.9.2.custom.min.js"></script>
    </head>
    
    <body>
        <? include('admin_header.php'); ?>
        <h1>Create Achievement Card Task</h1>
        
        <?php
        if (isset($msg)) {
            echo $msg;
        } else {
            ?>
            <form method="post" action="newAchievementTasks.php">
                <p>
                    Choose Campaign<br />
                    <select name="subject">
                        <?php
                        foreach ($campaigns as $id => $campaign) {
                            echo "<option value='" . $id . "'>" . $campaign . "</option>";
                        }
                        ?>
                    </select>
                </p>
                
                <p>
                    Name of Task<br />
                    <input type="text" name="task" size="50" />
                </p>
                
                <p>
                    Amount of points (enter the default amount - this can be changed when creating achievement cards)<br />
                    <input type="text" name="points" size="5" />
                </p>
                
                <p>
                    <input type="submit" name="submit" value="Create" />
                </p>
            </form>
        <?php } ?>
    </body>
</html>
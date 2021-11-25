<?php
$admin_auth = ['school'];
require 'header.php';

require 'class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$info = [];
if (isset($_POST['submit'])) {
    include("classes/user.php");
    include("classes/user_track.php");
    include("classes/school_class.php");
    include("class.taskExceptions.php");
    include("classes/date_tasks_mission.php");
    include("classes/daily_task.php");
    include("classes/weekly_task.php");
    include("classes/shabbos_task.php");
    include("classes/no_label_task.php");
    include("classes/task.php");
    include("classes/date_tasks_mark.php");

    $arrStart = explode('-', $_POST['start']);
    $arrEnd = explode('-', $_POST['end']);

    $start = gregoriantojd($arrStart[1], $arrStart[2], $arrStart[0]);
    $end = gregoriantojd($arrEnd[1], $arrEnd[2], $arrEnd[0]);

    $sql = "select * from users where user_registered > 0 and school_id = " . mysql_real_escape_string($_POST['school']);

    $grade = $_POST['grade'];
    if ($grade > 0) {
        $sql .= " and class_id = " . mysql_real_escape_string($grade);
    }

    $users = [];
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $users[] = $row;
    }

    foreach ($users as $user) {
        $user = new user($user); // create a new user
        $user->get_school_class(); // and get his class
        $user->get_user_tracks( -1, $start, $end, [], $user->lang_id ); // get the users tracks
        $info[] = $user;
    }
    echo "<pre>"; print_r($info); echo "</pre>";

    $types = ['daily_tasks', 'weekly_tasks', 'shabbos_tasks', 'no_label_tasks'];
    $totals = [];
    foreach ($info as $user) {
        $totals[$user->user_id] = 0;
        foreach ($user->user_tracks as $track) {
            foreach ($types as $type) {
                foreach ($track->$type as $task) {
                    $totals[$user->user_id] += 0.5;
                }
            }
        }
    }
}
?>
<!doctype html>
<html>
    <head>
        <title>Potential Miles</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <?php include('admin_header.php'); ?>
        <h1>Potential Miles</h1>
        <?php if (isset($_POST['submit'])) : ?>
            <table>
                <tr>
                    <th>Grade</th>
                    <th>Total Points Possible to Earn</th>
                </tr>
                <?php
                foreach ($users as $user) {
                    echo "<tr><td>$grade</td>";
                }
                ?>
            </table>
        <?php else : ?>
            <form action="" method="post">
                School: <select id="school" name="school">
                    <?php
                    foreach ($schools as $id => $school) {
                        echo "<option value='$id'>$school</option>";
                    }
                    ?>
                </select><br /><br />
                Class: <select id="grade" name="grade"></select><br /><br />
                Start Date: <input type="date" name="start" />
                End Date: <input type="date" name="end" /><br /><br />
                <input type="submit" name="submit" value="submit" />
            </form>
        <?php endif; ?>
    </body>
    <script>
        $(function () {
            $("#school").change( function () {
                const id = $(this).val()
                $.get('ajax/getClasses.php', { id: id }, function(result) {
                    const grades = JSON.parse(result)
                    const info = Object.entries(grades).sort((a, b) => a[1] - b[1])
                    let html = `<option value='0'>All Classes</option>`
                    for (let i in info) {
                        let grade = info[i]
                        let id = grade[0]
                        let name = grade[1]
                        html += `<option value=${id}>${name}</option>`
                    }
                    $("#grade").empty()
                    $("#grade").append(html)
                })
            })
        })
    </script>
</html>

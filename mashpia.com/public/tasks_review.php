<?php
$admin_auth = ['school'];
require 'header.php';
$school_id = $admin_user['auths']['school'][0];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Review Tasks Accomplishments</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
                font-size: 12px;
                padding: 5px;
                border: 1px solid grey;
            }
        </style>
    </head>
    <body>
        <?php include('admin_header.php');?>
        <h1>Review Tasks Accomplishments</h1>
        <?php
        if (isset($_POST['submit'])) {
//            echo "<pre>"; print_r($_POST); echo "</pre>";
            foreach ($_POST as $k => $v) {
                if ($k == 'grade') continue;
                if (empty($v)) {
                     if ($k == 'from' || $k == 'to') {
                         echo "You must choose dates.";
                     } else {
                         echo "You must choose a $k.";
                     }
                     exit;
                }
            }

            $start = $_POST['from'];
            $end = $_POST['to'];
            $startInfo = explode('-', $start);
            $endInfo = explode('-', $end);
            $start = gregoriantojd($startInfo[1], $startInfo[2], $startInfo[0]);
            $end = gregoriantojd($endInfo[1], $endInfo[2], $endInfo[0]);

            $users = [];
            $sql = "select * from users where user_registered > 0 and school_id = $school_id";
            if ($_POST['grade']) $sql .= " and class_id = " . mysql_real_escape_string($_POST['grade']);
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $users[$row['user_id']] = $row['first'] . ' ' . $row['last'];
            }

            $tasks = $_POST['task'];
            $subject = mysql_real_escape_string($_POST['subject']);
            $info = [];
            foreach ($users as $user_id => $name) {
                foreach ($tasks as $task) {
                    $task = mysql_real_escape_string($task);
                    $sql = "select * from date_tasks_marks dtmarks
                            join date_tasks dt using (date_task_id) 
                            join date_tasks_missions dtm using (date_tasks_mission_id) 
                            where dtm.subject_id = $subject
                            and dt.cat = '$task' 
                            and dtm.start_date >= $start
                            and dtm.end_date <= $end
                            and user_id = " . $user_id;
                    $result = mysql_query($sql);
                    while ($row = mysql_fetch_assoc($result)) {
                        $info[$user_id][$task][] = $row;
                    }
                }
            }

            $numTasks = count($tasks);
            echo "<table><tr><th>Student</th>";
            for ($i = 0; $i < $numTasks; $i++) echo "<th>" . $tasks[$i] . "</th>";
            echo "</tr>";

            foreach ($users as $user_id => $user) {
                echo "<tr><td>" . $user . "</td>";
                for ($i = 0; $i < $numTasks; $i++) {
                    echo "<td>" . count($info[$user_id][$tasks[$i]]) . "</td>";
                }
                echo "</tr>";
            }

            echo "</table>";
        } else {
        ?>
        <form action="" method="post">
            From: <input name="from" id="from" type="date" />
            To: <input name="to" id="to" type="date" />
            <br /><br />
            Grade:
            <select name="grade" id="grade"></select>
            <br /><br />
            Campaign:
            <select name="subject" id="subject"></select>
            <br /><br />
            Task (category):
            <select name="task[]" id="task" multiple></select>
            <br /><br />
            <input type="submit" name="submit" value="Show Report" />
        </form>
        <?php } ?>
    </body>
    <script>
        $( function () {
            const school_id = <?= $school_id ?>;
            console.log(school_id)

            $.get('ajax/getClasses.php', { id: school_id }, function( result ) {
                const grades = JSON.parse(result)
                fillSelect(grades, 'grade')
            })

            $.get('ajax/getCampaigns.php', { id: school_id }, function( result ) {
                const campaigns = JSON.parse(result)
                fillSelect(campaigns, 'subject')
            })

            $("#subject").change( function () {
                const id = $(this).val()
                const start = $("#from").val()
                const end = $("#to").val()
                const grade = $("#grade").val()
                console.log(start, end)
                if (!start || !end) {
                    alert('You must enter dates!')
                    return false
                }
                $.get('ajax/getTasks.php', { subject: id, start: start, end: end, school: school_id, grade: grade }, function( result ) {
                    const tasks = JSON.parse(result)
                    console.log(tasks)
                    let html = ""
                    for (cat in tasks) {
                        html += `<option value='${cat}'>${cat}</option>`
                    }
                    $("#task").empty()
                    $("#task").append(html)
                })
            })
        })

        // helper function to extract info and populate the select element
        function fillSelect(list, elem) {
            let html = "<option value='0'>Choose One</option>"
            for (id in list) {
                let value = list[id]
                html += `<option value=${id}>${value}</option>`
            }
            const el = '#' + elem
            $(el).empty()
            $(el).append(html)
        }
    </script>
</html>

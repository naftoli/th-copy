<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$pesukim = ['Saying', 'Learning', 'Teaching'];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mark 12 Pesukim</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            select, input[type="submit"] {
                padding: 10px;
                /* rounded corners */
                border-radius: 5px;
                border: 1px solid #ccc;
                background-color: #f0f0f0;
                color: #333;
                font-size: 14px;
            }
            input[type="submit"]:disabled {
                background-color: #ccc;
                color: #999;
                cursor: default;
            }
            .shortName {
                font-size: 14px;
                padding-bottom: 0;
            }
            .taskName {
                font-size: 10px;
                border-bottom: 1px solid #ccc;
                padding-bottom: 10px;
            }
            input[type="checkbox"] {
                width: 20px;
                height: 20px;
                margin: auto 0;
            }
            tr, td {
                vertical-align: middle;
                padding: 8px;
            }
        </style>
    </head>
    <body>
        <?php require $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
        <h1>Mark 12 Pesukim</h1>

        <form action="" method="post">
            <select name="school" id="school">
                <option value="0">Select School</option>
                <?php
                foreach ( $schools as $id => $school ) { 
                    echo "<option value='" . $id . "'>" . $school . "</option>";
                }
                ?>
            </select>
            <br /><br />

            <select name="grade" id="grade">
                <option value="0">Select Grade</option>
            </select>
            <br /><br />

            <select name="student" id="student">
                <option value="0">Select Student</option>
            </select>
            <br /><br />

            <!-- <select name="pesukim" id="pesukim">
                <option value="0">Select Pesukim</option>
                <?php
                // foreach ($pesukim as $type) {
                    // echo "<option value='" . $type . "'>" . $type . "</option>";
                // }
                ?>
            </select>
            <br /><br /> -->

            <input type="submit" name="submit" value="Submit" id="submit" disabled />
            <br /><br />

            <div id="taskDisplay"></div>
        </form>
    </body>

    <script>                
        $("#school").change( function() {
            var school = $(this).val();
            $.get('/ajax/getClasses.php?flat=true', { id : school }, function( info ) {
                var grades = $.parseJSON( info );
                var html = "<option value='0'>Choose Grade</option>";
                html += "<option value='-1'>All Grades</option>";
                for (var g in grades) {
                    html += "<option value='" + grades[g][0] + "'>" + grades[g][1] + "</option>";
                }
                $("#grade").empty();
                $("#grade").append( html );
            });
        });

        $("#grade").change( function() {
            let school = $("#school").val();
            let grade = $(this).val();
            if (grade > 0) {
                $.post('/ajax/getStudents.php', { school : school, class : grade }, function( info ) {
                    var students = $.parseJSON( info );
                    var html = "<option value='0'>Choose Student</option>";
                    for (let student in students) {
                        html += "<option value='" + student + "'>" + students[student] + "</option>";
                    }
                    $("#student").empty();
                    $("#student").append( html );
                });
            }
        });

        const toggleSubmit = () => {
            let grade = $("#grade").val();
            let student = $("#student").val();
            // let type = $("#pesukim").val();
            if (grade > 0 && student > 0) {
                $("#submit").attr('disabled', false);
            } else {
                $("#submit").attr('disabled', true);
            }
        }

        $("#grade").change( toggleSubmit );
        $("#student").change( toggleSubmit );
        // $("#pesukim").change( toggleSubmit );

        $("#submit").click( function(e) {
            e.preventDefault();
            let student = $("#student").val();
            $("#taskDisplay").html("<p>Loading tasks...</p>");
            $.post('getTasks.php', { student }, function( info ) {
                let tasks = $.parseJSON( info );
                let html = "<table class='taskList'>";
                for (let l in tasks.sorted_pesukim_labels) {
                    let label = tasks.sorted_pesukim_labels[l];
                    for (let task of tasks.pesukim_tasks) {
                        if (task.label_name == label) {
                            const id = student + ":" + task.date_task_id + ":" + task.end_date 
                            html += "<tr><td class='shortName'>" + task.short_name + "</td><td rowspan='2' class='checkbox'>";
                            html += "<input type='checkbox' id='" + id + "' class='taskCheckbox' name='task_" + task.date_task_id + "' " + 
                            (task.date_task_mark.marked ? "checked" : "") + (task.disable ? " checked disabled" : "") + "' /></td>";
                            html += "</tr><tr><td class='taskName'>" + task.task_name + "</td></tr>";
                        }
                    }
                }
                html += "</table>";
                $("#taskDisplay").html( html );

                $(".taskCheckbox").change( function() {
                    let params = $(this).attr('id').split(':');
                    let checked = $(this).attr('checked');
                    let url, function_name;
                    if (checked) {
                        url = '/add_functions.php';
                        function_name = 'add_task_mark';
                    } else {
                        url = '/delete_functions.php';
                        function_name = 'delete_task_mark';
                    }
                    url += "?function_name=" + function_name + "&parameters=" + params;
                    $.getJSON(url, function(success) {
                        if (success == false) {
                            alert("Update not performed.");
                        }
                    });
                });
            });
        });
    </script>
</html>
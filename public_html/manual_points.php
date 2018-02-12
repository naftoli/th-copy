<?php
ini_set('display_errors', 1);
$admin_auth = array('school'); 
require('header.php');

require_once 'class.adminSchools.php';
require_once 'class.schoolClasses.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$classes = array();
foreach ($schools as $id => $school) {
    $sc = new SchoolClasses($id);
    $classes[$id] = $sc->getClasses();
}
//echo "<pre>"; print_r($classes); echo "</pre>";
$msg = '';
if (isset($_POST['submit'])) {
    //echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
    $school = mysql_real_escape_string($_POST['school']);
    $grade = mysql_real_escape_string($_POST['grade']);
    $user = mysql_real_escape_string($_POST['user']);
    $action = mysql_real_escape_string($_POST['action']);
    $points = abs(mysql_real_escape_string($_POST['points']));
    if ($action == -1) {
        $points *= -1;
    }
    
    $users = array();
    if ($user == -1) {
        // get all users from class
        $users = array();
        $sql = "select user_id from users where class_id = " . $grade . " and user_registered > 0";
        $result = mysql_query( $sql );
        while ($row = mysql_fetch_assoc( $result )) {
            $users[] = $row['user_id'];
        }
    } else {
        $users[] = $user;
    }
    //echo "<pre>"; print_r( $users ); echo "</pre>"; exit;
    
    $success = true;
    mysql_query('set autocommit=0');
    mysql_query('begin');
    foreach ($users as $user) {
        $sql = "insert into pointsDB.user_points
                set user_id = " . $user . ",
                institution_id = " . $school . ",
                class_id = " . $grade . ",
                points = " . $points . ",
                created = now(),  
                resource_name = 'admin_users_manual'";
        if (!mysql_query($sql)) {
            $success = false;
            break;
        }
    }
    if ($success) {
        $msg = abs($points) . " points has been successfully ";
        if ($action == -1) $msg .= "subtracted.";
        else if ($action == 1) $msg .= "added.";
        mysql_query('commit');
    } else {
        $msg = "There was an error.";
        mysql_query('rollback');
    }
    mysql_query('set autocommit=1');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Add / Subtract Points</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <style>
            .msg {
                color: red;
                padding-bottom: 20px;
            }
        </style>
    </head>
    
    <body>
        <? include('admin_header.php'); ?>
        <h1>Add / Subtract Points</h1>
        
        <?php if ($msg != '') : ?>
            <div class='msg'>
                <?=$msg?>
            </div>
        <?php endif; ?>
        
        <form action="manual_points.php" method="post" id="pointsForm">
            <?php
            if (count($schools) > 1) {
                echo "<select name='school' id='school'>";
                echo "<option value='0'>Choose School</option>";
                foreach ($schools as $id => $school) {
                    echo "<option value='" . $id . "'>" . $school . "</option>";
                }
                echo "</select>";
            } else {
                $id = key($schools);
                echo "<select name='s' disabled>";
                echo "<option value='" . $id . "'>" . $schools[$id] . "</option>";
                echo "</select>";
                echo "<input type='hidden' name='school' value='" . $id . "' />";
            }
            ?>
            <br />
            <br />
            
            <select name='grade' id='grade'>
                <option value='0'>Select Grade</option>
                <?php
                if (count($schools) == 1) {
                    foreach ($classes as $school => $info) {
                        foreach ($info as $grade) {
                            $name = $grade['class_grade'] . (empty($grade['class_sub']) ? '' : '-' . $grade['class_sub']);
                            echo "<option value='" . $grade['class_id'] . "'>" . $name . "</option>";
                        }
                    }
                }
                ?>
            </select>
            <br />
            <br />
            
            <select name='user' id='user'>
                <option value='0'>Select Child</option>
            </select>
            <br />
            <br />
            
            <div id="action" style="display: none">
                <input type="radio" name="action" value="1" class="action" /> Add<br />
                <input type="radio" name="action" value="-1" class="action" /> Subtract<br />
                <input type="text" name="points" placeholder="Points" id="points" /><br /><br />
                <input type="submit" name="submit" value="submit" id="submit" />
            </div>
        </form>
    </body>
    <script>
        $( function() {
            $("#user").change( function() {
                var val = $(this).val();
                if (val != 0) $("#action").show();
                else $("#action").hide();
            });
            
            $("#grade").change( function() {
                var id = $(this).val();
                $.get('ajax/getUsers.php', { id : id }, function( info ) {
                    var users = JSON.parse( info );
                    console.log(users);
                    var html = "<option value='0'>Select Child</option>";
                    html += "<option value=-1'>All Children</option>";
                    for (var u in users) {
                        html += "<option value='" + u + "'>" + users[u] + "</option>";
                    }
                    $("#user").empty();
                    $("#user").append( html );
                });
            });
            
            $("#school").change( function() {
                var id = $(this).val();
                $.get('ajax/getClasses.php', {
                    id : id,
                    hasUsers : 1
                }, function( info ) {
                    var grades = $.parseJSON( info );
                    var html = "<option value='0'>Select Grade</option>";
                    for (var g in grades) {
                        html += "<option value='" + g + "'>" + grades[g] + "</option>";
                    }
                    $("#grade").empty();
                    $("#grade").append( html );
                    $("#user").empty();
                    $("#user").append("<option value='0'>Select Child</option>");
                });
            });
            
            $("#submit").click( function() {
                var school = $("#school").val();
                var grade = $("#grade").val();
                var user = $("#user").val();
                var action = $(".action").is(":checked");
                var points = $("#points").val();
                if (school == 0) {
                    alert('Please choose a school.');
                }
                else if (grade == 0) {
                    alert('Please choose a grade.');
                }
                else if (user == 0) {
                    alert('Please choose a child.');
                }
                else if (!action || action == 'false') {
                    alert('Please identify if you are adding or subtracting.');
                }
                else if (points == '' || points == 0) {
                    alert('You must enter a number.');
                } else {
                    return true;
                }
                return false;
            });
        });
    </script>
</html>

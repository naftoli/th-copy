<?
$admin_auth = array('user'); 
require('header.php');

require_once 'class.achosStudent.php';
$as = new AchosStudent($admin_user['admin_id']);
$user = $as->getStudentID();

$sql2 = "select label_id, label_name from labels";
$result2 = mysql_query($sql2);
$labels = array();
while ($row2 = mysql_fetch_assoc($result2)) {
    $labels[$row2['label_id']] = $row2['label_name'];
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Create a New Task</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="scripts/jquery-ui-1.9.2.custom.min.css" />
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript" src="scripts/jquery-ui-1.9.2.custom.min.js"></script>
        
        <link rel="stylesheet" href="newTask.css" />
        <script type='text/javascript'>
            $(function() {                 
                $("#toggleParshos").click( function() {
                    $(".parshos input").trigger('click');
                });
                
                $("#submit").click( function() {
                    var errors = '';
                    
                    if ($("#name").val() == '') {
                        if (errors != '') 
                            errors += '\n';
                        errors += "You have not entered a task.";
                    }
                    if ($("#category").val() == '') {
                        if (errors != '') 
                            errors += '\n';
                        errors += "You have not entered a category.";
                    }
                    if ($("#points").val() == '') {
                        if (errors != '') 
                            errors += '\n';
                        errors += "You must enter a point value.";
                    }
                    if ($("#label").val() == 0) {
                        if (errors != '') 
                            errors += '\n';
                        errors += "You must choose a label.";
                    }
                    if (!$('.mission').is(':checked')) {
                        if (errors != '') 
                            errors += '\n';
                        errors += 'You must check off at least one parsha.';
                    }
                    
                    if (errors != '') {
                        alert(errors);
                        return false;
                    }
                });
            });
        </script>
    </head>
    <body>
        <? include('admin_header.php'); ?>
        <h1>Create a New Task</h1>
        
        <?
        if (isset($_GET['msg'])) {
            echo "<div style='color: red'>" . urldecode($_GET['msg']) . "</div><br />";
        }
        ?>
        
        <p>
            <i>Please Note: All fields are mandatory</i>
        </p>
        
        <form action='createNewStudentTask.php' method='post'>            
                        
            <input type="hidden" name="campaign" value="1" />

            <p>
                New Task Name:<br />
                <input type='text' name='name' size='80' id='name' />
            </p>
            
            <p>
                Amount of points awarded for completing the task: <br />
                <input type='text' name='points' id='points' size='2' /> Points (only whole points please).
            </p>
            
            <p>
                Task Label for mission sheets:<br />
                <select name='label' id='label'>
                    <option value='0'>Choose label
                    <?
                    foreach ($labels as $id => $label) {
                        echo "<option value=" . $id . ">" . $label . "</option>";
                    }
                    ?>
                </select>
            </p>
            
            <input type="hidden" name="user" value="<?=$user?>" />

            <p align='center'>
                <input type='submit' name='submit' id='submit' value='Create Task' />
            </p>
        </form>
        
    </body>
</html>

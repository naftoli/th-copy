<?php
$admin_auth = array('school'); 
require('header.php');

if (isset($_POST['submit'])) {
    if ( isset( $admin_user['auths']['school'] ) && count( $admin_user['auths']['school'] ) == 1 ) {
        $school_id = $admin_user['auths']['school'][0];
        $date = $_POST['date'];
        if ( $school_id && $date ) {
            $sql = "DELETE FROM pointsDB.achievement_cards WHERE status = 'not scanned' AND created < '" . mysql_real_escape_string( $date ) . "' AND institution_id = " . $school_id;
            //echo $sql;
            if (mysql_query($sql)) {
                $msg = "You have successfully deleted all unscanned achievement cards from before " . $date;
            } else {
                $msg = "There was an error deleting the achievement cards.";
            }
        } else { 
        }
    } else {
        if ( !isset( $admin_user['auths']['school'] ) ) {
            $msg = "You don't have any schools associated with your account.";
        } else {
            $msg = "You have more than one school associated with your account. Please contact Tzivos Hashem.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Delete Achievement Cards</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="scripts/jquery-ui-1.9.2.custom.min.css" />
    </head>
    
    <body>
        <? include('admin_header.php'); ?>
        <h1>Delete Achievement Cards</h1>
        
        <?php
        if (isset($msg)) {
            echo $msg;
        } else {
            ?>
            <form method="post" action="remove_old_achievement_cards.php" id="deleteForm">
                <p>
                    Delete all unscanned achievement cards that were create before 
                    <input type="date" name="date" id="date" /><br />
                    <input type="submit" value="delete" name="submit" />
                </p>
            </form>
        <?php } ?>
    </body>

    <script>
        $( function() {
            $("#deleteForm").submit( function() {
                var d = $("#date").val();
                if( !d ) {
                    alert("You must choose a date.");
                    return false;
                }
                return confirm("Are u sure u want to delete these cards (this action is permanent)?");
            });
        });
    </script>
</html>
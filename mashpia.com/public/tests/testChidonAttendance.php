<?php
$user_id = $_GET['id'];
?>
<!DOCTYPE html>
<html>
    <head>
        <script src="../scripts/jquery-1.8.3.js"></script>
        <script>
            $( function() {
                var user_id = <?=$user_id?>;
                $.post('testAttendance.php', { user_id : user_id }, function( res ) {
                    alert( res );
                    window.close();
                });
            });
        </script>
    </head>
    <body>
    </body>
</html>
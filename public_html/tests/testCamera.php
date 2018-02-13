<?php
$url = "http://mashpia.com/tests/testChidonAttendance.php?id=8273";
$qr = "http://api.qrserver.com/v1/create-qr-code/?data=" . urlencode( $url ) . "&size=150x150";
?>
<!DOCTYPE html>
<html>
    <head>

    </head>
    <body>
        <img src="<?=$qr?>" alt="" title="" />
    </body>
</html>
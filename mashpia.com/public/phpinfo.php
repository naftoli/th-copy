<?php
if (isset($_COOKIE['naftoli'])) {
    $t=time();
    echo($t . "<br>");
    echo(date("Y-m-d",$t));
    phpinfo();
}
?>

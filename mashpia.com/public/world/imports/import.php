<?php
$info = file_get_contents("https://chabadkid.com/getuser.php?mashpia=mashpia_mbp_all");
echo "<pre>"; print_r($info); echo "</pre>";
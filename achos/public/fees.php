<?php
$today = unixtojd();
$cutoff = 2456547;
if ($today < $cutoff) {
    $reg_fee = 40;
} else {
    $reg_fee = 50;
}
?>

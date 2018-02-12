<?php

/*
fees.php

Calculate the fees for registration on a given day.

If after 9/3/2015 the fee is $50, if before it is $40

Cutoff date: 2457269 or 9/3/2015

To convert jd to Date use `jdtogregorian(2457269);`
*/

$today = unixtojd();
$cutoff = 2457269;
if ($today < $cutoff) {
    $reg_fee = 40;
} else {
    $reg_fee = 50;
}
?>

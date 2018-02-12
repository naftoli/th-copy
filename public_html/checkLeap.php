<?php
$hYear = 5776;
if (((7 * $hYear + 1) % 19) < 7) {
	$leap = true;
} else {
	$leap = false;
}
echo $leap;
?>
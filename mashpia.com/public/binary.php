<?
function mySearch(array $arr, $val) {
	$start = 0;
	$end = count($arr) - 1;
	while ($start <= $end) {
		echo "Start: " . $start . "<br />";
		echo "End: " . $end . "<br />";
		$mid = (int)($start + $end) / 2;
		if ($val === $arr[$mid]) {
			echo "Found at index number: " . $mid . "<br />";
			exit;
		} else if ($val > $arr[$mid]) {
			$start = $mid + 1;
		} else {
			$end = $mid - 1;
		}
	}
	echo "Not found.";
}

$arr = array(3,6,8,9,11,19);
mySearch($arr, 19);
?>
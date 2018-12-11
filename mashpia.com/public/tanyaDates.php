<?
$dates = array(2456955, 2457122, 2457130, 2457206);
$new = array();
$review = array();

for ($i = 0; $i < 4; $i++) {
	$start = $dates[$i++];
	$end = $dates[$i];
	
	$cnt = 1;
	while ($start < $end) {
		if (++$cnt % 4 == 0) {
			$review[] = $start;
			$start += 6;
			$review[] = $start++;
		} else {
			$new[] = $start;
			$start += 6;
			$new[] = $start++;
		}
	}
}

echo implode(',', $new) . "<br />";
echo implode(',', $review);
?>
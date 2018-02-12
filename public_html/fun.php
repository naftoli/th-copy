<?
$num = array();
for ($i = 0; $i < 40; $i++) {
	$num[] = $i;
}

function evenLessThan($max) {
	return function($item) use ($max) {
		return $item % 2 === 0 && $item < $max;
	};
}

$func = evenLessThan(16);
$newArr = array_filter($num, $func);
echo "<pre>"; print_r($newArr); echo "</pre>";
?>
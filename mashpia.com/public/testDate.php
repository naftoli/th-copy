<?
$d1 = new DateTime();
$d2 = new DateTime('2006-5-20');

$age = $d2->diff($d1);
echo $age->format('%y');
/*
$jd = 2456693;
$jDate = jdtojewish($jd);
$arrJDate = explode("/", $jDate);
print_r($arrJDate);
if (((7 * $arrJDate[2] + 1) % 19) < 7) 
	echo "leap year";
else 
	echo "not leap year";
 * 
 */
?>
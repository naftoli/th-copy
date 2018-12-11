<?
include("db.php");

$school_id = $_GET['school_id'];

$d = unixtojd();
$day = date("N");
$start = $d;

switch ($day) 
{
	case 1:
		$start += 6;
	break;
		
	case 2:
		$start += 5;
	break;
	
	case 3:
		$start += 4;
	break;
	
	case 4:
		$start += 3;
	break;
	
	case 5:
		$start += 2;
	break;
	
	case 6:
		$start++;
	break;
	
	case 7:
	break;
	
	default:
	break;
}

$sql = "SELECT * ";
$sql = $sql . "FROM reports ";
$sql = $sql . "WHERE report_type='mission_cover_sheet' ";
$sql = $sql . "AND visibility != 'none' ";
$sql = $sql . "AND start_date > " . $start . " ";
$sql = $sql . "ORDER BY start_date DESC";	
$query = mysql_query($sql);
$row_num = 0;
?>

<!--
	<li>
		<a data="0" href="JavaScript:void(0);" class="hiLite">Select A Week Period</a>
	</li>
-->

<? while ($row = mysql_fetch_assoc($query)) : ?>

	<? if ($row_num == 0) : ?>
	<li>
		<a class="hiLite" data="<?=$row['report_id'];?>" id="select_anchor_tag" href="JavaScript:void(0);" onclick="anchor_tag_click(this);"><?=$row['report_name'];?> - <?=jdtogregorian($row['start_date']);?></a>
	</li>	
	<? else : ?>
	<li>
		<a data="<?=$row['report_id'];?>" id="select_anchor_tag" href="JavaScript:void(0);" onclick="anchor_tag_click(this);"><?=$row['report_name'];?> - <?=jdtogregorian($row['start_date']);?></a>
	</li>
	<? endif; ?>
	
	<? $row_num++; ?>
<? endwhile?>

<?php require('header.php'); ?>

<?php
$sqlSelect = "";
$sqlFrom = "";
$sqlJoin = "";
$sqlWhere = "";
$sqlGroupBy = "";
$sqlOrderBy = "";
$sqlLimit = "";

$searchValue = "";

//$sql = $sqlSelect . $sqlFrom . $sqlWhere . $sqlOrderBy . $sqlLimit;
//$lastAuction = mysql_fetch_assoc(mq($sql));

// ********** Get the last Auction ********** //
//$sqlSelect = "SELECT a.auction_id, a.auction_name ";
//$sqlFrom = " FROM auctions AS a ";
//$sqlWhere = " WHERE a.auction_ran = 1 AND a.school_id IS NULL AND a.auction_name <> '' ";
//$sqlOrderBy = " ORDER BY a.auction_date DESC ";
//$sqlLimit = " LIMIT 1 ";
//$sql = $sqlSelect . $sqlFrom . $sqlWhere . $sqlOrderBy . $sqlLimit;
//$lastAuction = mysql_fetch_assoc(mq($sql));
// ********** Get the last Auction ********** //

$changeValue = $_POST['changeValue'];
echo "CHANGE VALUE:" . $changeValue;

if ($changeValue == "auction_id") {
	$sqlSelect = "SELECT auction_id AS itemOne, auction_name AS itemTwo";
	$sqlFrom = " FROM auctions ";
	$sqlWhere = " WHERE auction_ran = 1 AND school_id IS NULL AND auction_name <> '' ";	
}
else if ($changeValue == "prize_id") {
	$sqlSelect = " SELECT prize_id AS itemOne, prize_name AS itemTwo ";
	$sqlFrom = " FROM auctions ";
	$sqlJoin = " JOIN auction_winners USING (auction_id) ";
	$sqlJoin = $sqlJoin . " JOIN prizes_auction USING (prize_id) ";
	$sqlWhere = " WHERE auction_id = " . $_POST['auction_id'];
	$sqlGroupBy = " GROUP BY prize_name ASC ";
}
else if ($changeValue == "user_id") {
	$sqlSelect = " SELECT user_id AS itemOne, CONCAT(first, ' ', last) AS itemTwo ";
	$sqlFrom = " FROM auctions ";
	$sqlJoin = " JOIN auction_winners USING (auction_id) ";
	$sqlWhere = " WHERE auction_id = " . $_POST['auction_id'];
	$sqlGroupBy = " GROUP BY itemTwo ASC ";
}
else if ($changeValue == "grade_id") {
	$sqlSelect = " SELECT CONCAT(users.class_id, ',', users.school_id) AS itemOne, CONCAT(class_grade, ' ', class_sub) AS itemTwo ";
	$sqlFrom = " FROM auctions ";
	$sqlJoin = " JOIN auction_winners USING (auction_id) ";
	$sqlJoin = $sqlJoin . " JOIN users USING (user_id) ";
	$sqlJoin = $sqlJoin . " JOIN classes ON users.school_id=classes.school_id AND users.class_id=classes.class_id ";
	$sqlWhere = " WHERE auction_id = " . $_POST['auction_id'];
	$sqlGroupBy = " GROUP BY itemTwo ASC ";

}
else if ($changeValue == "base_id") {
}

$sql = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sqlGroupBy . $sqlOrderBy . $sqlLimit;
$auctions = mysql_query($sql);
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>

	<head>
		<title><?=T_('Auctions'), ' - ', T_('Tzivos Hashem Management System')?></title>
		<link href="styles_reset.css" rel="stylesheet" type="text/css">
		<link href="styles_kiosk.css" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="jquery.js"></script>
		<script type="text/javascript" src="jquery-ui.js"></script>	

		<script type="text/javascript">	
			function auctionWinners(searchItem, searchValue) {
				alert(searchItem + ":" + searchValue);
			}
		</script>
	</head>

	<body class="lgreen">

		<div id="wrapper">
		
			<div id="header">
			</div>
		
			<div id="main">
				<?php
					while ($auction = mysql_fetch_assoc($auctions)) {
				?>
					<div class="button_small">
						<div><a href="#" onclick="auctionWinners('<?=$changeValue;?>', '<?=$auction["itemOne"];?>');"><?= $auction["itemTwo"];?></a></div>
					</div>
				<?
						//echo $auction["itemOne"] . " " . $auction["itemTwo"];
					}				
				?>
			</div> <!-- main -->
		  
		</div> <!-- wrapper -->
		
		<input type="hidden" name="sql" value="<?=$sql;?>">
		
	</body>

</html>
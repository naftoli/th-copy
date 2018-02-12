<?php 

require('header.php');

function change_auction($auction_id) {
	$echo_string = "";
	
	$echo_string = $echo_string .  '<div class="close" onclick="noChange();"></div>';
	$echo_string = $echo_string .  '<div class="select_overlay">';
	$echo_string = $echo_string .  '<div class="pane_title">Choose an Auction:</div>';
	$echo_string = $echo_string .  '<div class="button_small">';

	$sqlSelect = "SELECT a.auction_id, a.auction_name ";
	$sqlFrom = " FROM auctions AS a ";
	$sqlWhere = " WHERE a.auction_ran = 1 ";
	$sqlWhere = $sqlWhere . " AND a.school_id IS NULL ";
	$sqlWhere = $sqlWhere . " AND a.auction_name <> '' ";
	$sqlWhere = $sqlWhere . " AND a.approved=1  ";
	$sqlWhere = $sqlWhere . " AND a.auction_id <> " . $auction_id . " ";
	$sql = $sqlSelect . $sqlFrom . $sqlWhere;
	$rows = mysql_query($sql);

	while ($row = mysql_fetch_assoc($rows)) {
		$echo_string = $echo_string .  '<div><a onClick="getAuctionWinners(\'auction_id\', ' . $row['auction_id'] . ', \'' . $row['auction_name'] . '\');">' . $row['auction_name'] . '</a></div>';
	}
		
	$echo_string = $echo_string .  '</div>';
	$echo_string = $echo_string .  '</div>';
	
	echo $echo_string;
}

function replace_double_quotes($prize_name) {
	$return_string = "";
	
	for ($cntr = 0; $cntr < strlen($prize_name); $cntr++) {
		
		if (substr($prize_name, $cntr, 1) != '"') {
			$return_string = $return_string . substr($prize_name, $cntr, 1);
		}
		
	}
	
	return $return_string;
}

function change_prize($auction_id) {
	$echo_string = "";
	
	$echo_string = $echo_string . '<div class="close" onclick="noChange();"></div>';
	$echo_string = $echo_string . '<div class="select_overlay">';
	$echo_string = $echo_string . '<div class="pane_title">Choose a Prize:</div>';
	
	$echo_string = $echo_string . '<div class="button_small mini">';
	$echo_string = $echo_string . '<div><a onclick="getAuctionWinners(\'prize_id\',\'All\', \'All\');">All</a></div>';
	$echo_string = $echo_string . '</div>';
	
	$sqlSelect = "SELECT prize_id, prize_name, prize_image_id ";
	$sqlFrom = " FROM auction_winners ";
	$sqlJoin = " JOIN prizes_auction USING (prize_id) ";
	$sqlWhere = " WHERE auction_id = " . $auction_id . " ";
	$sqlGroupBy = " GROUP BY prize_name ";
	$sqlOrderBy = " ORDER BY prize_name ";
	$sql = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sqlGroupBy . $sqlOrderBy;
	$rows = mysql_query($sql);

	while ($row = mysql_fetch_assoc($rows)) {
		$prize_name = replace_double_quotes($row['prize_name']);
		
		$echo_string = $echo_string .  '<div class="prize_item">';	
		$echo_string = $echo_string .  '<div class="prize_item_image">';
		$echo_string = $echo_string .  '<a onClick="getAuctionWinners(\'prize_id\', ' . $row['prize_id'] . ', \'' . $prize_name . '\');">'; // . $row['prize_name'];
		$echo_string = $echo_string .  '<img height="48" width="48" src="/file_view.php?id=' . $row["prize_image_id"] . '"/>';
		$echo_string = $echo_string .  '</a>';
		$echo_string = $echo_string .  '</div>';	
		$echo_string = $echo_string .  '</div>';		
	}
	
	$echo_string = $echo_string .  '</div>';
	
	echo $echo_string;
}

function change_name() {
	$echo_string = "";
	
	$echo_string = $echo_string . '<div class="close" onclick="noChange();"></div>';
	$echo_string = $echo_string . '<div class="select_overlay">';
	$echo_string = $echo_string . '<div class="pane_title">Choose a letter:</div>';
	
	$echo_string = $echo_string . '<div class="button_small mini">';
	$echo_string = $echo_string . '<div><a onclick="getAuctionWinners(\'name\',\'All\', \'All\');">All</a></div>';
	$echo_string = $echo_string . '</div>';
	
	$echo_string = $echo_string . '<div class="button_small mini">';
	
	$alphas = range('A', 'Z');
	
	for ($i = 0; $i <= 25; $i++) { 
		$echo_string = $echo_string . '<div><a onClick="getAuctionWinners(\'name\',\'' . $alphas[$i] . '\', \'\');">' . $alphas[$i] . '</a></div>';
	}
	
	$echo_string = $echo_string . '</div>';
	$echo_string = $echo_string . '</div>';
	
	echo $echo_string;
}

function change_grade($auction_id) {
	$echo_string = "";
	
	$echo_string = $echo_string . '<div class="close" onclick="noChange();"></div>';
	$echo_string = $echo_string . '<div class="select_overlay">';
	$echo_string = $echo_string . '<div class="pane_title">Choose a Grade:</div>';
	
	$echo_string = $echo_string . '<div class="button_small mini">';
	$echo_string = $echo_string . '<div><a onclick="getAuctionWinners(\'class_id\',\'All\', \'All\');">All</a></div>';
	$echo_string = $echo_string . '</div>';
	
	$echo_string = $echo_string . '<div class="button_small">';
	
	$sqlSelect = "SELECT users.class_id, class_grade ";
	$sqlFrom = " FROM auction_winners ";
	$sqlJoin = " JOIN users USING (user_id) ";
	$sqlJoin = $sqlJoin . " LEFT JOIN classes ON users.class_id = classes.class_id ";
	$sqlWhere = " WHERE auction_id = " . $auction_id . " AND class_grade <> '' ";
	$sqlGroupBy = " GROUP BY class_grade ";
	$sqlOrderBy = " ORDER BY class_grade ";
	$sql = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sqlGroupBy . $sqlOrderBy;
	$rows = mysql_query($sql);
	
	while ($row = mysql_fetch_assoc($rows)) {
		//$grade_name = $row['class_grade'] . "-" . $row['class_sub'];
		$grade_name = $row['class_grade'];
		$echo_string = $echo_string . '<div><a onClick="getAuctionWinners(\'class_id\', ' . $row['class_grade'] . ', \'' . $grade_name . '\');">' . $grade_name . '</a></div>';	
	}
	
	$echo_string = $echo_string . '</div>';
	$echo_string = $echo_string . '</div>';
	
	echo $echo_string;
}

function change_base($auction_id) {
	$echo_string = "";
	
	$echo_string = $echo_string .  '<div class="close" onclick="noChange();"></div>';
	$echo_string = $echo_string .  '<div class="select_overlay">';
	$echo_string = $echo_string .  '<div class="pane_title">Choose a Base:</div>';
	
	$echo_string = $echo_string . '<div class="button_small mini">';
	$echo_string = $echo_string . '<div><a onclick="getAuctionWinners(\'school_id\',\'All\', \'All\');">All</a></div>';
	$echo_string = $echo_string . '</div>';
	
	$echo_string = $echo_string .  '<div class="button_small">';

	
	$sqlSelect = "SELECT users.school_id, school_name ";
	$sqlFrom = " FROM auction_winners ";
	$sqlJoin = " JOIN users USING (user_id) ";
	$sqlJoin = $sqlJoin . " LEFT JOIN schools ON users.school_id = schools.school_id ";
	$sqlWhere = " WHERE auction_id = " . $auction_id . " AND school_name <> '' ";
	$sqlGroupBy = " GROUP BY school_name ";
	$sqlOrderBy = " ORDER BY school_name ";
	$sql = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sqlGroupBy . $sqlOrderBy;
	
	$rows = mysql_query($sql);
	
	while ($row = mysql_fetch_assoc($rows)) {
		$echo_string = $echo_string .  '<div class="school"><a onClick="getAuctionWinners(\'school_id\', ' . $row['school_id'] . ', \'' . $row['school_name'] . '\');">' . $row['school_name'] . '</a></div>';	
	}

	$echo_string = $echo_string .  '</div>';
	$echo_string = $echo_string .  '</div>';
	
	echo $echo_string;
}

if ( isset ($_GET['output']) ) 
	$output = $_GET['output'];

if ( isset($_GET['auction_id']) )
	$auction_id = $_GET['auction_id'];
		
switch ($output) {
	case "auction":
		change_auction($auction_id);
	break;
	
	case "prize_id":
		change_prize($auction_id);
	break;
	
	case "name":
		change_name();
	break;
	
	case "grade":
		change_grade($auction_id);
	break;
	
	case "base":
		change_base($auction_id);
	break;
	
	
}
	
?>


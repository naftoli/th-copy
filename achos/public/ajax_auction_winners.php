<?php 
require('header.php');

$auction_id = gri('auction_id', -1);
$page_number = gri('page_number', -1);
$view_gender = gr('view_gender', '');
$prize_id = gri('prize_id', -1);
$name = gr('name', '');
$class_id = gri('class_id', -1);
$school_id = gri('school_id', -1);
$sort_by = gr('sort_by', '');

// ********** FILTERS **********//
$sql_prize_id = "";
if ($prize_id != -1) 
	$sql_prize_id = $sql_prize_id . " AND prize_id=" . $prize_id . " ";

$sql_name = "";
if ($name != "") 
	$sql_name = $sql_name . " AND first LIKE '" . $name . "%'";

$sql_class_id = "";
if ($class_id != -1) 
	$sql_class_id = $sql_class_id . " AND class_grade='" . $class_id . "' ";

$sql_school_id = "";
if ($school_id != -1) 
	$sql_school_id = $sql_school_id . " AND users.school_id=" . $school_id . " ";
// ********** FILTERS **********//

// ********** SORT ********** //
$sqlOrderBy = "";
if ($sort_by != "") 
	$sqlOrderBy = " ORDER BY " . $sort_by . " ";
// ********** SORT ********** //

// ********** QUERY ********** //
$school_found = "true";

// ***** Try and find the wiiners from the users school, if there are none then find all the winners *****//
$sqlSelect = "SELECT user_id, users.gender, prize_name, prize_points, prize_image_id, user_photo_id, first, last, school_logo_id, school_number, school_name, class_grade, class_sub "; // rank_name, 
$sqlFrom = " FROM auction_winners ";
$sqlJoin = " JOIN users USING (user_id) ";
$sqlJoin = $sqlJoin . " JOIN prizes_auction USING (prize_id) ";
$sqlJoin = $sqlJoin . " JOIN auctions AS a USING (auction_id) ";
$sqlJoin = $sqlJoin . " LEFT JOIN schools ON users.school_id = schools.school_id ";
$sqlJoin = $sqlJoin . " LEFT JOIN classes USING (class_id) ";
$sqlWhere = " WHERE auction_id = " . $auction_id . " ";
$sql = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sql_prize_id . $sql_name . $sql_class_id . $sql_school_id . $sqlOrderBy;
$result = mysql_query($sql);
$numberOfRows = mysql_num_rows($result);

//***** Get all winners if there are none from the users school ***** //
if ($numberOfRows == 0) {
	$school_found = "false";
	
	$sqlSelect = "SELECT user_id, users.gender, prize_name, prize_points, prize_image_id, user_photo_id, first, last, school_logo_id, school_number, school_name, class_grade, class_sub "; // rank_name, 
	$sqlFrom = " FROM auction_winners ";
	$sqlJoin = " JOIN users USING (user_id) ";
	$sqlJoin = $sqlJoin . " JOIN prizes_auction USING (prize_id) ";
	$sqlJoin = $sqlJoin . " JOIN auctions AS a USING (auction_id) ";
	$sqlJoin = $sqlJoin . " LEFT JOIN schools ON users.school_id = schools.school_id ";
	$sqlJoin = $sqlJoin . " LEFT JOIN classes USING (class_id) ";
	$sqlWhere = " WHERE auction_id = " . $auction_id . " ";
	$sql = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sql_prize_id . $sql_name . $sql_class_id . $sqlOrderBy;
	$result = mysql_query($sql);
	$numberOfRows = mysql_num_rows($result);
}
// ********** QUERY ********** //

$winnersPerPage = 8;
$numberOfPages = ceil($numberOfRows / $winnersPerPage);
$firstRow = (($page_number - 1) * $winnersPerPage) + 1;
$lastRow = $firstRow + $winnersPerPage;

$lastPage = $lastRow - 1;

function get_rank_name($user_id) {
	$rank_name = "";
	
	$sql = "SELECT rank_name FROM rank_marks JOIN ranks USING (rank_ord) WHERE user_id=" . $user_id . "";
	$rows = mysql_query($sql);
	$row_num = 0;
	while ($row = mysql_fetch_assoc($rows)) {
		$rank_name = $row['rank_name'];
	}
	
	return $rank_name;
}

$infoString = $page_number . ";" . $numberOfPages . ";" . $firstRow . ";" . ($lastRow - 1) . ";" . $numberOfRows . ";" . $school_found  . ":";
?>
<?=$infoString;?>						

<div class="table_box">
								
	<table>
										
		<?php
		
		$rowNumber = 0;
		while ($row = mysql_fetch_assoc($result)) {
			$rowNumber++;
												
			$first_name = $row["first"];
			$pos = strpos($first_name, " ");
			if ($pos !== false) 
				$first_name = substr($first_name, 0, $pos) . " " . substr($first_name, $pos + 1, 1) . ".";
			
			$rank_name = get_rank_name($row['user_id']);
			
			if ($rowNumber >= $firstRow && $rowNumber < $lastRow) {
		?>											
			<tr>
				<td class="cell_prize">
					<img style="height:48; width:48;" src="/file_view.php?id=<?=$row["prize_image_id"];?>"/>
				</td>
												
				<td class="cell_prize_info" style="color:#FFFFFF;">	
					<!--<div class="small_info">
						<?//=$row["prize_points"];?> Miles
					</div>-->
					<?=$row["prize_name"];?>
				</td>												
												
				<td class="cell_member">
					<? if ($view_gender == $row['gender']) { ?>
						<img style="height:48; width:48;" src="/file_view.php?id=<?=$row["user_photo_id"];?>"/>
					<? } ?>
				</td>												
												
				<td class="cell_info" style="color:#FFFFFF;">													
					<div class="small_info">
						<?=$rank_name;?> - Grade: <?=$row['class_grade']?>-<?=$row['class_sub']?>
					</div>
													
					<?=$first_name;?> <span><?=$row["last"];?></span>
				</td>
												
				<td class="cell_school">
					<img style="height:48; width:48;" src="/file_view.php?id=<?=$row["school_logo_id"];?>"/>
				</td>
												
				<td class="cell_school_info" style="color:#FFFFFF;">
					<div class="small_info">
						<?=$row["school_name"];?>
					</div>
				</td>
													
			</tr>
											
		<?php
				} // End Of If 
												
			} // End Of While
		?>
		
	</table>
									
</div> <!-- table_box -->

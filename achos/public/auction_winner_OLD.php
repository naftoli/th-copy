<?php require('header.php'); ?>

<?php
require('calendar.php');
require_once('file_save.php');

//$orderBy = "";
//if (!is_null($_GET['orderBy'])) {
//	$orderBy = " ORDER BY " . $_GET['orderBy'] . " ";
//} 

$user_row = mysql_fetch_assoc(mq("SELECT user_id, school_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
      team_name, school_name, school_number, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color, user_start_date, kiosk_edit
FROM users
     LEFT JOIN schools USING (school_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN classes USING (school_id, class_id)
     LEFT JOIN teams USING (school_id, team_id)
     LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$user['user_id']} GROUP BY user_id) rank USING (user_id)
     LEFT JOIN ranks USING (rank_ord)
WHERE user_id = {$user['user_id']}
ORDER BY class_grade, class_sub, last, first
"));

$winnersPerPage = 7;
//$pageNumber = 1;
//if (isset($_GET['pageNumber'])) {
//	$pageNumber = $_GET['pageNumber'];
//}

$pageNumber = gri('pageNumber', 1);
$orderBy = gr('orderBy', '');
$sqlOrderBy = "";
if ($orderBy != "") 
	$sqlOrderBy = " ORDER BY " . $orderBy . " ";

$sqlSelect = "SELECT pa.prize_name, pa.prize_image_id, u.user_photo_id, u.first, u.last, s.school_logo_id, s.school_number, s.school_name, r.rank_name, c.class_grade, c.class_sub ";
$sqlFrom = " FROM auctions AS a ";
$sqlJoin = " JOIN auction_winners AS aw ON a.auction_id = aw.auction_id ";
$sqlJoin = $sqlJoin . " JOIN prizes_auction AS pa ON aw.prize_id = pa.prize_id ";
$sqlJoin = $sqlJoin . " JOIN users AS u ON aw.user_id = u.user_id ";
$sqlJoin = $sqlJoin . " JOIN schools AS s ON u.school_id = s.school_id ";
$sqlJoin = $sqlJoin . " JOIN rank_marks AS rm ON u.user_id = rm.user_id ";
$sqlJoin = $sqlJoin . " JOIN ranks AS r ON rm.rank_ord = r.rank_ord ";
$sqlJoin = $sqlJoin . " JOIN classes AS c ON u.school_id = c.school_id AND u.class_id = c.class_id ";
$sqlWhere = ""; //" WHERE a.school_id = {$user_row['school_id']} ";

$sql = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sqlOrderBy . " LIMIT 25 ";

$result = mysql_query($sql);
$numberOfRows = mysql_num_rows($result);
$numberOfPages = ceil($numberOfRows / 8);
$firstRow = (($pageNumber - 1) * $winnersPerPage) + 1;
$lastRow = $firstRow + $winnersPerPage;
$lastPage = $lastRow - 1;

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Auctions'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="styles_reset.css" rel="stylesheet" type="text/css">
		<LINK href="styles_kiosk.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="jquery.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="jquery-ui.js"></SCRIPT>
		
		<script type="text/javascript">
			function getWinners(button) {
				var pageNumber = parseInt(document.getElementById("pageNumber").value);
				
				if (button == "prevPage") {
					pageNumber = pageNumber - 1;
					document.getElementById("pageNumber").value = pageNumber;
					document.forms["auctionWinner"].submit();
				}
				else if (button == "nextPage") {
					pageNumber = pageNumber + 1;
					document.getElementById("pageNumber").value = pageNumber;
					document.forms["auctionWinner"].submit();
				}
				else {
					document.getElementById("orderBy").value = button;
					document.getElementById("pageNumber").value = 1;
					document.forms["auctionWinner"].submit();					
				}
			}
		</script>
		
	</HEAD>

	<body class="lgreen">
	
		<div id="wrapper">
		
			<div id="header">
			
				<div class="org">
				
					<div class="nav">
						<ul>
							<li class="icon_back">
								<a onclick="javascript:history.go(-1); return false" href="#">Back</a>
							</li>						
							<li class="icon_home">
								<a href="kiosk.php"><?=T_('Home')?></a>
							</li>
							<li class="icon_logout">
								<a href="logout.php?n=kiosk.php"><?=T_('Logout')?></a>
							</li>
						</ul>
					</div> <!-- nav -->
					
					<div class="org_photo">
						<?=!is_null($user_row['school_logo_kiosk_id']) ? linkImgFile($user_row['school_logo_kiosk_id'], null, 100) : (!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id'], null, 100) : '')?>
					</div> <!-- org_photo -->
					
					<?=T_('Base')?>: #<?=$user_row['school_number']?><BR>
					<?=es($user_row['school_name'])?><BR>
					<?=es($user_row['rank_name'])?> <?=es($user_row['first'])?> <?=es($user_row['last'])?> <!--(<?=es($user_row['username'])?>)--> <?=es($user_row['first_he'])?> <?=es($user_row['last_he'])?>
					
				</div> <!-- org -->
				
				<noscript>
					<p class="js_alert">
						Notice: You have javascript disabled.<br>Some parts of the site will not function without javascript.
					</p>
				</noscript>
				
			</div> <!-- header -->

			
			<div id="main">
			
				<div id="page_title">
					<?=T_('Auction - Winners')?>
				</div> <!-- page_title -->

				<div class="winner_boxes">
			
					<div class="one_column">
				
						<div class="pane_title">
							Showing
						</div>

						<div class="select">
							<div class="button_small mini">
								<div>
									<a href="auction_data.php?output=auction">Change</a>
								</div>
							</div>
							
							<div class="title">
								Auction:
							</div>
							
							<div id="filter_auction" class="option">
								11 Shvat
							</div>
							
							<br class="clear" />
						</div> <!-- select -->
					
						<div class="select">
							<div class="button_small mini">
								<div>
									<a href="auction_data.php?output=prize">Change</a>
								</div>
							</div>
							
							<div class="title">
								Prize:
							</div>
							
							<div id="filter_prize" class="option">
								All
							</div>

							<br class="clear" />
						</div> <!-- select -->
						
						<div class="select">
							<div class="button_small mini">
								<div>
									<a href="auction_data.php?output=name">Change</a>
								</div>
							</div>
							
							<div class="title">
								Name:
							</div>
							
							<div id="filter_name" class="option">
								All
							</div>

							<br class="clear" />
						</div> <!-- select -->
					
						<div class="select">
							<div class="button_small mini">
								<div><a href="auction_data.php?output=grade">Change</a></div>
							</div>
							<div class="title">Grade:</div>
							<div id="filter_grade" class="option">All</div>

							<br class="clear" />
						</div> <!-- select -->
					
						<div class="select">
							<div class="button_small mini">
								<div><a href="auction_data.php?output=base">Change</a></div>
							</div>
							<div class="title">Base #:</div>
							<div id="filter_base" class="option">All</div>

							<br class="clear" />
						</div> <!-- select -->
					
					</div> <!-- one_column -->
				
					<div class="two_column">
					
						<div class="winner_display">
						
							<div class="title">
								&nbsp;
							</div>
							
							<div class="button_bar">
							
								<form id="auctionWinner" action="auction_winner.php" method="post">
								
									<div class="sort_links button_small mini">							
										<div>
											<a href="#" onclick="javascript:getWinners('prize_name');"><span class="small">Sort by<br/></span> Prize</a>
										</div>

										<div>
											<a href="#" onclick="javascript:getWinners('first, last ');"><span class="small">Sort by<br/></span> Name</a>
										</div>
										
										<div>
											<a href="#" onclick="javascript:getWinners('school_name');"><span class="small">Sort by<br/></span> School</a>
										</div>
									</div> <!-- sort_links button_small mini -->
									
									<div id="pager" class="pager button_small mini"> 
										<span class="table_info">
											<span class="pagedisplay">Page <?=$pageNumber?> of <?=$numberOfPages?><br/></span> 
											<span class="table_showing">Showing <?=$firstRow;?>-<?=($lastRow - 1);?> of <?=$numberOfRows;?></span> 
										</span>
											
										<div name="prevDiv">
											<?php if ($pageNumber > 1): ?>
												<a href="#" onclick="javascript:getWinners('prevPage');"><</a>
											<? endif; ?>											
										</div>
											
										<div name="nextDiv">
											<?php if ($pageNumber < $numberOfPages): ?>
												<a href="#" onclick="javascript:getWinners('nextPage');">></a>
											<? endif; ?>
										</div>
										
									</div> <!-- pager button_small mini -->
								
									<input type="hidden" name="orderBy" id="orderBy" value="<?=$orderBy?>">
									<input type="hidden" name="pageNumber" id="pageNumber" value="<?=$pageNumber?>">
									<input type="hidden" name="numberOfPages" id="numberOfPages" value="<?=$numberOfPages?>">
									
								</form>
								
								<br class="clear" />
								
							</div> <!-- button_bar -->
							
							<!-- ********** WINNERS ********** -->
							<div class="winner_results">
							
								<div class="table_box">
								
									<table>
										<?php
											$rowNumber = 0;
											while ($row = mysql_fetch_assoc($result)) {
												$rowNumber++;
												
												if ($rowNumber >= $firstRow && $rowNumber < $lastRow) {
												
										?>											
											<tr>
												<td class="cell_prize">
													<img height="48" src="/file_view.php?id=<?=$row["prize_image_id"];?>"/>
												</td>
												
												<td class="cell_prize_info">
													<?=$row["prize_name"];?>
												</td>												
												
												<td class="cell_member">
													<img height="48" src="/file_view.php?id=<?=$row["user_photo_id"];?>"/>
												</td>												
												
												<td class="cell_info">
													<?=$row["first"];?> <span><?=$row["last"];?></span>
													<div class="small_info">Rank: <?=$row["rank_name"];?> - Grade: <?=$row['class_grade']?><?=$row['class_sub']?></div>
												</td>
												
												<td class="cell_school">
													<img height="48" src="/file_view.php?id=<?=$row["school_logo_id"];?>"/>
												</td>
												
												<td class="cell_school_info">
													<div class="small_info">Base: #<?=$row["school_number"];?></div>
													<div class="small_info"><?=$row["school_name"];?></div>
												</td>
												
											</tr>
											
										<?php
												} // End Of If 
												
											} // End Of While
										?>
									</table>
									
									
								</div> <!-- table_box -->
								
							</div> <!-- winner_results -->
							<!-- ********** WINNERS ********** -->
							
						</div> <!-- winner_display -->

					</div> <!-- two_column -->
				
					<div class="clear">
					</div>
				
				</div> <!-- winner_boxes -->
			
			</div> <!-- main -->
			
			<div id="footer">
			
				<div class="footer_logo">
				</div> <!-- footer_logo -->
				
				<div class="footer_logout">
				</div> <!-- footer_logout -->
				
			</div> <!-- footer -->

		</div> <!-- wrapper -->

		<input type="hidden" name="sql" value="<?=$sql;?>">
	</body>
	
</html>

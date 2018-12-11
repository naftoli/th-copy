<?php 
//include("kiosk/includes/header2.php");
require('header.php');
require('calendar.php');
require_once('file_save.php');

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

// ********** Get the last Auction ********** //
$sqlSelect = "SELECT a.auction_id, a.auction_name ";
$sqlFrom = " FROM auctions AS a ";
$sqlWhere = " WHERE a.auction_ran = 1 AND a.school_id IS NULL AND a.auction_name <> '' ";
$sqlOrderBy = " ORDER BY a.auction_date DESC ";
$sqlLimit = " LIMIT 1 ";
$sql = $sqlSelect . $sqlFrom . $sqlWhere . $sqlOrderBy . $sqlLimit;
$last_auction = mysql_fetch_assoc(mq($sql));
$auction_id = $last_auction['auction_id'];
$auction_name = $last_auction['auction_name'];
// ********** Get the last Auction ********** //
?>

<script src="http://cdn.jquerytools.org/1.1.2/jquery.tools.min.js"></script>
<script src="scripts/jquery.tablesorter.min.js"></script>
<script src="scripts/jquery.tablesorter.pager.min.js"></script>

<html>

	<head>
	
		<TITLE>Auctions - Tzivos Hashem Management System</TITLE>
		<LINK href="styles_reset.css" rel="stylesheet" type="text/css">
		<LINK href="styles_kiosk.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="jquery.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="jquery-ui.js"></SCRIPT>
	
		<script type="text/javascript">				
			function getWinners() {	
				var url = "ajax_auction_winners.php?auction_id=" + document.getElementById("auction_id").value;;
								
				url = url + "&page_number=" + document.getElementById("page_number").value;
				
				if (document.getElementById("filter").value != "") {
					url = url + "&filter=" + document.getElementById("filter").value;
					url = url + "&filter_value=" + document.getElementById("filter_value").value;
				}
					
				if (document.getElementById("sort_by").value != "") 
					url = url + "&sort_by=" + document.getElementById("sort_by").value;
					
				var http = getHTTPObject();
				http.open("GET", url, true);
										
				var winners = "";
				
				http.onreadystatechange = function() {
						
					if (http.readyState == 4 && http.status == 200) {
						winners = http.responseText;
						
						var colonLocation = winners.indexOf(":")
						var info = winners.substr(0, colonLocation);
						var infoArray = info.split(";");
						var page_number = parseInt(infoArray[0]);
						var number_of_pages = parseInt(infoArray[1]);
						var first_row = parseInt(infoArray[2]);
						var last_row = parseInt(infoArray[3]);
						var number_of_rows = parseInt(infoArray[4]);

						if (last_row > number_of_rows)
							document.getElementById("table_showing").innerHTML = "Showing " + first_row + "-" + number_of_rows + " of " + number_of_rows;
						else
							document.getElementById("table_showing").innerHTML = "Showing " + first_row + "-" + last_row + " of " + number_of_rows;

						var innerHTML = winners.substr(colonLocation + 1, (winners.length - colonLocation));						
						document.getElementById("winner_results").innerHTML = innerHTML;
						
						document.getElementById("pageDisplay").innerHTML = "Page " + page_number + " of " + number_of_pages + "<br/>";

						
						document.getElementById("number_of_pages").value = number_of_pages;
						
						if (page_number < 2) 
							document.getElementById("prevPage").disabled = true;
						else
							document.getElementById("prevPage").disabled = false;
								
						if (page_number >= number_of_pages) 
							document.getElementById("nextPage").disabled = true;
						else
							document.getElementById("nextPage").disabled = false;
							
								
					} // if	(http.readyState == 4 && http.status == 200) {
							
				} // http.onreadystatechange
							
				http.send(null);
				
			}

			function changeShowing(output) {
				var url = "auction_data.php?output=" + output + "&auction_id=" + document.getElementById("auction_id").value;
				
				var http = getHTTPObject();
				http.open("GET", url, true);
				
				http.onreadystatechange = function() {
						
					if (http.readyState == 4 && http.status == 200) {
						var page_width = window.screen.width;
						var left = (page_width - 600) / 2;

						document.getElementById("overlay").style.display = "block";
						document.getElementById("overlay").style.top = "181.08px";
						document.getElementById("overlay").style.left = left + "px";
						document.getElementById("overlay").style.position = "absolute";
						document.getElementById("overlay").style.zIndex = "10000";
						
						document.getElementById("overlay").innerHTML = http.responseText;
											
						$('#wrapper').fadeTo("slow", 0.3);						
					} 
							
				} 
							
				http.send(null);				
				
			} 
			
			function getHTTPObject() {
				var xmlhttp;

				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				}
				else if (window.ActiveXObject){ 
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
					
					if (!xmlhttp) {
						xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
					}
				}
				
				return xmlhttp; 
			} // getHTTPObject
			
			function getAuctionWinners(filter, value, filter_name) {
				document.getElementById("overlay").style.display = "none";
				$('#wrapper').fadeTo("slow", 1.0);
				
				if (value != "All") {
				
					document.getElementById("filter").value = filter;
					document.getElementById("filter_value").value = value;
					
					document.getElementById("filter_prize").innerHTML = "All";	
					document.getElementById("filter_name").innerHTML = "All";	
					document.getElementById("filter_grade").innerHTML = "All";	
					document.getElementById("filter_base").innerHTML = "All";	
					
					if (filter == "auction_id") {
						document.getElementById("auction_id").value = value;
						document.getElementById("filter_auction").innerHTML = filter_name;					
					}
					else if (filter == "prize_id") {
						document.getElementById("filter_prize").innerHTML = filter_name;					
					}				
					else if (filter == "name") {
						document.getElementById("filter_name").innerHTML = value;					
					}				
					else if (filter == "class_id") {
						document.getElementById("filter_grade").innerHTML = filter_name;					
					}				
					else if (filter == "school_id") {
						document.getElementById("filter_base").innerHTML = filter_name;					
					}				

				}
				else {
					if (filter == "prize_id") 
						document.getElementById("filter_prize").innerHTML = "All";					
					else if (filter == "name") 
						document.getElementById("filter_name").innerHTML = "All";					
					else if (filter == "class_id") 
						document.getElementById("filter_grade").innerHTML = "All";					
					else if (filter == "school_id") 
						document.getElementById("filter_base").innerHTML = "All";					
				
					document.getElementById("filter").value = "";
					document.getElementById("filter_value").value = "";
				}
				
				document.getElementById("page_number").value = 1;
				
				getWinners();
			}
			
			function noChange() {
				document.getElementById("overlay").style.display = "none";
				$('#wrapper').fadeTo("slow", 1.0);			
			}
			
			
			function getNextPage() {
				var number_of_pages = parseInt(document.getElementById("number_of_pages").value);
				
				if (number_of_pages > 0) {
					var page_number = parseInt(document.getElementById("page_number").value);
					
					if (page_number < number_of_pages) {
						document.getElementById("page_number").value = page_number + 1;
						getWinners();
					}
				}				
			}
			
			function getPrevPage() {
				if (parseInt(document.getElementById("page_number").value) > 1) {
					document.getElementById("page_number").value = parseInt(document.getElementById("page_number").value) - 1;
					getWinners();
				}
			}

			function sortWinners(sort_item) {
				document.getElementById("sort_by").value = sort_item;
				document.getElementById("page_number").value = "1";
				getWinners();
			}
		</script>

	</head>


	<body class="lgreen" onload="getWinners('');">

		<input type="hidden" name="auction_id" id="auction_id" value="<?=$auction_id;?>">
		<input type="hidden" name="filter" id="filter" value="">
		<input type="hidden" name="filter_value" id="filter_value" value="">
		<input type="hidden" name="page_number" id="page_number" value="1">
		<input type="hidden" name="number_of_pages" id="number_of_pages" value="0">
		<input type="hidden" name="sort_by" id="sort_by" value="">
	
		<div id="wrapper">
	
			<div id="header">
				<?php include("kiosk/includes/topbar.php"); ?>
			</div>
		
			<div id="main">
		
				<div id="page_title">
					Auction - Winners
				</div>
			
				<div class="winner_boxes">
				
					<div class="one_column">
					
						<div class="pane_title">
							Showing
						</div>
						
						<div class="select">
						
							<div class="button_small mini">
								<div>
									<a href="#" onclick="changeShowing('auction');">Change</a>
								</div>
							</div> <!-- button_small -->
							
							<div class="title">
								Auction:
							</div>
							
							<div id="filter_auction" class="option">
								<?=$auction_name;?>
							</div>
							
							<br class="clear" />
							
						</div> <!-- select -->
						
						<div class="select">
						
							<div class="button_small mini">
								<div>
									<a href="#" onclick="changeShowing('prize_id');">Change</a>
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
									<a href="#" onclick="changeShowing('name');">Change</a>
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
								<div>
									<a href="#" onclick="changeShowing('grade');">Change</a>
								</div>							
							</div>
							
							<div class="title">
								Grade:
							</div>
							
							<div id="filter_grade" class="option">
								All
							</div>
							
							<br class="clear" />
							
						</div> <!-- select -->
						
						<div class="select">
							<div class="button_small mini">							
								<div>
									<a href="#" onclick="changeShowing('base');">Change</a>
								</div>							
							</div>
							
							<div class="title">
								Base #:
							</div>
							
							<div id="filter_base" class="option">
								All
							</div>
							
							<br class="clear" />
							
						</div> <!-- select -->
						
					</div> <!-- one_column -->
				
					<div class="two_column">
					
						<div class="winner_display">
							<div class="title">
								&nbsp;
							</div>
							
							<div class="button_bar">
							
								<div class="sort_links button_small mini">
								
									<div>
										<a href="#" onclick="sortWinners('prize_name');">
											<span class="small">Sort by<br/></span> Prize
										</a>
									</div>
									
									<div class="cssHide">
										<a href="#">1</a>
									</div>
									
									<div class="cssHide">
										<a href="#">1</a>
									</div>
								
									<div>
										<a href="#" onclick="sortWinners('first, last');">
											<span class="small">Sort by<br/></span> Name
										</a>
									</div>
									
									<div class="cssHide">
										<a href="#">1</a>
									</div>
									
									<div>
										<a href="#" onclick="sortWinners('school_name');">
											<span class="small">Sort by<br/></span> School
										</a>
									</div>
									
								</div> <!-- sort_links -->
								
								<div id="pager" class="pager button_small mini"> 
							
									<!-- <form> -->
									
									<span class="table_info">
										<span class="pagedisplay" id="pageDisplay"></span> 
										<span class="table_showing" id="table_showing"></span> 
									</span>
									
									<div>
										<a href="#" class="prev" onclick="getPrevPage();"><</a>
									</div>
									
									<div>
										<a href="#" class="next" onclick="getNextPage();">></a>
									</div>
									
									<input type="hidden" class="pagesize" value="8"/> 
								
									<!-- </form> -->
								
								</div> <!-- pager -->
								
								<br class="clear" />
								
							</div> <!-- button_bar -->
							
							<div class="winner_results" id="winner_results">
							</div>
							
						</div> <!-- winner_display -->
						
					</div> <!-- two_column -->
					
					<div class="clear">
					</div>
					
				</div> <!-- winner_boxes -->
				
			</div> <!-- main -->
		
			<div id="footer">
				<?php include("kiosk/includes/bottombar.php"); ?>
			</div>
			
		</div>
	
		<div class="overlay" id="overlay"> 
			<div class="contentWrap"></div> 
		</div>	
	
	</body>

	<?php include("kiosk/includes/footer.php"); ?>

</html>

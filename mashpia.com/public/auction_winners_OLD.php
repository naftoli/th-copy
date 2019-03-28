<?php require('header.php'); ?>

<?php
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
$lastAuction = mysql_fetch_assoc(mq($sql));
// ********** Get the last Auction ********** //

// ********** Get all the auctions ********** //
$sqlSelect = "SELECT a.auction_id, a.auction_name ";
$sqlFrom = " FROM auctions AS a ";
$sqlWhere = " WHERE a.auction_ran = 1 AND a.school_id IS NULL AND a.auction_name <> '' ";
$sqlOrderBy = " ORDER BY a.auction_date DESC ";
$sql = $sqlSelect . $sqlFrom . $sqlWhere . $sqlOrderBy;
$rows = mysql_query($sql);
while ($row = mysql_fetch_assoc($rows)) {
	echo "<input type='hidden' name='AUCTION ID' value='" . $row['auction_id'] . "'>\n";
}
// ********** Get all the auctions ********** //

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
				//var url = "ajax_auction_winners.php?auctionId=" + document.getElementById("auction_id").value;
				var url = "http://mashpia.com/ajax_auction_winners.php?auctionId=" + document.getElementById("auction_id").value;
					
				var http = getHTTPObject();
				http.open("GET", url, true);
										
				var winners = "";
				
				http.onreadystatechange = function() {
						
					if (http.readyState == 4 && http.status == 200) {
						winners = http.responseText;
						
						var colonLocation = winners.indexOf(":")
						var info = winners.substr(0, colonLocation);
						var infoArray = info.split(";");
						
						var innerHTML = winners.substr(colonLocation + 1, (winners.length - colonLocation));
								
						document.getElementById("winner_results").innerHTML = innerHTML;						
						document.getElementById("pageDisplay").innerHTML = "Page " + infoArray[0] + " of " + infoArray[1] + "<br/>";
						document.getElementById("showingDisplay").innerHTML = "Showing " + infoArray[2] + "-" + infoArray[3] + " of " + infoArray[4];
							
						if (infoArray[0] < 2) 
							document.getElementById("prevPage").disabled = true;
						else
							document.getElementById("prevPage").disabled = false;
								
						if (infoArray[0] >= infoArray[5]) 
							document.getElementById("nextPage").disabled = true;
						else
							document.getElementById("nextPage").disabled = false;
							
								
					} // if	(http.readyState == 4 && http.status == 200) {
							
				} // http.onreadystatechange
							
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
			}


			var aFilter = new Array()
			aFilter = {'auction':'11 Shvat','prize':'All','name':'All','grade':'All','base':'All'};
			var sLink; 
	
			function updateFilter(filter,val) {
				aFilter[filter]=val;
				var key;
				sLink = '';
				for (key in aFilter) {
					sLink += '&' + escape(key) + '=' + escape(aFilter[key]);
				}
				//event.preventDefault();
				var num = $(this).eq();
				var toLoad = "auction_data.php?output=main" + sLink;
				$('.winner_results').fadeOut('fast',loadContent);
				$('.loader').remove();
				$('.two_column').append('<div class="loader">LOADING...</div>');
				$('.loader').fadeIn('normal');
				//window.location.hash = $(this).attr('href').substr(0,$(this).attr('href').length-5);
				function loadContent() {
					$.get(toLoad,'',
						function(data){
							$('.winner_results').html(data);
							showNewWinnerContent()
						});
				}
				function showNewWinnerContent() {
					//$('.winner_display').jScrollPane();
					$("table").tablesorter({
						headers: { 3: {sorter:'name'}}}).tablesorterPager({container: $("#pager"),size:8,positionFixed:false,seperator:' of ',textPage:'Page ',textShowing:'Showing '
					}); 
					$("table").bind("sortStart",function() { 
						$("table").hide();
					}).bind("sortEnd",function() { 
						$("table").fadeIn('normal'); 
					}); 

					$(".sort_links a").click(function() { 
						var sorting = [[$(".sort_links a").index(this),0]];
						$("table").trigger("sorton",[sorting]); 
						return false; 
					}); 
					$('.winner_results').fadeIn('normal',hideLoader());
					for (key in aFilter) {
						$('.select .option#filter_' + escape(key)).text(aFilter[key]);
					}
					
				}
				function hideLoader() {
					$('.loader').fadeOut('fast');
				}
				closeOverlay();
	}
	
	function showNewContent() {
		$('.select_overlay').fadeIn('normal',hideLoader());
		//reinitialiseOverlay();
	}
	function hideLoader() {
		$('.loader').fadeOut('fast');
	}
	function closeOverlay() {
		$(".select a").each(function() {
			if ($(this).overlay()) {
				$(this).overlay().close();
			}
		});
	}
	
	$(document).ready(function(){
		$(".select a").overlay({closeOnClick: false, top:'22%', target: "#overlay", expose:{color:'#000',opacity:0.7,loadSpeed:200},
			onBeforeLoad: function() { 
				var wrap = this.getContent().find(".contentWrap"); 
				$.get(this.getTrigger().attr("href"),'',
					function(data){
						wrap.html(data);
						showNewContent()
					});
				$('.select_overlay').hide();
				$('.loader').remove();
				$('.contentWrap').append('<div class="loader">LOADING...</div>');
				$('.loader').fadeIn('normal'); 
			}
			//,onClose: function() {} 
		}); 
		//$("table").tableSorter(); 
		updateFilter('auction','11 Shvat');
	});
	

		</script>
		
	</head>

	<body class="lgreen" onload="javascript:getWinners();">
	
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
				
						<form name="auction_data" method="POST" action="auction_data.php">
						
							<input type="hidden" name="changeValue" id="changeValue">
							<input type="hidden" name="auction_id" id="auction_id" value="<?=$lastAuction['auction_id'];?>">		
							
							<div class="pane_title">
								Showing
							</div>

							<div class="select">
								<div class="button_small mini">
									<div>
										<!--<a href="#" onClick="document.getElementById('changeValue').value='auction'; $(this).parents('form').get(0).submit(); return false;">Change</a>-->
										<!--<a href="#" onClick="document.getElementById('changeValue').value = 'auction_id'; document.auction_data.submit();">Change</a>-->
										<div><a href="auction_data.php?output=auction">Change</a></div>
									</div>
								</div>
								
								<div class="title">Auction:</div>
								
								<div id="filter_auction" class="option">
									<?=$lastAuction['auction_name'];?>
								</div>
								
								<br class="clear" />
							</div> <!-- select -->
						
							<div class="select">
								<div class="button_small mini">
									<div>
										<a href="#" onClick="document.getElementById('changeValue').value = 'prize_id'; document.auction_data.submit();">Change</a>
									</div>
								</div>
								
								<div class="title">Prize:</div>								
								<div id="filter_prize" class="option">All</div>

								<br class="clear" />
							</div> <!-- select -->
							
							<div class="select">
								<div class="button_small mini">
									<div>
										<a href="#" onClick="document.getElementById('changeValue').value = 'user_id'; document.auction_data.submit();">Change</a>
									</div>
								</div>
								
								<div class="title">Name:</div>								
								<div id="filter_name" class="option">All</div>

								<br class="clear" />
							</div> <!-- select -->
						
							<div class="select">
								<div class="button_small mini">
									<div><a href="#" onClick="document.getElementById('changeValue').value = 'grade_id'; document.auction_data.submit();">Change</a></div>
								</div>
								
								<div class="title">Grade:</div>
								<div id="filter_grade" class="option">All</div>

								<br class="clear" />
							</div> <!-- select -->
						
							<div class="select">
								<div class="button_small mini">
									<div>
										<a href="#" onClick="document.getElementById('changeValue').value = 'base_id'; document.auction_data.submit();">Change</a>
									</div>
								</div>
								<div class="title">Base #:</div>
								<div id="filter_base" class="option">All</div>

								<br class="clear" />
							</div> <!-- select -->
						
						</form>
						
					</div> <!-- one_column -->
				
					<div class="two_column">
					
						<div class="winner_display">
						
							<div class="title">
								&nbsp;
							</div>
							
							<div class="button_bar">
							
								<!--<form id="auctionWinner" action="auction_winner.php" method="post">-->
								
									<div class="sort_links button_small mini">							
										<div>
											<a href="#" onclick="javascript:getWinners('prize_name', '');"><span class="small">Sort by<br/></span> Prize</a>
										</div>

										<div>
											<a href="#" onclick="javascript:getWinners('first, last ', '');"><span class="small">Sort by<br/></span> Name</a>
										</div>
										
										<div>
											<a href="#" onclick="javascript:getWinners('school_name', '');"><span class="small">Sort by<br/></span> School</a>
										</div>
									</div> <!-- sort_links button_small mini -->
									
									<div id="pager" class="pager button_small mini"> 
									
										<!-- Page 1 of 7 -->
										<!--<div name="infoDiv" id="infoDiv">-->
										<span class='table_info'>
											<span class="pagedisplay" id="pageDisplay">
											</span>
											<span class="table_showing" id="showingDisplay">
											</span>
										</span>
										<!--</div>-->
										<!-- Showing 1-7 of 54 -->
										
										<div name="prevDiv">
											<a href="#" id="prevPage" onclick="javascript:getWinners('prevPage', '');"><</a>
										</div>
											
										<div name="nextDiv">
											<a href="#" id="nextPage" onclick="javascript:getWinners('nextPage', '');">></a>
										</div>
										
									</div> <!-- pager button_small mini -->
								
									
								<!--</form>-->
								
								<br class="clear" />
								
							</div> <!-- button_bar -->
							
							<!-- ********** WINNERS ********** -->
							<div class="winner_results" id="winner_results">														
							</div>
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

		<div class="auction_winners_overlay" id="auction_winners_overlay"> 
			<div class="contentWrap"></div>
		</div>

	</body>
	
</html>

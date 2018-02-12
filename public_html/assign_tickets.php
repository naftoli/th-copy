<? 
ini_set('max_execution_time', 300);
//header("Location: under_construction.php");
$admin_auth = array('school','user');

$change_school = false;
if (isset($_POST['change_school'])) {
    $change_school = $_POST['change_school'];
}

require('header.php'); 
$school_id = 0;

if (isset($_POST['school_id']))
    $school_id = $_POST['school_id'];
    
include("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
if ($admin->auth != "super") {
    $admin->get_schools();
    if (count($admin->schools) == 1) {
    	//print_r($admin->schools);
        $school_id = $admin->schools[0]->school_id;
    }
}

$auction_id = 70;
$class_id = 0;
$user_id = 0;
$schools_select = "";
$classes_select = "";
$users_select = "";

//if (isset($_POST['action'])) {
//    $action = $_POST['action'];   
$school_id = $_POST['school_id'];   

if (isset($_POST['class_id'])) 
	$class_id = $_POST['class_id'];
else
	$class_id = 0;
		
if (isset($_POST['user_id'])) 
	$user_id = $_POST['user_id'];
else
	$user_id = 0;
		
get_classes_select($school_id, $class_id);
get_users_select($school_id, $class_id, $user_id);

if ($user_id > 0) {
	$sql = "select * from auctions where auction_id = " . $auction_id;
	$result = mysql_query($sql);
	$auction = mysql_fetch_assoc($result);
	$auction_points = auctionPoints($user_id, $auction);
	
	$sql = "select first, last from users where user_id = " . $user_id;
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$userName = $row['first'] . ' ' . $row['last'];
	
	$userPrizes = array();
	$sql = "select prize_id, quantity from auction_user_prizes where auction_id = " . $auction_id . " and user_id = " . $user_id;
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$userPrizes[$row['prize_id']] = $row['quantity'];
	}
	$up = json_encode($userPrizes);
}
//}

function get_users_select($school_id, $class_id, $user_id) {
    global $users_select;
    
    $sql = "SELECT u.user_id, u.first, u.last, u.class_id, c.class_grade, c.class_sub ";
    $sql = $sql . "FROM users AS u ";
    $sql = $sql . "JOIN classes AS c USING (class_id) ";
    $sql = $sql . "WHERE u.school_id=" . $school_id . " and u.user_registered > 0 ";
    if ($class_id > 0)
        $sql = $sql . "AND class_id=" . $class_id . " ";
    $sql = $sql . "ORDER BY u.class_id, u.last, u.first";
    //echo $sql;
    $query = mysql_query($sql); 
    
    $users_select = "<div class='user_list select_box'>";
    $users_select = $users_select . "<a class='prev button'>";
    $users_select = $users_select . "<span class='icon'></span><span class='label'>Previous Student</span>";
    $users_select = $users_select . "</a>";
    $users_select = $users_select . "<select name='user_id' id='user_id' class='sSelect'>";
    $users_select = $users_select . "<option value='-1'>All students</option>";
        
    while ($row = mysql_fetch_assoc($query)) {
        $grade = $row['class_grade'];
        if ($row['class_sub'] != "")
            $grade = $grade . "-" . $row['class_sub'];
            
        if ($user_id == $row['user_id'])
            $users_select = $users_select . "<option selected value='" . $row['user_id'] . "'>" . $grade . " " . $row['first'] . " " . $row['last'] . "</option>";
        else
            $users_select = $users_select . "<option value='" . $row['user_id'] . "'>" . $grade . " " . $row['first'] . " " . $row['last'] . "</option>";       
    }

    $users_select = $users_select . "</select>";
    $users_select = $users_select . "<a class='next button'>";
    $users_select = $users_select . "<span class='icon'></span><span class='label'>Next Student</span>";
    $users_select = $users_select . "</a>";
    $users_select = $users_select . "</div>";
}

function get_classes_select($school_id, $class_id) {
    global $classes_select;
    
    $sql = "SELECT * FROM classes WHERE school_id=" . $school_id . " and class_era = 0 order by class_grade, class_sub";
    $query = mysql_query($sql);
    
    $classes_select = "<div class='class_list select_box'>";
    $classes_select = $classes_select . "<a class='prev button'>";
    $classes_select = $classes_select . "<span class='icon'></span>";
    $classes_select = $classes_select . "<span class='label'>Previous Platoon</span>";
    $classes_select = $classes_select . "</a>";
    $classes_select = $classes_select . "<select name='class_id'>";
    $classes_select = $classes_select . "<option value='-1'>Entire School</option>";
    
    while ($row = mysql_fetch_assoc($query)) {      
        if ($class_id == $row['class_id']) 
            $classes_select = $classes_select . "<option selected value='" . $row['class_id'] . "'>" . $row['class_grade'] . "-" . $row['class_sub'] . "</option>";
        else
            $classes_select = $classes_select . "<option value='" . $row['class_id'] . "'>" . $row['class_grade'] . "-" . $row['class_sub'] . "</option>";
    }
    
    $classes_select = $classes_select . "</select>";
    $classes_select = $classes_select . "<a class='next button'>";
    $classes_select = $classes_select . "<span class='icon'></span>";
    $classes_select = $classes_select . "<span class='label'>Next Platoon</span>";
    $classes_select = $classes_select . "</a>";
    $classes_select = $classes_select . "</div>";
}


// ***** SCHOOLS ***** //
if ($admin->auth == "super") {
    $schools_sql = "SELECT school_id, school_name FROM schools where school_era is null ORDER BY school_name";
    $schools_query = mysql_query($schools_sql);
}
elseif (count($admin->schools) > 0) 
{
    $schools_sql = "SELECT s.school_id, s.school_name FROM schools AS s JOIN admin_auths AS aa ON (aa.admin_id=" . $admin->admin_id . " AND aa.auth='school' AND aa.id=s.school_id) ORDER BY school_name";
    $schools_query = mysql_query($schools_sql);
}
// ***** SCHOOLS ***** //
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Assign Tickets - Tzivos Hashem Management System</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
		<style>
			.prize {
				float: left;
				font-size: 12px;
				padding: 10px;
				width: 150px;
				height: 153px;
				display: inline-block;
			}
			.prizeImg {
				width: 100px;
			}
			.prizeImg img {
				width: 100px;
				max-height: 120px;
			}
			.prizePoints {
				color: red;
			}
			.prizeName {
				font-size: 14px;
			}
			.prizeQty {
				padding-left: 30px;
			}
			.prizeQty input {
				width: 30px;
			}
			@media print {
				.noPrint {
					display: none;
				}
			}
			@media screen {
				.noShow {
					display: none;
				}
			}
		</style>
    </head>

    <body>
            
        <? include('admin_header.php'); ?>
        
        <div class="body left marking_missions">
                        
            <H1>Assign Tickets for Raffle</H1>
            
            <form name="date_tasks_report" id="date_tasks_report" action="assign_tickets.php" method="post" accept-charset="UTF-8">
                            
                <input type="hidden" name="action" id="action" value="">
				
				<div class="infobox2 marking_list clearfix noprint">
                
                    <div class="school_list select_box">
                        <a class="prev button">
                            <span class="icon"></span>
                            <span class="label"><?=T_('Previous School')?></span>
                        </a>
                    
                        <SELECT name="school_id" id="school_id">
                            <OPTION value="-1">Please select a school</OPTION>
                            <? while ($school = mysql_fetch_assoc($schools_query)) : ?>
                                                        
                                <? if ($school_id == $school['school_id']) : ?>
                                    <OPTION selected value="<?=$school['school_id'];?>"><?=$school['school_name'];?></OPTION>
                                <? else : ?>
                                    <OPTION value="<?=$school['school_id'];?>"><?=$school['school_name'];?></OPTION>
                                <? endif; ?>
                            
                            <? endwhile; ?>
                        </SELECT>
                        
                        
                        <a class="next button">
                            <span class="icon"></span>
                            <span class="label"><?=T_('Next School')?></span>
                        </a>                        
                    </div>
                
                    <div id="class_list_div" name="class_list_div">
                        <?=$classes_select;?>
                    </div>
                    
                    <div id="user_list_div" name="user_list_div">
                        <?=$users_select;?>
                    </div>
                </div>
                
            </form>
				
            <div id="tickets">    
                
            </div>
                
		</div>

<script type="text/javascript">
    $(function(){
        // ***** SCHOOL LIST CHANGE ***** //
        $(".school_list select").sSelect().change(function () {
            document.getElementById('action').value = "get_selects";
            $('#date_tasks_report').submit();
        })
        // ***** SCHOOL LIST CHANGE ***** //
        
        // ***** CLASS LIST CHANGE ***** //
        $(".class_list select").sSelect().change(function () {
            document.getElementById('action').value = "get_selects";
            $('#date_tasks_report').submit();
        })
        // ***** CLASS LIST CHANGE ***** //
        
        // ***** USER LIST CHANGE ***** //
        $(".user_list select").sSelect().change(function () {
			$('#date_tasks_report').submit();
			/*
			var user = $("#user_id").val();
			if (user > 0) {
				showTickets(user);
			}
			*/
            //if (number_of_students > 0)
            //  $(this).closest('form').submit();
        })
        // ***** USER LIST CHANGE ***** //
		
		var user = $("#user_id").val();
		if (user > 0) showTickets();
    });
	
	var auction = <?=$auction_id?>;
	var userName = "<?=$userName?>";
	var userPrizes = <?=$up?$up:0?>;
	var points = <?=$auction_points['cur']?floor($auction_points['cur']):0?>;
	function showTickets() {
		$.post('ajax/getPrizes.php', { auction : auction }, function( success ) {
			var prizes = $.parseJSON( success );
			var html = "<h2>Choose Prizes for Auction</h2>";
			html += "<p class='noPrint' align='center'><input type='submit' value='Print' onclick='window.print()' />";
            html += "<input type='submit' class='save' value='Save' /></p>";
			html += "<p class='noShow'>Tzivos Hashem Hakhel Auction 5776</p>";
			html += "<p>&nbsp;" + points + " total points<br />-<span id='subtract'></span> points in cart<br />";
			html += "&nbsp;<span id ='balance'></span> points available for " + userName + "</p>";
			var total = 0;
			for (var p in prizes) {
				var prize = prizes[p];
				html += "<div class='prize' id='" + prize.prize_id + "'><div class='prizePoints'>" + prize.prize_points + " Point Prize</div>";
				html += "<div class='prizeName'>" + prize.prize_name + "</div>";
				html += "<div class='prizeImg'><img src='file_view.php?id=" + prize.prize_image_id + "' /></div>";
				html += "<div class='prizeQty'><input type='text' ";
				if (userPrizes[prize.prize_id]) {
					html += "value='" + userPrizes[prize.prize_id] + "' ";
					total += (parseInt(prize.prize_points) * parseInt(userPrizes[prize.prize_id]));
				}
				html += "/> Qty</div></div>";
			}
			html += "<div style='clear: both'></div><br />";
			html += "<p align='center'><input type='submit' class='save' value='Save' /></p>";
			
			$("#tickets").empty();
			$("#tickets").append( html );
			
			$("#subtract").text(total);
			$("#balance").text(points - total);
			
			$('.save').click( function() {
				var user = $("#user_id").val();
				var total = 0;
				var cart = [];
				$('.prizeQty input').each( function() {
					var qty = parseInt($(this).val());
					var prize_id = $(this).parent().parent().attr('id');
					var pPoints = parseInt($(this).parent().parent().find('.prizePoints').text());
					if (qty > 0) {
						//var prize_id = $(this).parent().parent().attr('id');
						//alert(prize_id);
						total += (qty * pPoints);
						cart.push({
							'id' : prize_id,
							'qty': qty
						});
					}
				});
				if (total > points) {
					alert('You do not have enough points for this transaction.');
				} else {
					if (cart.length) {
						$.post('ajax/purchaseTickets.php', {
							user : user,
							auction : auction,
							cart : cart
						}, function( success ) {
							if (success == 0) {
								alert('Saved.');
							}
						});
					}
				}
			});
			
			$(".prizeQty input").blur( function() {
				var elem = this;
				if (notEnoughPoints()) {
					alert('You do not have enough points for this.');
					$(elem).focus();
				}
			});
		});
	}
	
	function notEnoughPoints() {
		var total = 0;
		$('.prizeQty input').each( function() {
			var qty = parseInt($(this).val());
			var pPoints = parseInt($(this).parent().parent().find('.prizePoints').text());
			if (qty > 0) {
				total += (qty * pPoints);
			}
		});
		
		$("#subtract").text(total);
		$("#balance").text(points - total);
		
		if (total > points)
			return true;
		else
			return false;
	}
</script>
        	
</body>
</html>
                
<? 
if (isset($_GET['store_purchase_id'])) 
	$store_purchase_id = $_GET['store_purchase_id'];
else 
	$store_purchase_id = 0;
	
if ($store_purchase_id > 0) {
    require('header.php'); 
    require_once('file_save.php');

	//echo "store_purchase_id:" . $store_purchase_id . "<br />";
	
    $user_row = mysql_fetch_assoc(mq("
    SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
           user_city, user_state, user_postal, user_country, user_phone,
           user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
           team_name, school_name, school_number, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color
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

    if(mysql_result(mq("SELECT GET_LOCK('withdraw', 30)"),0) != 1) 
        trigger_error('could not get lock', E_USER_ERROR);
		
    $count = 0;
    do {
		if($count++ > 100000) trigger_error('could not get ID', E_USER_ERROR);
		
		$code_id = mysql_result(mq('SELECT FLOOR(RAND() * 999999999)'),0);
	} while(mysql_result(mq("SELECT COUNT(*) FROM user_withdraw WHERE code_id = $code_id"),0) != 0);
        
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>

	<head>
		<title><?=T_('Print a voucher'), ' - ', T_('Tzivos Hashem Management System')?></title>	
		<LINK href="style.css" rel="stylesheet" type="text/css">		
	</head>
	
	<body>

		<div id="print">
		 
			<div class="print_box receipt">
				<div class="receipt_bsd">??"?</div>
				<div class="receipt_logo"><img width="115" height="70" alt="CTH Logo" src="images/cth_logo_print.gif"/></div>
				<div class="receipt_text">Tzivos Hashem 
					<div class="receipt_title">Store Voucher</div>
					This voucher entitles
					<div class="receipt_name">Seargent Shimmy Weinbaum</div>
					to 
					<div class="receipt_name">2 Cans of Coke!</div>
					<div class="receipt_small">Present this voucher to your base commander<br /> to redeem it for 2 Cans of Coke!</div>
				</div>
				<div class="receipt_barcode">
					<div class="receipt_small">For base commander use only! Do not scan!</div>
					<img height="29" width="233" alt="" src="images/cards/13044664506077890560.png"/>
					<div class="receipt_small">13963453634699857920</div>
				</div>
				<div class="receipt_school">
					<div class="logo"><img height="48" width="48" alt="My Shliach" src="images/schoolLogos/file_view.png"/></div>
					This voucher is only valid in:
					<div class="strong">BASE #613770</div>
					<div class="strong">Cheder Menachem</div>
					Our City, State and Country
				</div>
			</div>
	
		</div>
		
	</body>

</html>
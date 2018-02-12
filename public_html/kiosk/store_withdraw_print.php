<?php 
include("../header.php");
require_once('../file_save.php');

$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
       team_name, school_name, school_number, school_city, school_state, school_country, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color, 
	   camp_name, camp_logo_id, camp_number, camp_city, camp_state, camp_country
FROM users
     LEFT JOIN schools USING (school_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN classes USING (school_id, class_id)
     LEFT JOIN teams USING (school_id, team_id)
     LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$user['user_id']} GROUP BY user_id) rank USING (user_id)
     LEFT JOIN ranks USING (rank_ord)
	 LEFT JOIN camps ON (users.camp_id=camps.camp_id) 
WHERE user_id = {$user['user_id']}
ORDER BY class_grade, class_sub, last, first
"));

if (isset($_COOKIE["camper_id"])) {
	$institution_logo_id = $user_row['camp_logo_id'];
	$institution_name = $user_row['camp_name'];
	$institution_number = $user_row['camp_number'];
	$institution_city = $user_row['camp_city'];
	$institution_state = $user_row['camp_state'];
	$institution_country = $user_row['camp_country'];	
}
else {
	$institution_logo_id = $user_row['school_logo_id'];
	$institution_name = $user_row['school_name'];
	$institution_number = $user_row['school_number'];
	$institution_city = $user_row['school_city'];
	$institution_state = $user_row['school_state'];
	$institution_country = $user_row['school_country'];
}

if (isset($_GET['store_purchase_id'])) {

	if(mysql_result(mq("SELECT GET_LOCK('withdraw', 30)"),0) != 1) 
		trigger_error('could not get lock', E_USER_ERROR);
		
	$count = 0;
	do {
		if ($count++ > 100000) 
			trigger_error('could not get ID', E_USER_ERROR);
			
        $voucher_id = mysql_result(mq('SELECT FLOOR(RAND() * 999999999)'),0);
		
	} while(mysql_result(mq("SELECT COUNT(*) FROM store_purchases WHERE voucher_id = $voucher_id"),0) != 0);

	$store_purchase_id = $_GET['store_purchase_id'];
	mq("UPDATE store_purchases SET voucher_id=" . $voucher_id . " WHERE store_purchase_id=" . $store_purchase_id);
	
	$store_purchase = mysql_fetch_assoc(mq("SELECT * FROM store_purchases JOIN prizes_camp USING (prize_id) WHERE store_purchase_id=" . $store_purchase_id));
	
	if ($store_purchase['prize_quantity'] == 1)
		$description = $store_purchase['prize_quantity'] . " " . $store_purchase['prize_name'] . "!";
	else
		$description = $store_purchase['prize_quantity'] . " " . $store_purchase['prize_name'] . "s!";	
}	

include("includes/slider.php"); 

$title = "Store";
include("includes/header.php");
?>

<script type="text/javascript">
	$(document).ready(function(){	
		 $("a.icon_withdraw").click(function(event){
			   $("div#print div").hide();
				var index = $("a.icon_withdraw").index(this);
			   $("div#print div").eq(index).show();
			   
			   $(this).parent().animate({ opacity: 0}, function() {
					$(this).hide();
					window.print();
					$(".card_withdraw").eq(index).animate({marginTop:'400px'},1000);
			   });
		 });
	});	
	
	function hide()	{
        parent.window.fhide();
    }	
</script>


<body class="receipt" onLoad="<?if(!isset($_COOKIE['kiosk_machine'])):?>parent.vouch.print();<?endif;?>setTimeout('hide()',500);">

    <div id="print">
	
    	<div class="print_box receipt">
		
            <div class="receipt_bsd">בס"ד</div>
			
            <div class="receipt_logo"><img width="115" height="70" alt="CTH Logo" src="images/cth_logo_print.gif"/></div>
			
            <div class="receipt_text">Tzivos Hashem 
                <div class="receipt_title">Store Voucher</div>
                This voucher entitles
                <div class="receipt_name"><?=$user_row['rank_name'];?> <?=$user_row['first'];?> <?=$user_row['last'];?></div>
                to 
                <div class="receipt_name"><?=$description;?></div>
                <div class="receipt_small">Present this voucher to your base commander<br /> to redeem it for <?=$description;?></div>
            </div>
			
            <div class="receipt_barcode">
            	<div class="receipt_small">For base commander use only! Do not scan!</div>
            	<img SRC="../barcode.php/<?=str_pad($voucher_id, 9, '0', STR_PAD_LEFT)?>" alt="">
                <div class="receipt_small"><?=str_pad($voucher_id, 9, '0', STR_PAD_LEFT)?></div>
            </div>
			
            <div class="receipt_text">
                <div class="logo"><img height="48" width="48" alt="" src="../file_view.php?id=<?=$institution_logo_id;?>"/></div>
                This voucher is only valid in:
                <div class="receipt_small">BASE #<?=$institution_number;?></div>
                <div class="receipt_small"><?=$institution_name;?></div>
                <?=$institution_city;?>, <?=$institution_state;?> <?=$institution_country;?>
            </div>
			
        </div>
		
    </div>
	
</body>

<?php include("includes/footer.php"); ?>

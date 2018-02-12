<? 
if(!isset($prnum)) $prnum = -1;

if($prnum==-1)
{
    require('header.php'); 
    require_once('file_save.php');

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

    $chay_elul = chaiElul();

    $withdraw_used_points = mysql_fetch_assoc(mq("SELECT SUM(points) points_total FROM user_withdraw WHERE user_id = {$user['user_id']}"));
    $cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = {$user['user_id']} AND mark_date >= $chay_elul")), 0));
    $left_points = $cur_points - $withdraw_used_points['points_total'];
}
if($left_points >= 50) 
{
    if($prnum==-1)
    {
        if(mysql_result(mq("SELECT GET_LOCK('withdraw', 30)"),0) != 1) 
            trigger_error('could not get lock', E_USER_ERROR);
        $count = 0;
        do 
        {
            if($count++ > 100000) trigger_error('could not get ID', E_USER_ERROR);
            $id = mysql_result(mq('SELECT FLOOR(RAND() * 999999999)'),0);
        } while(mysql_result(mq("SELECT COUNT(*) FROM user_withdraw WHERE code_id = $id"),0) != 0);
        
		mq("INSERT INTO user_withdraw (user_id, code_id, points, jul_print_date) VALUES (" . $user['user_id'] . ", " . $id . ", 50, " . julian_today() . ")");
    }
  
} 
else 
{
  header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/');
}
if($prnum==-1)
{
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
       "http://www.w3.org/TR/html4/strict.dtd">
    <HTML DIR="<?=$dir?>">
    <HEAD>
    <TITLE><?=T_('Print a voucher'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
    <LINK href="kiosk/styles/style.css" rel="stylesheet" type="text/css">
    <script type="text/javascript">
    function hide()
    {
        //alert("hide");
        parent.window.fhide();
    }
    </script>
    </HEAD>
    <BODY class="receipt" onLoad="<?if(!isset($_COOKIE['kiosk_machine'])):?>parent.vouch.print();<?endif;?>setTimeout('hide()',500);">
    <h1>
    <?=sprintf(T_('Congratulations %s'), es($user_row['rank_name'] . ' ' . $user_row['first'] . ' ' . $user_row['last']))?>
    </h1>
    <? 
}
?>
<div class="print_box receipt">
            <div class="receipt_bsd">בס"ד</div>
            <div class="receipt_logo"><img width="115" height="70" alt="CTH Logo" src="kiosk/images/cth_logo_print.gif"/></div>
            <div class="receipt_text">770 Album - Rebbe Picture Packet 
                <div class="receipt_title">Voucher</div>
                Congratulations
                <div class="receipt_name"><?=$user_row['rank_name'].' '.$user_row['first'].' '.$user_row['last']?></div>
                for earning
                <div class="receipt_name">50 Miles!</div>
                <div class="receipt_small">Present this voucher to your base commander<br /> to redeem it for a pack of 5 Rebbe pictures!</div>
            </div>
            <div class="receipt_barcode">
            	<div class="receipt_small">For base commander use only! Do not scan!</div>
            	<IMG SRC="barcode.php/1<?=str_pad($id, 9, '0', STR_PAD_LEFT)?>" alt="">
                <div class="receipt_small">1<?=str_pad($id, 9, '0', STR_PAD_LEFT)?></div>
            </div>
            <div class="receipt_school">
                <div class="logo"><?=!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id'], null, 100) : ''?></div>
                This voucher is only valid in:
                <div class="strong"><?=T_('Base')?>: #<?=$user_row['school_number']?></div>
                <div class="strong"><?=es($user_row['school_name'])?></div>
                <?=es($user_row['school_city'] . ', ' . $user_row['school_state'])?>
            </div>
        </div>
</BODY>
</HTML>

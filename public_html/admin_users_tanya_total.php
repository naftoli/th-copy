<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
$ui_type = 'programs';
require_once('admin_ui.php');
assure_id_school('school_id');
$school_id = gri('school_id', -1);
$action = gr('action');
$totalTanya = gr('totalTanya');
$totalMishna = gr('totalMishna');

if ( empty( $action ) ) {
    if ( empty( $totalTanya ) || empty( $totalMishna ) ) {
        //get school total from db
        $sql = "select total_tanya, total_mishna from tanya_totals where school_id = " . $school_id;
        //echo $sql;
        $result = mysql_query( $sql );
        if ( mysql_num_rows($result) > 0 ) {
            $row = mysql_fetch_assoc($result);
            $totalTanya = $row['total_tanya'];
            $totalMishna = $row['total_mishna'];
        }
    }
}

if(!empty($action)) switch($action) {
  case 'save':    
    //if total entered, save total to db
    if ( empty( $totalTanya ) ) {
        $totalTanya = 0;
    }
    if ( empty( $totalMishna ) ) {
        $totalMishna = 0;
    }		
    $sql = "insert into tanya_totals (school_id, total_tanya, total_mishna) 
            values( $school_id, $totalTanya, $totalMishna ) 
            on duplicate key update total_tanya = $totalTanya, total_mishna = $totalMishna";
    //echo $sql;
    mq($sql);
	header( "Location: admin.php" );
    break;

  default:
    user_error('unknown action', E_USER_ERROR);
    break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_("Tanya Lines Learned") . ' - ' . T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<H1><?=T_('Total Tanya Lines Learned')?></H1>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_users_tanya_total.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<?endif;?>
<?if($school_id == -1):?>
<?=T_('Please select an Institution.')?>
<?else:?>
<DIV class="ui_body">
<DIV class="content">

<DIV class="infobox">
    Please enter lines of Tanya and lines of Mishna learned in honor of Yud Alef Nissan Ayin Gimmel.<br />
</div>
<FORM action="admin_users_tanya_total.php" method="post" accept-charset="UTF-8" name="user_tracks">
    <div>
        Total Tanya in school: <input type="text" name="totalTanya" maxlength="9" size="5" value="<?=$totalTanya?>"><br />  
        Total Mishna in school: <input type="text" name="totalMishna" maxlength="9" size="5" value="<?=$totalMishna?>">
    </div>
<DIV>
<P>
<INPUT type="hidden" name="action" value="save">
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="submit" value="<?=T_('Save')?>">
<INPUT type="reset" value="<?=T_('Undo Changes')?>">
</P>
</DIV>
</FORM>
<? endif; ?>
<BR style="clear: both;">
</DIV>
</DIV>
</DIV>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>

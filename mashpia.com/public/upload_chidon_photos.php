<?php
$admin_auth = array('school');
require('header.php');
require_once('file_save.php');
$ui_type = 'school';
require_once('admin_ui.php');

$action = gr('action');
assure_id_school('school_id');
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
if (isset($_GET['missing'])) {
    $missing = 1;
} else {
    $missing = 0;
}

$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
$usersSql = "select u.user_id, u.first, u.last, u.chidon_pic_5782, c.class_grade, c.class_sub, c.class_teacher 
            from users u 
            join classes c using (class_id) 
            join th_chidon tc using (user_id) 
            where tc.year = 5782 
            and tc.date_paid > 0 
            and u.school_id = " . $school_id;
if ($class_id != -1) $usersSql .= " and class_id = " . $class_id;
if ($missing) $usersSql .= " and u.chidon_pic_5782 is null";
$usersSql .= " order by u.last, u.first";
$edit_result = mq($usersSql);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
<HEAD>
    <TITLE><?=T_('Manage Photos'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
    <LINK href="admin_styles.css" rel="stylesheet" type="text/css"/>
    <style>
        .inline_top{
            display: inline-block;
            vertical-align: top;
        }
    </style>

    <SCRIPT type="text/javascript">
        function delete_photo(checkbox, user_id) {
            if (checkbox.checked) {
                var answer = confirm ("Are you sure that you want to delete this photo?");
                if (answer) {
                    var function_name = "remove_user_photo";
                    var parameters = [user_id];
                    var url = "edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;

                    $.getJSON(url, function(success) {
                        if (success) {
                            $("#td_" + user_id).html("");
                        }
                        else {
                            alert("Photo not removed. Please try again.");
                        }
                    });

                }
            }
        }
    </SCRIPT>
</HEAD>

<BODY>

<? include('admin_header.php'); ?>

<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
    <DIV class="body">
        <DIV class="sub_menu">
            <?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
        </DIV>
        <H1><?=T_('Base Management')?></H1>
        <?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
            <? $school_result = mq('SELECT school_id, school_name FROM schools ' .
                ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ') 
                    and test_school = 0 AND school_era is NULL' :
                    'WHERE test_school = 0 AND school_era is NULL') .
                ' ORDER BY school_name'); ?>
            <FORM action="upload_chidon_photos.php" method="get" accept-charset="UTF-8">
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
                <DIV class="ui_menu">
                    <?ui_menu();?>
                </DIV>
                <DIV class="content">
                    <H2><?=T_('Manage Photos')?></H2>
                    <DIV class="infobox">
                        As we will be making a virtual award ceremony this year, we are relying on great photos to put it together!<br />
                        <br />
                        When taking photos, please pay close attention to the following guidelines.<br />
                        <br />
                        <input type="checkbox" /> Horizontal picture (when using a phone, make sure the phone is on the side, not upright)
                        <br />
                        <input type="checkbox" /> Every child in separate photos
                        <br />
                        <input type="checkbox" /> For girls: hair should be pulled back, neat and tidy
                        <br />
                        <input type="checkbox" /> Smiling and looking at the camera
                        <br />
                        <input type="checkbox" /> Waist up
                        <br />
                        <input type="checkbox" /> Hands should be down
                        <br />
                        <input type="checkbox" /> Chidon sweater 
                        <br />
                        <input type="checkbox" /> No stains on clothing
                        <br />
                        <input type="checkbox" /> Brightly-lit room
                        <br />
                        <input type="checkbox" /> Solid, dark background
                        <br />
                        <input type="checkbox" /> High-quality image
                    </DIV>
                    <DIV class="infobox2">
                        <FORM action="upload_chidon_photos.php" method="get" accept-charset="UTF-8">
                            <P>
                                <INPUT type="hidden" name="school_id" value="<?=$school_id?>">
                                <?=T_('Show only Platoon')?>: <SELECT name="class_id">
                                    <OPTION value="-1">&lt;<?=T_('All')?>&gt;
                                        <?while($class_row = mysql_fetch_assoc($class_result)):?>
                                    <OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
                                    <?endwhile;?>
                                </SELECT>
                                <br />
                                <input type='checkbox' name="missing" /> Show only missing pictures
                                <br />
                                <INPUT class="submit" type="submit" value="<?=T_('Go')?>">
                            </P>
                        </FORM>
                    </DIV>

                    <? if ($edit_result) : ?>
                        <!--<FORM name="delete_form" id="delete_form" action="admin_users_photo.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">-->
                        <DIV>
                            <INPUT type="hidden" name="action" id="action" value="delete">
                            <INPUT type="hidden" name="school_id" id="school_id" value="<?=$school_id?>">
                            <INPUT type="hidden" name="hidden_user_id" id="hidden_user_id" value="">
                            <INPUT type="hidden" name="class_id" id="class_id" value="<?=$class_id?>">

                            <TABLE class="list list_<?=$align_start?>">
                                <THEAD>
                                <TR>
                                    <TH><?=T_('Soldier')?><BR><?=T_('Platoon')?></TH>
                                    <TH><?=T_('New Photo')?></TH>
                                    <TH><?=T_('Existing Photo')?><BR><?=T_('Uploading a new photo will replace the old.')?></TH>
                                </TR>
                                </THEAD>

                                <? $toggle = 0; ?>
                                <? while($row = mysql_fetch_assoc($edit_result)) : ?>
                                    <TR class="<?=($toggle ^= 1) ? 'odd' : 'even'?>">
                                        <TD>
						<SPAN style="font-size: 115%; font-weight: bold;">
							<?=es($row['last']), ', ', es($row['first'])?>
						</SPAN>
                                            <BR>
                                            <BR>
                                            <?=T_('Platoon'), ' ', $row['class_grade'], '-', es($row['class_sub']), ' ', es($row['class_teacher'])?>
                                        </TD>

                                        <TD>
                                            <a href="upload_chidon_photo.php?school_id=<?=$school_id;?>&user_id=<?=$row['user_id'];?>
							<? if ($class_id > 0) echo "&class_id=" . $class_id; ?>
							" class="button">New Photo</a>
                                            <!--<INPUT type="file" name="photo_<?//=$row['user_id']?>" class="file_THIS_CLASS_WAS_NOT_WORKING_PROPERLY">
						<INPUT type="submit" value="<?//=T_('Save')?>">-->
                                        </TD>

                                        <TD name="td_<?=$row['user_id'];?>" id="td_<?=$row['user_id'];?>">
                                            <? if(!is_null($row['chidon_pic_5782'])) : ?>
                                                <LABEL>
                                                    <?=T_('Delete current photo')?>
                                                    <INPUT onclick="delete_photo(this, <?=$row['user_id'];?>);" type="checkbox" name="photo_delete_<?=$row['user_id']?>" class="checkbox" value="1">
                                                </LABEL>
                                                <BR>
                                                <div class="inline_top">
                                                    <img src="/mobile/reg/<?=$row['mobile_pic']?>?time=<?=time()?>" height="80" />
                                                </div>
<!--                                                    <div class="inline_top">-->
<!--                                                        <form action="/tasks/flip_images.php" method="post">-->
<!--                                                            <input type="hidden" name="target" value="/mobile/reg/--><?//=$row['mobile_pic']?><!--"/>-->
<!--                                                            <input type="radio" name="degrees" value="90"/> 90&deg; <br/>-->
<!--                                                            <input type="radio" name="degrees" value="180"/> 180&deg; <br/>-->
<!--                                                            <input type="radio" name="degrees" value="-90"/> -90&deg; <br/>-->
<!--                                                            <input type="hidden" name="redirect" value="--><?//=$_SERVER['REQUEST_URI']?><!--"/>-->
<!--                                                            <input type="submit" name='action' value="flip"/>-->
<!--                                                        </form>-->
<!--                                                    </div>-->
                                            <?endif?>
                                        </TD>
                                    </TR>
                                <?endwhile;?>
                            </TABLE>

                            <!--<P>
				<INPUT type="submit" value="<?//=T_('Save')?>">
			</P>-->

                        </DIV>

                        <!--	</FORM>-->
                    <? endif; ?>

                    <BR style="clear: both;">
                </DIV>
            </DIV>
        <? endif; ?>
    </DIV>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>

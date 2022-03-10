<?php
ini_set('display_errors', 1);
$admin_auth = array('school');
require('header.php');
require_once('file_save.php');
$ui_type = 'school';
require_once('admin_ui.php');
$school_name = "";

if (isset($_POST['action'])) {
    $school_id = $_POST['school_id'];
    $user_id = $_POST['user_id'];

    if (isset($_FILES['user_photo']))  {
        if ($pic = addFileNew($_FILES['user_photo'])) {
            $sql = "update users set chidon_pic_5782 = '" . $pic . "' where user_id = " . $user_id;
            mysql_query($sql);
            $str = "Location: http://" . $_SERVER['DOCUMENT_ROOT'] . "/upload_chidon_photos.php?school_id=" . $school_id;
            if ($_POST['class_id'] > 0) $str .= "&class_id=" . $_POST['class_id'];
            header($str);
            exit;
        } else {
            $message = "Could not update system with new picture.";
        }
    }
}

$school_id = $_GET['school_id'];
$class_id = 0;
if (isset($_GET['class_id'])) $class_id = $_GET['class_id'];
$user_id = $_GET['user_id'];
$sql = "SELECT * FROM users WHERE user_id=" . $user_id;
$query = mysql_query($sql);
$user = mysql_fetch_assoc($query);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
<HEAD>
    <TITLE><?=T_('Manage Photos'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
    <LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>

<BODY>

<? include('admin_header.php'); ?>

<DIV class="ui_<?=$ui_type?> <?=$align_start?>">

    <DIV class="body">

        <DIV class="sub_menu">
            <? if(!empty($message)) : ?>
                <H2><?=$message?></H2>
            <?endif;?>
        </DIV>

        <H1>
            <?=T_('Base Management')?>
        </H1>

        <FORM action="admin_user_photo.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="school_id" value="<?=$school_id;?>">
            <input type="hidden" name="class_id" value="<?=$class_id?>">
            <input type="hidden" name="user_id" value="<?=$user_id;?>">

            <TABLE class="list list_<?=$align_start?>">
                <THEAD>
                <TR>
                    <TH><?=T_('Soldier')?><BR><?=T_('Platoon')?></TH>
                    <TH><?=T_('New Photo')?></TH>
                    <TH><?=T_('Existing Photo')?><BR><?=T_('Uploading a new photo will replace the old.')?></TH>
                </TR>
                </THEAD>

                <TR class="odd">
                    <TD>
								<SPAN style="font-size: 115%; font-weight: bold;">
									<?= $user['first'] ?>, <?= $user['last'] ?>
								</SPAN>
                    </TD>

                    <TD>
                        <INPUT type="file" name="user_photo">
                        <INPUT type="submit" value="<?=T_('Save')?>">
                    </TD>

                    <TD>
                        <? if (! is_null($user['chidon_pic_5782'])) : ?>
                            <img src="/mobile/reg/<?= $user['chidon_pic_5782'] ?>" height="80" />
                        <? endif; ?>
                    </TD>
                </TR>

            </TABLE>

        </FORM>

    </DIV>

</DIV>

<? include('admin_footer.php'); ?>

</BODY>

</HTML>

<?php
ini_set('display_errors', 1);
$admin_auth = array('school');
require('header.php');
require_once('file_save.php');
$ui_type = 'school';
require_once('admin_ui.php');
$school_name = "";

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

function addPhoto($file) {
    // check for errors
    if ($file['error'] > 0) {
        return false;
    }

    // check for file type
    switch ($file['type']) {
        case 'image/jpeg':
        case 'image/png':
        case 'image/gif':
            break;
        default:
            $file['msg'] = "Only JPG, PNG, and GIF files are allowed.";
            return false;
    }

    $file_name = $file['name'];
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/mobile/reg/img/";
    $target_file = $target_dir . basename($file_name);

    // For display purposes, we'll use a different path
    $display_path = "/mobile/reg/img/" . basename($file_name);
    if ($_SERVER['SERVER_NAME'] != 'mashpia.com') {
        $display_path = "img/" . basename($file_name);
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // Resize the image
        switch ($file['type']) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($target_file);
                break;
            case 'image/png':
                $image = imagecreatefrompng($target_file);
                break;
            default:
                return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $new_width = 300;
        $new_height = floor($height * ($new_width / $width));
        $resized_image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($resized_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        if (imagepng($resized_image, $target_file)) {
            return $display_path;
        }
    } else {
        $message = "Could not upload file.";
    }

    return false;
}

if (isset($_POST['action'])) {
    $school_id = $_POST['school_id'];
    $user_id = $_POST['user_id'];

    if (isset($_FILES['user_photo']))  {
        $message = "Could not update system with new picture.";
        if ($pic = addPhoto($_FILES['user_photo'])) {
//        if ($pic = addFileNew($_FILES['user_photo'])) {
            $sql = "update th_chidon set chidon_photo = \"" . $pic . "\" where user_id = " . $user_id . " and year = " . $year;
            if (mysql_query($sql)) {
                $str = "Location: http://mashpia.com/upload_chidon_photos.php?school_id=" . $school_id;
                if ($_POST['class_id'] > 0) $str .= "&class_id=" . $_POST['class_id'];
                if ($_POST['user_id'] > 0) $str .= "&user_id=" . $_POST['user_id'];
                header($str);
                exit;
            }
        } else if (isset($_FILES['user_photo']['msg'])) {
            $message = $_FILES['user_photo']['msg'];
        }
    }
}

$school_id = $_REQUEST['school_id'];
$class_id = 0;
$user_id = $_REQUEST['user_id'];
if (isset($_REQUEST['class_id'])) $class_id = $_REQUEST['class_id'];
$sql = "SELECT u.*, tc.chidon_photo 
        FROM users u 
        JOIN th_chidon tc USING (user_id)
        WHERE user_id = " . $user_id . " 
        AND year = " . $year;
$query = mysql_query($sql);
$user = mysql_fetch_assoc($query);

$chidon_photo = $user['chidon_photo'];
if (! empty($chidon_photo)) {
    if ($chidon_photo && $_SERVER['SERVER_NAME'] != 'mashpia.com') {
        $chidon_photo = "http://mashpia.com/mobile/reg/" . $chidon_photo;
    } else {
        $chidon_photo = "/mobile/reg/" . $chidon_photo;
    }
}
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

        <FORM action="upload_chidon_photo.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
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
                        <? if (! empty($chidon_photo)) : ?>
                            <img src="<?= $chidon_photo ?>" height="80" />
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

<?php
$admin_auth = array('school');
require 'header.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Teachers</title>
<style>
table {
	width: 100%;
}
th, td {
	border: 1px solid black;
	vertical-align: text-top;
	padding: 6px;
    font-size: 12px;
}
/* div#wrapper { width: 1151px; }
#content .col_content { padding: 20px 10px; }
#content .slider, #content { width: 900px; } */
</style>
</head>

<body>
<? include('admin_header.php');?>
<h1>Platoon Teacher Information</h1>
<div class='infobox'>
    <h2>What this page does</h2>
    <p>This page edits the information headquarters will use to contact this platoon and for all E-mail, SMS and WhatsApp notifications.</p>
    <p><strong>If you wish to edit the account ( username/password ) used to access a platoon please click <a href='/teacher_list.php'>here.</a></strong></p>
</div>
<?
$teachers = array();
require_once 'class.adminSchools.php';       
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
foreach ($schools as $id => $school) {
    $sql = "select class_id, class_grade, class_sub, email, cell, class_teacher 
			FROM classes
			where school_id = " . $id . "
			AND class_era = 0
            ORDER BY class_grade, class_sub";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $teachers[$school][$row['class_id']] = $row;
    }   
}

foreach ($teachers as $school => $info) { ?>
    <table>
        <caption><?=$school?></caption>
        <tr>
            <th>Grade</th>
            <th>Sub</th>
            <th>Teacher Name (Mission Sheets)</th>
            <th>Email</th>
            <th>Cell Phone</th>
			<th></th>
        </tr>
        <?
        foreach ($info as $class_id => $row) { ?>
            <tr id="<?=$class_id?>">
                <td>
                    <?=$row['class_grade']?>
                </td>
                <td>
                    <?=$row['class_sub']?>
                </td>
                <td>
                    <input type='text' class='class_teacher' value='<?=$row['class_teacher']?>' />
                </td>
                <td>
                    <input type='email' class='email' value='<?=$row['email']?>' />
                </td>
                <td>
                    <input type='text' size='10' class='cell' value='<?=$row['cell']?>' />
                </td>
                <td>
                    <button class='update' data-class_id="<?=$class_id?>">Update</button>
                </td>
            </tr>
        <?php
		}
        ?>
    </table>
	<p></p>
<? } ?>
</body>
<script>
$( document ).ready( function() {
    $("button.update").click( function( event ) {
        var class_id = event.target.dataset.class_id;
        var tr = $( 'tr#' + class_id );

        var class_teacher = $(tr).find('input.class_teacher').val().trim();
        var email = $(tr).find('input.email').val().trim();
        var cell = $(tr).find('input.cell').val().trim();
        
        $.post('ajax/updatePlatoon.php', {
            class_id : class_id,
            class_teacher : class_teacher,
            email : email,
            cell : cell
        }, function( success ) {
            var response = JSON.parse( success );
            if ( !response.success ) {
                alert( response.error );
            } else {
                alert( 'Updated' );
            }
        });
    });
});
</script>
</html>


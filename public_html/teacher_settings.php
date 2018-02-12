<?
$admin_auth = array('school'); 
require('header.php');

$teachers = array();
require_once 'class.adminSchools.php';       
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
foreach ($schools as $id => $school) {
    $sql = "select class_id, class_grade, class_sub, email, cell
			from classes
			where school_id = " . $id . "
			and class_era = 0
			order by class_grade, class_sub";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $sql2 = "select a.* from admins a 
                join admin_auths aa using (admin_id)
                where aa.id = " . $row['class_id'] . " and aa.auth = 'class'";
        $result2 = mysql_query($sql2);
        $row2 = mysql_fetch_assoc($result2);
        if (mysql_num_rows($result2) == 0) continue;
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $row2['email'] = $row['email'];
        $row2['cell'] = $row['cell'];
		$id = $row2['admin_id'];
		if (!$id) $id = 'class' . $row['class_id'];
        $teachers[$school][$grade] = $row2;
    }    
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Teacher Settings</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style>
        	fieldset {
                border: 1px solid white;
                padding: 10px;
                padding-top: 0px;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
                font-size: 16px;
            }
            legend {
                margin-left: 20px;
                padding: 5px;
                color: purple;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            .middle {
            	text-align: center;
            	margin: auto;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Teacher Settings</h1>
        
        <?php
        foreach ($teachers as $school => $info) {
        ?>
            <table>
                <caption><?=$school?></caption>
                <tr>
                    <th>Teacher</th>
                    <th>Grade</th>
                    <th>Print Acheivement Cards</th>
					<th>Enable Teacher's Store</th>
                </tr>
                <?php
                foreach ($info as $grade => $row) {
                    echo "<tr><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" . $grade . "</td><td>";
                    echo "<input type='checkbox' class='setting' id=" . $row['admin_id'];
                    if ($row['achievement_cards']) echo " checked";
                    echo " />";
					echo " (CHECKED = YES, UNCHECKED = NO)";
                    echo "</td><td><input type='checkbox' class='store' id=" . $row['admin_id'];
					if ($row['store']) echo " checked";
					echo " />";
					echo " (CHECKED = YES, UNCHECKED = NO)";
					echo "</td></tr>";
                }
                ?>
            </table>
			<br />
        <?php } ?>
    </BODY>
    <script>
        $(function() {
            $(".setting").click( function() {
                var id = $(this).attr('id');
                var checked = $(this).is(":checked") ? 1 : 0;
                $.post('ajax/updateTeacherSetting.php', { admin : id, setting : checked, type : 'achievement' }, function(success) {
                    if (parseInt(success) == 0) {
                        alert('updated.');
                    } else {
                        alert('error updating.');
                    }
                });
            });
			$(".store").click( function() {
                var id = $(this).attr('id');
                var checked = $(this).is(":checked") ? 1 : 0;
                $.post('ajax/updateTeacherSetting.php', { admin : id, setting : checked, type : 'store' }, function(success) {
                    if (parseInt(success) == 0) {
                        alert('updated.');
                    } else {
                        alert('error updating.');
                    }
                });
            });
        });
    </script>
</HTML>
        
<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Student List</title>
<style type='text/css'>
tr, th, td {
    border: 1px dashed black;
    padding: 6px;
}
.school {
    width: 200px;
}
.grade {
    width: 50px;
}
</style>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>

<script type="text/javascript">
    $(document).ready( function() {
        $("body").delegate(".school", 'change', function() {
           var child = $(this).closest('tr').find('.user_id').val();
           var school = $(this).val();
           $.get('ajax/changeSchool.php', { sid : school, cid : child });
           
           //clear grades options if exists
           var grade = $(this).closest('tr').find('.grade');
           var grade_id = grade.val();
           if (grade_id) {
               grade.empty();
               grade.attr('disabled', true);
               $.get('ajax/changeGrade.php', { cid : child, gid : grade_id });
           }
           
           if (school > 0) {       
               $.get('ajax/getClasses.php', { id : school }, function(data) {
                   data = $.parseJSON(data);
                   grade.append("<option value=0>choose</option>");
                   $.each(data, function(i, val) {
                       grade.append("<option value=" + i + ">" + val + "</option>");
                   });
                   grade.attr('disabled', false);
               });
            }           
        });
        
        $("body").delegate(".grade", 'change', function() {
            var grade = $(this).closest('tr').find('.grade');
            var grade_id = grade.val();
            var child = $(this).closest('tr').find('.user_id').val();
            $.get('ajax/changeGrade.php', { cid : child, gid : grade_id });
        });
    });
</script>

<h1>Soldier List</h1>

<?
$registered = false;
$not_registered = false;

if (isset($_POST['submit'])) {
    if (isset($_POST['registered'])) {
        $registered = true;
    }
    if (isset($_POST['not_registered'])) {
        $not_registered = true;
    }
?>
<p>Please Note: As soon as you change the school for the child it happens instantly so be careful!</p>
<table border="1" cellspacing="3" style="font-size:12px">
<tr>
    <th>First Name</th>
    <th>Last Name</th>
    <th>Serial Number</th>
    <th>Registered</th>
    <th>Assigned To</th>
    <th>Change to</th>
    <th>Grade</th>
</tr>
<?
$schools = array();
$sch_sql = "select school_id, school_name from schools";
$sch_res = mysql_query($sch_sql);
while ($sch_row = mysql_fetch_assoc($sch_res)) {
    $schools[$sch_row['school_id']] = $sch_row['school_name'];
}
$tmp = array( 0 => 'None' );
$schools = $tmp + $schools;

if ($registered && $not_registered) {
    $where = "";
} else if ($registered) {
    $where = "where user_registered > 0";
} else if ($not_registered) {
    $where = "where user_registered is NULL";
} else {
    echo "You have not chosen anything, please go back and make your choice.";
    exit;
}

$sql = "
        select user_id, first, last, user_registered, user_serial, user_code, u.school_id, school_name 
        from users u 
        left join schools s on (s.school_id = u.school_id) " . 
        $where . "  
        order by last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
        
    if ($row['last'] == "")
        continue;
    $registered = $row['user_registered'] > 0 ? 'yes' : 'no';
   
    echo "<tr><td><input type='hidden' name='user_id' class='user_id' value=" . $row['user_id'] . ">" . 
        $row['first'] . "</td><td>" . $row['last'] . "</td><td>" . $row['user_serial'] . "</td><td>" . 
        $registered . "</td><td>" . $row['school_name'] . "</td><td>" . 
        "<select class='school'>";
    foreach ($schools as $index => $school) {
        if (is_null($row['school_id'])) 
            $row['school_id'] = 0; 
        if ($index == $row['school_id'])
            echo "<option value=" . $index . " selected='selected'>$school</option>";
        else
            echo "<option value=" . $index . ">$school</option>";
    }
    echo "</select></td><td>";
    echo "<select class='grade' disabled='true'></select></td></tr>";
}
?>
</table>

<? } else { ?>
    <form action="soldiers2.php" method="post">
        <p>Please choose which children to display:</p>
        <input type="checkbox" name="registered">Registered<br />
        <input type="checkbox" name="not_registered">Not Registered<br /><br />
        <input type="submit" name="submit" value="submit">
    </form>
<? } ?>

<? else : ?>
no permission to view this page
<? endif; ?>

</body>
</html>

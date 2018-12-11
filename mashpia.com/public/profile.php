<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="jquery.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Admin Profile</title>
<style>
    tr, td {
        padding: 5px;
        border: 1px dashed black;
    }
    .photo img {
        border: 1px solid black;
    }
</style>
<script type="text/javascript">
function validateForm() {
    var username = $('#username').val();
    var password = $('#password').val();
    var first = $('#first').val();
    var last = $('#last').val();
    var email = $('#admin_email').val();
    var work = $('#admin_phone_work').val();
    var home = $('#admin_phone_home').val();
    var cell = $('#admin_phone_mobile').val();
    var address = $('#admin_address1').val();
    
    var msg = '';
    if ( username == '' ) {
        msg += "You must have a username.\n";
    }
    if ( password == '' ) {
        msg += "You must have a password.\n";
    }
    if ( password == '1234' ) {
        msg += "You must enter a different password.\n"
    }
    if ( first == '' || first == 'Update' ) {
        msg += "Please enter your first name.\n";
    }
    if ( last == '' || last == "Profile Here" ) {
        msg += "Please enter your last name.\n";
    }
    if ( address == '' ) {
        msg += "Please enter an address.\n";
    }
    if ( email == '' ) {
        msg += "Please enter an email.\n";
    }
    
    if ( cell == '' && home == '' && work == '' ) {
        msg += "Please enter at least one phone number.";
    }
    
    if ( msg != '' ) {
        alert( msg );
        return false;
    }
}
    
$(document).ready( function() {
    $(".photo a").click( function() {
        $(".upload").html( "<form action='upload_photo.php' method='post' enctype='multipart/form-data'>" +  
                "<br />Upload Picture:<br /><input type='file' name='photo' /><br />" + 
                "<input type='hidden' name='admin_id' value='<?=$admin_user['admin_id']?>' />" + 
                "<input type='submit' name='submit' value='submit' />" + 
            "</form>" );
    });
});    
</script>
</head>

<body>
<? require_once('admin_header2.php'); ?>
<h1>Update Profile</h1>
<?
$fields = array( 
    'Username'  =>  'username',
    'Password'  =>  'password',
    'Title'     =>  'title',
    'First'     =>  'first',
    'Last'      =>  'last',
    'Address'   =>  'admin_address1',
    'Address2'  =>  'admin_address2',
    'City'      =>  'admin_city',
    'State'     =>  'admin_state',
    'Zip'       =>  'admin_postal',
    'Country'   =>  'admin_country',
    'Work Number'   =>  'admin_phone_work', 
    'Home Number'   =>  'admin_phone_home',
    'Cell Number'   =>  'admin_phone_mobile', 
    'Email'     =>  'admin_email'
);

if ( isset( $_POST['submit'] ) ) {
    $sql = "update admins set ";
    foreach ( $fields as $field ) {
        //if username has not been changed, skip from sql b/c query will not work 
        if ( $field == 'username' ) {
            if ( $_POST[$field] == $admin->username ) continue;
        }
        $sql .= $field . "='" . $_POST[$field] . "'";
        if ( $field != 'admin_email' ) $sql .= ", ";
    }
    $sql .= "where admin_id = " . $admin->admin_id;
    if ( mysql_query( $sql ) ) {
        ?>
        <script type="text/javascript">
            document.location = "admin.php";
        </script>
        <?
    } else {
        echo "There was an error: " . mysql_error();
    }
}
?>
<form action="profile.php" method="post" onsubmit="return validateForm()" >
    <div class="module" id="module-info">
        <div class="module_content">
            <div class="lists form">
                <ul>
                    <?
                    foreach ( $fields as $name => $field ) {
                        echo "<li><span class='label'><label for='" . $field . "'></label>" . $name . "</span>";
                        if ( $field == 'password' ) {
                            echo "<span class='input'><input type='password' id='" . $field . "' name='" . $field . "' value='" . $admin->$field . "'></span></li>";                            
                        } else {
                        echo "<span class='input'><input type='text' id='" . $field . "' name='" . $field . "' value='" . $admin->$field . "'></span></li>";
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <div align="center">
        <input type='submit' name='submit' value='update' class='button' />
    </div>    
</form>
<div class='photo'>
<? 
$p = "select photo from admins where admin_id = " . $admin_user['admin_id'];
$res = mysql_query($p);
$pRow = mysql_fetch_assoc($res);
$photo = $pRow['photo'];
if (empty($photo)) { ?>
    <form action="upload_photo.php" method="post" enctype="multipart/form-data">
        Upload Personal Picture:<br /><input type="file" name="photo" /><br />
        <input type="hidden" name="admin_id" value="<?=$admin_user['admin_id']?>" />
        <input type="submit" name="submit" value="submit" />
    </form>
<? 
} else {
    $size = getimagesize("images/staff/$photo"); 
    $width = $size[0];
    $height = $size[1];
    if ($width > 150) {
        if ($width > 250) {
            if ($width > 450) {
                $width = 0.25 * $width;
                $height = 0.25 * $height;
            } else {
                $width = 0.5 * $width;
                $height = 0.5 * $height;
            }
        } else {
            $width = 0.75 * $width;
            $height = 0.75 * $height;
        } 
    }
    echo "<img src='images/staff/$photo' width='$width' height='$height' />";
    echo "<br /><a href='#'>update photo</a>";
    echo "<span class='upload'></span>";
} ?>
</div>
</body>
</html>
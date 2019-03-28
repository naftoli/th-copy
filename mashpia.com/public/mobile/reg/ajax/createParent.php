<?
chdir('../../../');
require 'db.php';

$father = mysql_real_escape_string( $_POST['father'] );
$mother = mysql_real_escape_string( $_POST['mother'] );
$lname = mysql_real_escape_string( $_POST['lastname'] );
$email = mysql_real_escape_string( $_POST['email'] );
$phone = mysql_real_escape_string( $_POST['phone'] );
$address = mysql_real_escape_string( $_POST['address'] );
$city = mysql_real_escape_string( $_POST['city'] );
$state = mysql_real_escape_string( $_POST['state'] );
$zip = mysql_real_escape_string( $_POST['zip'] );
$country = mysql_real_escape_string( $_POST['country'] );
$username = mysql_real_escape_string( $_POST['username'] );
$pwd = mysql_real_escape_string( $_POST['pwd'] );
$fatherPic = mysql_real_escape_string( $_POST['fatherPic'] );
$motherPic = mysql_real_escape_string( $_POST['motherPic'] );

//check if username and email has been used
$sql = "select * from admins where username = '" . $username . "' or admin_email = '" . $email . "'";
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
	echo -1;
	exit;
}

//create thumbnails of image
$pics = array( $fatherPic, $motherPic );
foreach ($pics as $pic) {
	if (!is_null($pic)) {
		$target = "../" . $pic;
		try {
			//create thumb from image
			$image = new Imagick( $target );
			$width = $image->getImageWidth();
			if ($width > 250) {	
				$image->thumbnailImage( 250, 0 );
				$image->writeImage( $target );
			}
			$image->destroy();
		} catch (ImagickException $e) {
			//echo $e->getMessage();
		}
	}
}

chdir('newClasses');
require 'newParent.php';
$info = array(
	'first' 			=>	$father . ' / ' . $mother, 
	'last'				=>	$lname, 
	'admin_address1'	=>	$address, 
	'admin_city'		=>	$city, 
	'admin_state'		=>	$state, 
	'admin_postal'		=>	$zip, 
	'admin_country'		=>	$country, 
	'admin_phone_mobile'=>	$phone, 
	'admin_email'		=>	$email, 
	'username'			=>	$username, 
	'password'			=>	$pwd, 
	'father'			=>	$father, 
	'mother'			=>	$mother, 
	'father_pic'		=>	$fatherPic, 
	'mother_pic'		=>	$motherPic
);
$p = new newParent();
$created = $p->action( $info );
if ($created) {
	$p->sendConfEmail();
	echo 1;
} else {
	echo 0;
}
?>
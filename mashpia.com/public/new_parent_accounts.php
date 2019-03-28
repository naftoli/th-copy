<?
$admin_auth = array('school'); 
require('header.php');
require 'PHPExcel/IOFactory.php';
?>
<html>
    <head>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
<?
$msg = "";
if ( isset( $_FILES['file'] ) ) {
    //new parents
    $parents = array();
    
    //load spreadsheet
    $objPHPExcel = PHPExcel_IOFactory::load( $_FILES['file']['tmp_name'] );
    $objWorksheet = $objPHPExcel->getActiveSheet();
    
    //get all info and save to database
    $headers = array();
    $firstRow = true;
    $errorLine = 1;
    $num = -1; //first time it loops through header and sets num to 0
    
    foreach ( $objWorksheet->getRowIterator() as $row ) {
        if ( $firstRow ) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach ( $cellIterator as $cell ) {
                $headers[] = trim( $cell->getValue() );
            } 
            $firstRow = false;
        } else {
            $i = 0; 
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells( false );
            foreach ( $cellIterator as $cell ) {
                $value = trim( $cell->getValue() );
                switch ( $headers[$i] ) { 
                    case 'First Name':
                        if ( empty( $value ) ) { 
                            $msg .= "Error on line $errorLine: $headers[$i] is mandatory.<br />";
							break;
                        }
                        $parents[$num]['first'] = $value;
                        break;
                    case 'Last Name': 
                        if ( empty( $value ) ) { 
                            $msg .= "Error on line $errorLine: $headers[$i] is mandatory.<br />";
							break;
                        }
                        $parents[$num]['last'] = $value;
                        break;
                    case 'Address':
                        $parents[$num]['address'] = $value;
                        break;
                    case 'City':
                        $parents[$num]['city'] = $value;
                        break;
                    case 'State':
                        $parents[$num]['state'] = $value;
                        break;
                    case 'Zip':
                        $parents[$num]['zip'] = $value;
                        break;
                    case 'Country':
                        $parents[$num]['country'] = $value;
                        break;
                    case 'Home Phone':
                        $parents[$num]['hphone'] = $value;
                        break;
                    case 'Cell Phone':
                        $parents[$num]['cphone'] = $value;
                        break;
                    case 'Work Phone':
                        $parents[$num]['wphone'] = $value;
                        break;
                    case 'Email': 
                        if ( empty( $value ) ) { 
                            $msg .= "Error on line $errorLine: $headers[$i] is mandatory.<br />";
							break;
                        } else {
                            if ( !filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
                                $msg .= "Error on line $errorLine: Invalid email format.<br />";
								break;
                            }
                        }
                        $parents[$num]['email'] = $value;
                        break;
                }
                $i++;
            }
        }
        $errorLine++;
        $num++;
    }

    if ( count( $parents ) == 0 ) {
        $msg .= "You have not provided any names.<br />";
    }
    
    /*
    echo "<pre>";
    print_r( $parents );
    echo "</pre>";
    exit;
    */
     
    if ( $msg == "" ) {
        //insert all values into db
        foreach ( $parents as $parent ) {
            $first = mysql_real_escape_string( ucwords( $parent['first'] ) );
            $last = mysql_real_escape_string( ucwords( $parent['last'] ) );
            $address = mysql_real_escape_string( ucwords( $parent['address'] ) );
            $city = mysql_real_escape_string( ucwords( $parent['city'] ) ); 
            $state = mysql_real_escape_string( strtoupper( $parent['state'] ) ); 
            $zip = mysql_real_escape_string( $parent['zip'] );
            $country = mysql_real_escape_string( ucwords( $parent['country'] ) );
            $hphone = mysql_real_escape_string( $parent['hphone'] );
            $cphone = mysql_real_escape_string( $parent['cphone'] ); 
            $wphone = mysql_real_escape_string( $parent['wphone'] );
            $email = mysql_real_escape_string( $parent['email'] );
            
            $pos = strpos( $address, ' ' );
            if ( $pos ) {
                $addressNum = substr( $address, 0, $pos );
            } else {
                $addressNum = '';
            }
            $lastPart = substr( $last, 0, 3 );
            $firstPart = substr( $first, 0, 3 );
            $user = strtolower( $firstPart ) . strtolower( $lastPart ) . $addressNum;
			
			//check if email exists
			$sql = "select * from admins where admin_email = '" . $email . "'";
			$result = mysql_query( $sql );
			$emailNum = mysql_num_rows($result);
			if ( $emailNum ) {
				continue;
			}
            
            //check if username exists
            $sql = "select * from admins where username = '" . $user . "'";
            $result = mysql_query( $sql );
            $num = mysql_num_rows($result);
            if ( $num ) {
            	$salt = rand( 10, 99 );
                $user .= $salt;
            }

            $sql = "insert into admins set 
                    first = '$first', 
                    last = '$last', 
                    username = '$user', 
                    password = 'parent', 
                    admin_address1 = '$address', 
                    admin_city = '$city', 
                    admin_state = '$state', 
                    admin_postal = '$zip', 
                    admin_country = '$country', 
                    admin_phone_work = '$wphone', 
                    admin_phone_home = '$hphone', 
                    admin_phone_mobile = '$cphone', 
                    admin_email = '$email', 
                    is_parent = 1  
                    ";
            //echo $sql;
            
            if ( !@mysql_query( $sql ) ) {
            	echo mysql_error() . "<br />";
                $msg = "There was an error uploading your spreadsheet.<br />";
                $msg .= "Please contact Tzivos Hashem.<br />";
                $msg .= "Thank You!<br />";
                break;
            } else {
                //send email to parent with details of account
                $text = "Congratulations!
You have a new account that has been setup on mashpia.com.

Your account details are as follows:
Username: $user
Password: parent

To login go to mashpia.com and enter username / password.

Once logged in, you can update your profile and add your existing
children to your account by putting in their 20 digit barcode.

Thank you!

Please Note: If you already have an account setup with us, 
please continue using your existing account and disregard this message.";
                $to = "$email";
                $subject = "New Mashpia.com Account";
                $headers = "From: cth@tzivoshashem.org" . "\r\n" . 
                           "Reply-to: cth@tzivoshashem.org";
                @mail( $to, $subject, $text, $headers );
            }
        }
        if ( $msg == "" ) {
            $msg = "Your parent accounts were successfully created.<br />";
            $msg .= "Thank You!<br />";
        }
    } else {
        $msg .= "Please correct the mistake(s) and then try again.<br />";
    }
}

$admin_id = $admin_user['admin_id'];
$auth = $admin_auth[0];

require 'class.adminSchools.php';
$a = new AdminSchools( $admin_id, $auth );
$schools = $a->getSchools();

//parents empty excel sheet
$inputFileName = 'parents.xls';

foreach ( $schools as $id => $school ) {
    $school_id = $id;
    break;
}

//create empty excel sheet with school id in file name
$file = "parents_{$school_id}.xls";

//delete parents_school_id.xls if exists
if ( file_exists( $file ) ) {
    unlink( $file );
}

// Read the file
$objPHPExcel = PHPExcel_IOFactory::load( $inputFileName );

//update protection
$objPHPExcel->getActiveSheet()->getProtection()->setSheet( true );

// Write the file
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");

$admins = array();
$parents = array();
$children = array();
$sql = "SELECT u.school_id, aa.admin_id, aa.id, a.first, a.last, u.user_id, u.first, u.last
		FROM admin_auths aa
		JOIN admins a
		USING ( admin_id ) 
		RIGHT JOIN users u ON ( u.user_id = aa.id ) 
		WHERE user_registered >0
		AND u.school_id = $school_id 
		ORDER BY school_id, u.last";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) )  {
	$children[] = $row['user_id'];
	$admin_id = $row['admin_id'] ? $row['admin_id'] : 0;
	if ( $admin_id > 0 ) {
		$admins[] = $admin_id;
	}
	$parents[$admin_id][] = $row['user_id'];
}
echo "<pre>";
print_r( $parents );
echo "</pre>";

$objWriter->save( $file );

include('admin_header.php');
?>    
        <h1>Create Parent Accounts</h1>
        <? if ( isset( $msg ) ) {
            echo "<div style='color:red'>" . $msg . "</div><br />";
        } ?>
        <div class="infobox">
            <p>Directions:</p>
            <p>1. Please download <a href="<?=$file?>">spreadsheet</a>.</p>
            <p>2. Update the file and save. (First Name, Last Name, and Email are mandatory).</p>
            <p>3. Upload the file.</p>
            <p>Please Note: Any email which already exists in the system, no new account will be created for that individual.</p>
        </div>
        <div class="box_content">
            <form action="new_parent_accounts.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
            <label><?=T_('Upload your saved spreadsheet')?>
            <br /><input type="file" name="file" class="file"></label>
            <br /><input type="submit" name="submit" value="upload" />
            </form>
        </div>
    </body>
</html>
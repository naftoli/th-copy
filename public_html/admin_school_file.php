<?  // allows schools to upload class files to server // ?>
<? ini_set('display_errors', TRUE); ?>
<? ini_set('max_execution_time', 300); ?>
<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
$ui_type = 'school';
require_once('admin_ui.php');
require_once('file_save.php');

// contains email addresses
require_once('constant_file.php');

$auth_mode = check_id_access();
$school_id = gri('school_id', -1);

$action = gr('action');
$edit_row = false;

// get responsible person 
// get 18 = director, if director not found then use 16 = principal 

$admins = mysql_query("SELECT 
		a.admin_email, a.first, a.last, s.school_name
		FROM admin_auths 	AS aa 
		JOIN admins 		AS a USING (admin_id)
		LEFT JOIN schools 	AS s on s.school_id = aa.id
		WHERE aa.id=" . $school_id . " AND aa.role_id in(16,18) AND aa.auth='school' order by aa.role_id desc ");

$admins_row = mysql_fetch_assoc($admins);

function get_user_serial() {
    $user_serial = 0;
    $sql = "SELECT IFNULL(MAX(user_serial), 0)+1 AS user_serial FROM users";
    $query = mysql_query($sql);
    $row = mysql_fetch_assoc($query);
    $user_serial = $row["user_serial"];
    return $user_serial;
    
}

function get_user_code() {
    $user_code = 0;
	/*
    if (mysql_result(mq("SELECT GET_LOCK('users', 30)"),0) != 1) 
        trigger_error('could not get lock', E_USER_ERROR);
    */
    $count = 0;
    do {
        if ($count++ > 100000) 
            trigger_error('could not get ID', E_USER_ERROR);
        $user_code = mysql_result(mq('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
    } while (mysql_result(mq("SELECT COUNT(*) FROM users WHERE user_code = $user_code"),0) != 0);
    return $user_code;
}
//variable for determining when to show student grade picker table
$showTable = false;

$tanyaSchool = 0;
$chidonSchool = 0;
// find out if school is a chidon only school or a tanya/mishna only school
if ($school_id > 0) {
    $sql = "select chayolei, tanya, chidon from schools where school_id = " . $school_id;
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    if ($row['chayolei'] == 0) {
        if ($row['chidon'] == 1) {
            $chidonSchool = 1;
        }
        if ($row['tanya'] == 1) {
            $tanyaSchool = 1;
        }
    }
}

if(gr('save') && $school_id != -1) { 
      
      if(isset($_FILES['file'])) {
          //added by Naftoli January 15, 2013 
          $h_school = $_POST['hschool'];
          	  
          //create array with header info
            $columnNames = array( 
                  "*First Name", 
                  "*Last Name", 
                  "*First Name Hebrew", 
                  "*Last Name Hebrew",
                  "*Gender",
                  "*English Date of Birth", 
                  "Address 1", 
                  "Address 2", 
                  "City", 
                  "State", 
                  "Zip", 
                  "Country", 
                  "Phone", 
                  "Parents Email"
            ); 
          if ( !$h_school ) {
              //add to array 'Mission Type'
              $columnNames[] = "*Mission Type";
          } 
          //print_r( $columnNames );	  

          //check if file has necessary info
	  	  require 'PHPExcel/IOFactory.php';
          $objPHPExcel = PHPExcel_IOFactory::load($_FILES['file']['tmp_name']);
          $objWorksheet = $objPHPExcel->getActiveSheet();

          //load spreadsheet data into array
          $headers = array();
          $data = array();
          $rowNum = 0;
          $errorLine = 1;
          $firstRow = true;
          $msg = "";
          foreach ( $objWorksheet->getRowIterator() as $row ) {
              if ( $firstRow ) { 
                  $cellIterator = $row->getCellIterator();
                  $cellIterator->setIterateOnlyExistingCells(false);
                  foreach ( $cellIterator as $cell ) {
                      $headers[] = $cell->getValue();
                  }
                  if ( array_diff( $headers, $columnNames ) ) {
                      $msg .= "You have an incorrect excel sheet.<br />";
                      $msg .= "Please download the sample file again and do not modify the header information.";
                      break;
                  }
                  $firstRow = false;
              } else {
                  $i = 0; 
                  $cellIterator = $row->getCellIterator();
                  $cellIterator->setIterateOnlyExistingCells(false);
                  foreach ( $cellIterator as $cell ) {
                      $value = trim($cell->getValue());
                      if ( $h_school ) {
                          if ( $headers[$i] == "*First Name" || $headers[$i] == "*Last Name" ) {
                              if ( $value == "" ) {
                                  $msg .= "Error: You must supply a first name and last name for every student.<br />";
                              }
                          }
                      } else {
                          if ( $headers[$i] == "*First Name" || 
                               $headers[$i] == "*Last Name" ||
							   $headers[$i] == "*First Name Hebrew" || 
                               $headers[$i] == "*Last Name Hebrew" ||
                               $headers[$i] == "*Gender" || 
                               $headers[$i] == "*English Date of Birth" || 
                               $headers[$i] == "*Mission Type" ) {
                              if ( $value == "" ) {
                                  $msg .= "You have an error on line " . $errorLine . ".<br />";
								  // old error message
                                  //$msg .= "You must supply a first name, last name, first name hebrew, last name hebrew, gender, dob, and mission type for every student.<br />";
								  
								  // changed error message to be more descriptive on 9/6/2017
								  $msg .= "You must supply a ". substr($headers[$i], 1) . " for every student.<br /><br />";
                              }
                          }
						  
						  if ( $headers[$i] == "*First Name" ||
							   $headers[$i] == "*Last Name" ||
							   $headers[$i] == "*First Name Hebrew" ||
							   $headers[$i] == "*Last Name Hebrew" ) {
								if ( strlen( $value ) < 3 ) {
									$msg .= "You have an error on line " . $errorLine . ".<br />";
									// old error message
									//$msg .= "Names cannot be less than 3 characters in length.<br />";
									
									// changed error message to be more descriptive on 9/6/2017
									$msg .= substr($headers[$i], 1) . " cannot be less than 3 characters in length.<br /><br />";
								}
						  }
						  
						  if ( $headers[$i] == "*First Name Hebrew" ||
							   $headers[$i] == "*Last Name Hebrew" ) {
								$str = urlencode( $value );
								if ( strpos( $str, '%' ) === false ) {
									$msg .= "You have an error on line " . $errorLine . ".<br />";
									$msg .= "Hebrew name must be in hebrew characters.<br />";
								}
						  }
						  
                          if ( $headers[$i] == "*Gender" ) {
                              //echo $value; 
                              if ( strtolower( $value ) != 'm' && strtolower( $value ) != 'f' ) {
                                  $msg .= "Error on line " . $errorLine . ": Gender must be 'm' or 'f'<br />";
                              }
                          }
                          
                          if ( $headers[$i] == "*English Date of Birth" ) {
                              if ( empty( $value ) ) { 
                                  $msg .= "Error on line $errorLine: $headers[$i] is mandatory.<br />";
                                  break;
                              } 
                              if ( is_numeric( $value ) ) {  
                                  $date = PHPExcel_Shared_Date::ExcelToPHPObject( $value );
                                  $timestamp = $date->getTimestamp();
                                  $jd = unixtojd( $timestamp ); 
                                  $dob = jdtogregorian( ++$jd ); // for some reason the result is off by one day so need to add 1
                                  $arrDate = explode( "/", $dob );
                              } else {
                                  if ( !strpos( $value, '/' ) )
                                      $msg .= "Error on line " . $errorLine . ": Date of Birth must follow the format MM/DD/YYYY.<br />";
                                  else 
                                      $arrDate = explode( "/", $value );
                              }
                              if ( (int)$arrDate[0] > 12 || (int)$arrDate[0] < 1 ) {
                                  $msg .= "Error on line " . $errorLine . ": Month must be a number between 1 and 12.<br />";
                              }
                              if ( (int)$arrDate[1] > 31 || (int)$arrDate[1] < 1 ) {
                                  $msg .= "Error on line " . $errorLine . ": Day must be a number between 1 and 31.<br />";
                              }
                              //get current year and make sure year is between 5 years ago and 14 years ago
                              $year = date( 'Y' );
                              $start = $year - 15;
                              $end = $year - 5;
                              if ( (int)$arrDate[2] < $start || (int)$arrDate[2] > $end ) {
                                  $msg .= "Error on line " . $errorLine . ": Year cannot be less than $start or more than $end.<br />";
                              }
                              $sqlDate = $arrDate[2] . "-" . $arrDate[0] . "-" . $arrDate[1];
                              $value = $sqlDate;                              
                          }
                          
                          if ( $headers[$i] == "*Mission Type" ) {
                              $value = strtolower($value);
                              if ( $value != 'chabad' && $value != 'frum' ) {
                                  $msg .= "Error on line " . $errorLine . ": Mission type must be 'chabad' or 'frum'<br />";
                                  //$msg .= "Value given - " . $value . "<br />";
                              }
                          }
                      }
                      if ( $msg != "" ) {
                          $msg .= "Please correct the mistake(s) and then try again.<br />";
                          break 2;
                      }
                      $data[$rowNum][$headers[$i++]] = $value;
                  }
              }
              $rowNum++;
              $errorLine++;
          }
          
          /*
          echo "<pre>";
          print_r( $data );
          echo "</pre>";
          */
		  //echo $msg;
          
          //make sure that there is information that has been uploaded
          if ( count( $data ) > 0 && $msg == "" ) {
			/*
			//get all subjects
			$sbj = "select * from subjects where subject_type NOT IN ('school_points', 'home_points')";
			$sub_res = mysql_query($sbj);
			$subjects = array();
			while ($subject = mysql_fetch_assoc($sub_res)) {
				$subjects[] = $subject['subject_id'];
			}
			*/
              //create users and get user_ids
              foreach ( $data as $index => $row ) {
                  //set up variables
                  $first = $row['*First Name'];
                  $last = $row['*Last Name'];
                  $first_he = $row['*First Name Hebrew'];
                  $last_he = $row['*Last Name Hebrew'];
                  $gender = strtolower( $row['*Gender'] );
                  $dob = $row['*English Date of Birth'];
                  $user_address1 = $row['Address 1'];
                  $user_address2 = $row['Address 2'];
                  $user_city = $row['City'];
                  $user_state = $row['State'];
                  $user_postal = $row['Zip'];
                  $user_country = $row['Country'];
                  $user_email = $row['Parents Email'];
                  if ( isset( $row['*Mission Type'] ) ) {
                      $mission_type = $row['*Mission Type'];
                  }
                  
                  $user_code = get_user_code();
                  $user_serial = get_user_serial(); 
                  $school_type = 0;
                  
                  $sql = "insert into users set ";
                  $sql .= "lang='en', ";
                  $sql .= "user_code=" . $user_code . ", ";
                  $sql .= "first='" . mysql_real_escape_string(ucwords(strtolower($first))) . "', ";
                  $sql .= "last='" . mysql_real_escape_string(ucwords(strtolower($last))) . "', ";
                  $sql .= "school_id=" . $school_id . ", ";                   
                  $sql .= "first_he='" . mysql_real_escape_string($first_he) . "', ";
                  $sql .= "last_he='" . mysql_real_escape_string($last_he) . "', ";
                  $sql .= "gender='" . mysql_real_escape_string($gender) . "', ";
                  $sql .= "dob='" . mysql_real_escape_string($dob) . "', ";
                  $sql .= "user_address1='" . mysql_real_escape_string($user_address1) . "', ";
                  $sql .= "user_address2='" . mysql_real_escape_string($user_address2) . "', ";
                  $sql .= "user_city='" . mysql_real_escape_string($user_city) . "', ";
                  $sql .= "user_state='" . mysql_real_escape_string($user_state) . "', ";
                  $sql .= "user_postal='" . mysql_real_escape_string($user_postal) . "', ";
                  $sql .= "user_country='" . mysql_real_escape_string($user_country) . "', ";
                  $sql .= "email='" . mysql_real_escape_string($user_email) . "', "; 
                  if ( isset( $mission_type ) ) {
                      switch ( $mission_type ) {
                          case 'chabad':
                              if ($gender == 'm') $school_type = 2;
                              else if ($gender == 'f') $school_type = 3;
                              break;
                          case 'frum':
                              if ($gender == 'm') $school_type = 12;
                              else if ($gender == 'f') $school_type = 13;
                              break;
                      }
                  }
                  $sql .= "school_type_id = " . $school_type . ", ";
                  $sql .= "user_start_date='" . unixtojd() . "', ";                         
                  $sql .= "user_serial=" . $user_serial;
                  
                  if ($tanyaSchool) {
                    $sql .= ", yan = 1";
                  }
                  if ($chidonSchool) {
                    $sql .= ", chidon = 1";
                  }
                  //echo $sql;
                  mysql_query( $sql ) or die( mysql_error() );
                  $user_id = mysql_insert_id();
                  $data[$index]['user_id'] = $user_id;				   
					/*
			        //create ladder/year for child
			        foreach ($subjects as $subject) {
			        	$track_id = 1;
						if ($subject == 1) {
							if (in_array($school_type, array(12,13))) {
								$track_id = 3;
							} else if (in_array($school_type, array(2,3))) {
								$track_id = 5;
							}
						}
			            $ins = "insert into user_tracks values ($user_id, $subject, $track_id, 6, 1)";
			            @mysql_query($ins);
			        }
					/*
			        //create private rank for soldier 
			        $jd = unixtojd();
			        $sql = "insert into rank_marks 
			        		set rank_ord = 1, 
			        		user_id = $user_id, 
			        		date_promoted = $jd";
			        @mysql_query( $sql );
			        */
					// setup user tracks
					require_once 'class.campaignEnrollment.php';
					$c = new CampaignEnrollment( $user_id );
					$c->enroll();
					
					// setup birthday missions
					require_once 'class.birthday.php';
					$b = new Birthday( $user_id );
					$b->setBirthday();
					require_once 'class.birthdayYi.php';
					$b = new BirthdayYi( $user_id );
					$b->setBirthday();
					
					//set dob for syncing with wp
					require_once 'class.heDob.php';
					$hdob = new HeDob( $user_id );
					$hdob->setHeDob();
					$msg = "Students have been successfully created.";
					//show table to allow users to assign students to grades
					$showTable = true;
              }
          } else {
              if ( $msg == "" ) {
                  $msg .= "You have not provided any names.<br />";
                  $msg .= "Please go back and try again once your file is ready.";
              }
          }
           
    /*
    function send_email($email, $subject, $body, $type = 'html')
    {
        mail($email, $subject, $body, "From: No Reply <noreply@" . str_replace('www.', '', strtolower($_SERVER['HTTP_HOST'])) . ">\r\nX-Mailer: PHP/" . phpversion() . "\r\nErrors-To: errors@" . str_replace('www.', '', strtolower($_SERVER['HTTP_HOST'])) . "\r\nMIME-Version: 1.0\r\nContent-Type: text/" . $type . "; charset=utf-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n");
    }
          
          $school_file_id = gri('file_delete', 0) ? 'NULL' : 'school_file_id';
          $school_file_id = addFile($_FILES['file'], $school_file_id);
		  if($school_file_id !== 'school_file_id')
		  {
		  	  mq("DELETE FROM files USING files JOIN schools ON (files.file_id = schools.school_file_id) WHERE school_id = $school_id");		
			  mq("UPDATE schools SET school_file_id = $school_file_id WHERE school_id = $school_id");
			  
			  // send an e-mail confirming that a new file have been uploaded.			  
			  // these email values come from constant_file.php			  
			  
				// email 1 
				$to = $admins_row['admin_email'] ;				
				$subject='new file uploaded with file id: '. $school_file_id;
				//if($school_list == 'school_list')

				$body = "<br>Dear " .  $admins_row['first'] ." ". $admins_row['last'] . ",<br><br>";
				$body .= "You have successfully uploaded your excel sheet onto the website.";
				$body .= "Our programmers are putting the info into the system. Please give us 24 hours to complete this.<br>";
				$body .= "You will be sent an email when it is done.<br><br>";
				$body .= "Your next step is to:<br><br>";
				$body .= "Take a photo of each child. When your children's infomation is put into the system you will ";
				$body .= "need to upload the photos so we can print ID cards.<br><br>";
				$body .= "If you have any further questions please contact us at:<br><br>";
				$body .= "718 907 8884<br>" ;
				$body .= "CTH@tzivoshashem.org<br>";
				//send_email($to, $subject, $body, $type = 'html');			  

				// email 2 - to TH headquarters//
				$to = $headquarters ;
				$subject='new file uploaded with file id: '. $school_file_id;			  
				$body  = "<br>TH HQ - alert <br>";
				$body .= "<b>" . $admins_row['school_name'] . " </b>(" . $admins_row['first'] . " " . $admins_row['last'] . ") has uploaded an excel sheet. Please look it over and make sure all<br>" ; 
				$body .= "fields are correctly filled out. If something is missing let the school know.<br><br>" ; 
				$body .= "When the file is complete let the programmer know to put it into the system.<br>" ; 
				//send_email($to, $subject, $body, $type = 'html');			  
				
				// email 3 - to programmers
				$to = $programmers_email .",". $director_Montreal_email;
				$subject='new file uploaded with file id: '. $school_file_id;			  
				$body  = "<br>Programmer - alert <br>";
				$body .= "<b>" . $admins_row['school_name'] . " </b>(" . $admins_row['first'] . " " . $admins_row['last'] . ") has uploaded an excel sheet. <br>" ; 
				$body .= "TH is looking it over and will let you know when it is ready to be put into the system.<br><br>" ; 				
				//send_email($to, $subject, $body, $type = 'html');			  				
				
				//$message = T_('Institution edited');
		}
           * 
           */
	}	
}

if ( isset( $_POST['submit'] )  ) {
    $msg = ""; 
    foreach( $_POST['class'] as $user_id => $class_id ) {
        $sql = "update users set class_id = " . $class_id . " 
                where user_id = " . $user_id;
        if ( !@mysql_query( $sql ) ) {
            $msg .= "There was an error assigning your students to classes.<br />";
            $msg .= "Please manually assign classes to each child.<br />";
            $msg .= "Thank you.";
            break;
        }
		/*
        $year = "select class_grade from classes where class_id = " . $class_id;
        $year_res = mysql_query($year);
        $row = mysql_fetch_row($year_res);
        $y = $row[0];
        switch ($y) {
            case 'Pre1a':
                $level = 6;
                break;
            case '1':
                $level = 7;
                break;
            case '2':
                $level = 8;
                break;
            case '3':
                $level = 9;
                break;
            case '4':
                $level = 10;
                break;
            case '5':
                $level = 11;
                break;
            case '6':
                $level = 12;
                break;
            case '7':
                $level = 13;
                break;
            case '8':
                $level = 14;
                break;
            default:
                $level = null;
                break;
        }
        //update user tracks to correct level
        $update = "update user_tracks set level = $level where user_id = " . $user_id;
		@mysql_query($update);
		*/ 
    }
    if ( $msg == "" ) {
        $msg .= "You have sucessfully created your students.<br />";
        $msg .= "Please click <a href='admin_setup_guide.php'>here</a> to continue.<br />";
        $msg .= "Thank you.";
    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('School or Class Upload'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<style>
    th, td { 
        font-size: 12px;
        padding-right: 10px;
    }
</style>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
<DIV class="body">
<DIV class="sub_menu">
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
</DIV>
<H1><?=T_('Base Management')?></H1>
<?if($admin_user['auth'] == 'super' || $auth_mode == 'school' && count($admin_user['auths']['school']) > 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>

<FORM action="admin_school_file.php" method="get" accept-charset="UTF-8">
<P>
<?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT> <INPUT class="submit" type="submit" value="<?=T_('Go')?>">
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
<H2><?=T_('School or Class Upload')?></H2>

<?
//find out if school has classes setup
$sql = "select * from classes where class_era = 0 and school_id = " . $school_id;
$result = mysql_query( $sql );
$num = mysql_num_rows($result);
if ($num == 0) {
    echo "You need to first setup your classes.<br />";
    echo "Please click <a href='admin_class.php?school_id=$school_id'>here</a>
     to setup your classes.";
     exit;
}
if ( $showTable && count($data) ) {
    //get classes
    $classes = array();
    while ( $row = mysql_fetch_assoc( $result ) ) {
        $classes[$row['class_id']] = $row['class_grade'] . ($row['class_sub'] == '' ? '' : '-' . $row['class_sub']);
    }
	
    ?>
    <form action="admin_school_file.php?school_id=<?=$school_id?>" method="post">
        <table>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Assign to Grade</th>
            </tr>
            <?
            foreach ( $data as $index => $row ) {
                echo "<tr><td>" . $data[$index]["*First Name"] . "</td>";
                echo "<td>" . $data[$index]["*Last Name"] . "</td>";
                echo "<td><select name='class[" . $data[$index]['user_id'] . "]'>";
                foreach ( $classes as $class_id => $class ) {
                    echo "<option value=" . $class_id . ">" . $class . "</option>";
                }
                echo "</select></td></tr>";
            }
            ?>
            <tr>
                <td colspan="3" align="center">
                    <input type="submit" name="submit" value="Assign" />
                </td>
            </tr>
        </table>
    </form>
    <?
    exit;
}
if ( isset( $msg ) && $msg != "" ) {
    echo $msg;
	exit;
}
?>

<DIV class="infobox">
<? if ( !$h_school ) {
    $excel = "students.xls";
} else {
    $excel = "hs_students.xls";
} ?>
<P>
    Directions:<br />
    1. Download the <a href="<?=$excel?>" style="background-color: lightblue;">spreadsheet</a>.<br />
    2. Enter all information into spreadsheet.<br />
    <? if ( $h_school ) { ?>
    <strong>Please Note: You MUST have the First Name and Last Name fields of each student filled out.</strong>
    <? } else { ?>
    <strong>Please Note: You MUST have the First Name, Last Name, First Name Hebrew, Last name Hebrew, English Date of Birth, Gender, and Mission Type fields of each student filled out.</strong>
    <? } ?>
    <br />3. Upload spreadsheet into system.<br />
</P>
</DIV>

<? $row = mysql_fetch_assoc(mq("SELECT school_file_id FROM schools WHERE school_id = $school_id")); ?>

<DIV class="box_content">
	<FORM action="admin_school_file.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data" onSubmit="if(!this.elements['confirm'].checked) alert('<?=			esq(T_('To continue, please confirm that you have read the warning.'))?>'); return this.elements['confirm'].checked;">

	<!--<P class="rows"> -->
	<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
	<LABEL><?=T_('Upload your class / school list')?>
	<BR><INPUT type="file" name="file" class="file" style="opacity:1;"></LABEL> <?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B<BR>
	<!--<LABEL><?=T_('This is a class list')?> <INPUT type="checkbox" name="class_list" class="checkbox" value="1"></LABEL>
	<LABEL><?=T_('This is a school list')?> <INPUT type="checkbox" name="school_list" class="checkbox" value="1"></LABEL><br />-->

	<BR>
	<LABEL><INPUT type="checkbox" name="confirm"> 
	    <?=T_('I confirm that these students have never been uploaded before and all fields follow the exact field in the spreadsheet. 
                I understand that if I have not followed the directions correctly, my information will not be added to the system and 
                I will need to re-upload it.')?></LABEL><BR>
	<BR>
	<input type="hidden" name="hschool" value="<?=$h_school?1:0?>" />
	<INPUT class="submit" type="submit" name="save" value="<?=T_('Save')?>">
	<!--</P> -->
	</FORM>
	
</div>
<? endif; ?>

<BR style="clear: both;">
</DIV>
</DIV>
</DIV>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>

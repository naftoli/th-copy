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
    if (mysql_result(mq("SELECT GET_LOCK('users', 30)"),0) != 1) 
        trigger_error('could not get lock', E_USER_ERROR);
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

if(gr('save') && $school_id != -1) { 
      
      if(isset($_FILES['file'])) {
        //create array with header info
        $columnNames = array( 
              "First Name", 
              "Last Name", 
              "First Name Hebrew", 
              "Last Name Hebrew",
              "English DOB"
        );   

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
                    if ( $value == "" ) {
                        $msg .= "All Fields are Mandatory.<br />";
                        $msg .= "Please correct the mistake(s) and then try again.<br />";
                        break 2;
                    }
                    
                    if ($headers[$i] == 'English DOB') {
                       	//process dob for entry into db
                        if ( is_numeric( $value ) ) {
                        	//excel dates when added by admin are numeric values usually                                                                           
                            $date = PHPExcel_Shared_Date::ExcelToPHPObject( $value );
                            $timestamp = $date->getTimestamp();
							$jd = unixtojd( $timestamp );
							$value = jdtogregorian( $jd );
						} else if ( !strpos( $value, '/' ) ) {
                            $msg .= "Error on line " . $errorLine . ": Date of Birth must follow the format MM/DD/YYYY.<br />";
                        	break;
						}                         
                        
						$dateError = "";
                        $arrDate = explode( "/", $value );
                        if ( (int)$arrDate[0] > 12 || (int)$arrDate[0] < 1 ) {
                            $dateError .= "Error on line " . $errorLine . ": Month must be a number between 1 and 12.<br />";
                        }
                        if ( (int)$arrDate[1] > 31 || (int)$arrDate[1] < 1 ) {
                            $dateError .= "Error on line " . $errorLine . ": Day must be a number between 1 and 31.<br />";
                        }
                        
                        //get current year and make sure year is not older than 15
                        $year = date( 'Y' );
                        $start = $year - 16;
                        //$end = $year - 4;
                        if ( (int)$arrDate[2] < $start ) {
                            $dateError .= "Error on line " . $errorLine . ": Year cannot be less than $start.<br />";
                        }
                        
                        if ( $dateError != "" ) {
                            $msg .= $dateError; 
                        }
                        if ($msg != '') {
                            break 2;
                        }
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
        exit;
        */
        
        //make sure that there is information that has been uploaded
        if ( count( $data ) > 0 && $msg == "" ) {
            //show table to allow users to assign students to grades
            $showTable = true;
            
            //create users and get user_ids
            foreach ( $data as $index => $row ) {
                //set up variables
                $first = $row['First Name'];
                $last = $row['Last Name'];
                $first_he = $row['First Name Hebrew'];
                $last_he = $row['Last Name Hebrew'];
                $user_code = get_user_code();
                $user_serial = get_user_serial();
                
                $dob = $row['English DOB'];
				if (strpos($dob, '/') !== false) $arrDob = explode('/', $dob);
				else {
					$msg = "Incorrect dob format. Must be in the format: MM/DD/YYYY<br />";
					break;
				}
                $dob = $arrDob[2] . '-' . $arrDob[1] . '-' . $arrDob[0];
                
                $sql = "insert into users set ";
                $sql .= "lang='en', ";
                $sql .= "user_code=" . $user_code . ", ";
                $sql .= "first='" . mysql_real_escape_string(ucwords(strtolower($first))) . "', ";
                $sql .= "last='" . mysql_real_escape_string(ucwords(strtolower($last))) . "', ";
                $sql .= "school_id=" . $school_id . ", ";                   
                $sql .= "first_he='" . mysql_real_escape_string($first_he) . "', ";
                $sql .= "last_he='" . mysql_real_escape_string($last_he) . "', ";
                $sql .= "dob = '" . mysql_real_escape_string($dob) . "', ";
                $sql .= "user_start_date='" . unixtojd() . "', ";                         
                $sql .= "user_serial=" . $user_serial;
                //echo $sql;
                mysql_query( $sql ) or die( mysql_error() );
                $user_id = mysql_insert_id();
                $data[$index]['user_id'] = $user_id;				   
            }
        } else {
            if ( $msg == "" ) {
                $msg .= "You have not provided any names.<br />";
                $msg .= "Please go back and try again once your file is ready.";
            }
        }
	}	
}

if ( isset( $_POST['submit'] ) ) {
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
    }
    if ( $msg == "" ) {
        $msg .= "You have sucessfully created your chidon students.<br />";
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
	.content {
		display: none;
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

<FORM action="uploadChidonFile.php" method="get" accept-charset="UTF-8">
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
<H2><?=T_('Upload children for Chidon.')?></H2>

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
if ( isset( $msg ) && $msg != "" ) {
    echo $msg;
    exit;
}
if ( $showTable ) {
    //get classes
    $classes = array();
    while ( $row = mysql_fetch_assoc( $result ) ) {
        $classes[$row['class_id']] = $row['class_grade'] . ($row['class_sub'] == '' ? '' : '-' . $row['class_sub']);
    } 
    ?>
    <form action="uploadChidonFile.php" method="post">
        <table>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Assign to Grade</th>
            </tr>
            <?
            foreach ( $data as $index => $row ) {
                echo "<tr><td>" . $data[$index]["First Name"] . "</td>";
                echo "<td>" . $data[$index]["Last Name"] . "</td>";
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
?>

<DIV class="infobox">
<? $excel = "chidon.xls"; ?>
<P>
    Directions:<br />
    1. Download the <a href="<?=$excel?>" style="background-color: lightblue;">spreadsheet</a>.<br />
    2. Enter all information into spreadsheet.<br />
    Please Note: You MUST have ALL FIELDS filled out.<br />
    3. Upload spreadsheet into system.<br />
</P>
</DIV>

<? $row = mysql_fetch_assoc(mq("SELECT school_file_id FROM schools WHERE school_id = $school_id")); ?>

<DIV class="box_content">
	<FORM action="uploadChidonFile.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data" onSubmit="if(!this.elements['confirm'].checked) alert('<?=			esq(T_('To continue, please confirm that you have read the warning.'))?>'); return this.elements['confirm'].checked;">

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
<script>
	$(function() {
		var school = <?=$school_id?>;
		if (school == 176 || school == 54) {
			var password = prompt("Please enter the password to access this page.");
			if (school == 176) {
				if (password != 'laky') {
					alert('You have no permission to access this page.');
					location.href = 'admin.php';
				}
			} else if (school == 54) {
				if (password != 'cth792ep') {
					alert('You have no permission to access this page.');
					location.href = 'admin.php';
				}
			}
		}
		$(".content").show();
	});
</script>
</HTML>

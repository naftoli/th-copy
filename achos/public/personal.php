<?
$admin_auth = array('user');
require_once 'header.php';

require_once 'class.achosStudent.php';
$as = new AchosStudent($admin_user['admin_id']);
$user_id = $as->getStudentID();

if (isset($_POST['personal_info'])) {
    $sql = "UPDATE admins  
            SET 
            first = '" . mysql_real_escape_string($_POST['first'])  . "' ,
            last =  '" . mysql_real_escape_string($_POST['last'])  . "' ,
            username = '" . mysql_real_escape_string( $_POST['username'] ) . "', 
            password = '" . mysql_real_escape_string( $_POST['password'] ) . "' 
        	WHERE admin_id = " . $admin_user['admin_id'];       
    $query = mysql_query($sql); 
    if($query){
        $sql = "update users set 
                first = '" . mysql_real_escape_string($_POST['first'])  . "' ,
                last =  '" . mysql_real_escape_string($_POST['last'])  . "' , 
                class_id = " . mysql_real_escape_string($_POST['grade']) . ", 
                print_missions = " . mysql_real_escape_string($_POST['print']) . "  
                where user_id = " . $user_id;
        mysql_query($sql);
		
		//update level
		$level = $_POST['level'];
		$sql = "update user_tracks set level = " . $level . " where user_id = " . $user_id;
		mysql_query($sql);
    }   
    else{
        include('constant_file.php');
        @mail($programmers_email2, 'Error in program register_parent.php',  "error in SQL update statement: " , mysql_error() );        
    }   
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML>

    <HEAD>
        <TITLE><?=T_('Admin Menu'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
        <LINK href="admin_styles.css" rel="stylesheet" type="text/css">
        <SCRIPT type="text/javascript" src="jquery.js"></SCRIPT>
        <script src="scripts/jquery.styleselect.js"></script>
        <script>
            $( function() {
                $(".sSelect").sSelect();
            });
            
            // validate input   
            function validation() {
                if (document.getElementById('first').value == '') {
                    document.getElementById('first').focus();
                    alert("First Name is mandatory.");
                    return false;
                }   
                else if (document.getElementById('last').value == '') {
                    document.getElementById('last').focus();
                    alert("Last Name is mandatory.");
                    return false;
                }
                else {
                    return true;            
                }
            }
            
        </script>
    </head>
    <BODY>

        <? include('admin_header.php'); ?>
                
        <DIV CLASS="body">          
            
            <DIV class="admin">
                
                <h1>Personal Profile</h1>

                    <form method="post" action="personal.php" accept-charset="UTF-8" name="info"  onsubmit="return validation();">                                 
                                
                        <h2>Personal Info</h2> 
                        <div class="module" id="module-info">
                            <div class="module_content">
                                <div class="lists form">
                                  <ul>
                                      <li>
                                          <span class="label"><label for="first">First Name</label></span>
                                          
                                          <span class="input"><input name="first" type="text" id='first' value='<?=$admin->first?>' /></span>
                                      </li>
                                      <li>
                                          <span class="label"><label for="last">Last Name</label></span>
                                          <span class="input"><input name="last" type="text" id='last' value='<?=$admin->last?>' /></span>
                                      </li>
                                      
                                      <li>
                                          <span class="label"><label for="grade">Grade</label></span>
                                          <span class="input"><select name="grade" class="sSelect">
                                              <?
                                              $gradeSql = "select class_id, print_missions from users where user_id = " . $user_id;
                                              $gradeRes = mysql_query($gradeSql);
                                              $gradeRow = mysql_fetch_assoc($gradeRes);
                                              $grade = $gradeRow['class_id'];
											  //needed for later
											  $print = $gradeRow['print_missions'];
											  
                                              $sql = "select * from classes";
                                              $result = mysql_query($sql);
                                              while ($row = mysql_fetch_assoc($result)) {
                                                  echo "<option value=" . $row['class_id'];
                                                  if ($row['class_id'] == $grade)
                                                    echo " selected='selected'";
                                                  echo ">" . $row['class_grade'] . '-' . $row['class_sub'] . "</option>";
                                              }
                                              ?>
                                          </select></span>
                                      </li>
                                      <li>
                                      	<span class="label"><label for="level">Level</label></span>
                                      	<span class="input"><select name="level" class="sSelect">
                                      		<?
                                      		$sql = "select level from user_tracks where user_id = " . $user_id;
											$result = mysql_query($sql);
											$row = mysql_fetch_assoc($result);
											$level = $row['level'];
                                      		for ($i = $level; $i < 5; $i++) {
                                      			if ($i == $level) echo "<option value='" . $i . "' selected>" . $i . "</option>"; 
												else echo "<option value='" . $i . "'>" . $i . "</option>";
                                      		}
                                      		?>
                                      	</select></span>
                                      </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <h2>Login Info</h2> 
                        <div class="module" id="module-info">
                            <div class="module_content">
                                <div class="lists form">
                                  <ul>
                                      <li>
                                          <span class="label"><label for="first">Username</label></span>
                                          
                                          <span class="input"><input name="username" type="text" id='username' value='<?=$admin->username?>' /></span>
                                      </li>
                                      <li>
                                          <span class="label"><label for="last">Password</label></span>
                                          <span class="input"><input name="password" type="password" id='password' value='<?=$admin->password?>' /></span>
                                      </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <h2>Scoreboard Print Settings</h2>
                        <div class="module" id="module-info">
                            <div class="module_content">
                                <div class="lists form">
                                  <ul>
                                      <li>
                                      	<span class="input">
                                          	<input type="radio" name="print" value="0" 
                                          	<? if (!$print) echo "checked='checked' "; ?>
                                          	/> I do NOT need to have my scoreboard printed<br />
                                      	</span>
                                      </li>
                                      <li>
                                      	<span class="input">
                                      		<input type="radio" name="print" value="1" 
                                      		<? if ($print) echo "checked='checked' "; ?>
                                      		/> I DO need to have my scoreboard printed
                                      	</span>
                                      </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div align='center'><input type="submit" value="Update" class="button" name="personal_info"></div>
                        
                    </form>
                    
            </div>
                    
        </div>
                    
    </body>
                    
</html> 
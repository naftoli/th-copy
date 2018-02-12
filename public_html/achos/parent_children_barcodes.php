<?php
$admin_auth = array('school');
require('header.php');
$admin_id = $admin_user['admin_id'];

$sql = "select id from admin_auths where admin_id = " . $admin_id;
$res = mysql_query( $sql );
$r = mysql_fetch_assoc( $res );
$school_id = $r['id'];

//check for hebrew schools
$h_school = false;
$sql = "select inst_id from schools where school_id = " . $school_id;
$res = mysql_query( $sql );
$row = mysql_fetch_assoc( $res );
$inst_id = $row['inst_id'];
if ( $inst_id == 4 ) {
    $h_school = true;
}

$action = isset($_POST['action']) ? $_POST['action'] : "";
$children = array();

if ($action == "") {			
	$sql = "SELECT s.school_id, s.school_name ";
	$sql = $sql . "FROM admin_auths AS aa ";
	$sql = $sql . "JOIN schools AS s ON (aa.id = s.school_id) ";
	$sql = $sql . "WHERE aa.admin_id=" . $admin_id . " AND aa.auth = 'school'";
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	if ($num_rows > 1) {
		$select = "<SELECT name='school_id'>";
		while ($row = mysql_fetch_assoc($query)) {
			$select = $select . "<OPTION value='" . $row['school_id'] . "'>" . $row['school_name'] . "</OPTION>";
		}
		$select = $select . "</SELECT>";
	}
	else {
		$row = mysql_fetch_assoc($query);
		produce_report($row['school_id']);
	}
}
else {
	produce_report(0);	
}

function produce_report($school_id) {
	global $children;
	global $director_first;
	global $director_last;
	global $director_title;
	
	if ($school_id == 0) $school_id = $_POST['school_id'];
	
	// PROGRAM DIRECTOR //
	$sql = "SELECT a.title, a.first, a.last, a.admin_phone_work FROM admin_auths AS aa JOIN admins AS a USING (admin_id) WHERE aa.auth='school' AND aa.id=" . $school_id . " AND aa.role_id=18";
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$director_title = $row["title"];
	$director_first = $row["first"];
	$director_last = $row["last"];
    $director_phone = trim($row["admin_phone_work"]);
	
	$sql = "SELECT a.first, a.last ";
	$sql = $sql . "FROM admin_auths AS aa ";
	$sql = $sql . "JOIN admins AS a USING (admin_id) ";
	$sql = $sql . "WHERE aa.id=" . $school_id . " AND aa.role_id=18";
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);

	include("camps/includes/classes/user.php");
	include("camps/includes/classes/admin.php");
	$sql = "SELECT u.* ";
	$sql = $sql . "FROM users AS u ";
	$sql = $sql . "JOIN classes AS c USING (class_id) ";
	$sql = $sql . "WHERE u.school_id=" . $school_id . " AND u.class_id > 0 ";
	$sql = $sql . "ORDER BY c.class_grade, c.class_sub ";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$child = new user($row);
		$child->get_childs_parent();
		$child->get_school_class();
		$child->get_school();
		array_push($children, $child);
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML>

	<HEAD>
		<TITLE>BARCODES</TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<STYLE type="text/css">
		</STYLE>
		
		<script>
		</script>
		
		<style type="text/css">
			noprint {
				.noprint { display: none; }
			}
		</style>
		
	</HEAD>
	
	<BODY>
<? if (count($children) > 0) : ?>
<DIV class="noprint">
			<input type="button" value="PRINT" onclick="window.print();"> 
			<?
			if ( $h_school ) 
                $action = "admin_setup_guide_hschool.php";
            else 
                $action = "admin_setup_guide.php";
			?>
			<FORM name="setup_guide" method="post" action="<?=$action?>">
				<input type="hidden" name="admin_id" value="<?=$admin_id;?>">
				<input type="button" value="RETURN" onclick="document.forms['setup_guide'].submit();">
			</FORM>
			</DIV>
			
			<br />
					
			<DIV style="position:absolute; left:50px;">
			
				<? for ($cno = 0; $cno < count($children); $cno++) : ?>
					<? $child = $children[$cno]; ?>
						
					<DIV style="text-align:left;">	
						School: <?=$child->school->school_name;?>
						<br />
						To the parents of: <?=$child->first;?> <?=$child->last;?>
						<br />
						<? $grade = (empty($child->school_class->class_sub) ? $child->school_class->class_grade : $child->school_class->class_grade . '-' . $child->school_class->class_sub);?>
						Grade: <?=$grade?>
						<br />
						Teacher: <?=$child->school_class->class_teacher;?>
						<br />
						Account Number: 3<?=$child->user_code;?>
					</DIV>					
					<br />
					<br />
					
					<DIV style="text-align:left;">
						Dear Parent(s),
					</DIV>
					<br />
					
					<DIV style="text-align:left;">
						We are thrilled to announce that our school, together with schools worldwide, will be participating in Chayolei Tzivos Hashem 5774.
					</DIV>
					<br />
					
					<DIV style="text-align:left;">
						The program cost per child is well over $180. Thanks to the partnership of Merkos 
                        L'Inyonei Chinuch, Tzivos Hashem Headquarters, the Rohr Family, Anash.com, 
                        and generous donations from excited members of our community, your child is 
                        able to join the program for a greatly subsidized price of $50.
					</DIV>
					<br />
					
					<DIV style="text-align:left;">
                        There is an early bird special if you register your child before the 6th of Tishrei (September 10th) and you pay as low as $40.00 per child.
                    </DIV>
                    <br />
                    
                    <DIV style="text-align:left;">
                        For your convenience, you can register your child through cash, check, or credit card.
                    </DIV>
                    <br />
                    
                    <DIV style="text-align:left;">
                        To pay with cash or check, fill out the registration form (enclosed) and send it into our school office. Please make checks payable to our school.
                    </DIV>
                    <br />
                    
                    <DIV style="text-align:left;">
                        To pay with credit card, please register on your parents account on www.mashpia.com. If you forgot your log in information, simply click the 'Forgot Username or Password' link and enter your e-mail address so that you can be reminded of your log in information.
                    </DIV>
                    <br />
					
					<DIV style="text-align:left;">
						If you do not yet have an account please follow the steps below to create one.<br />
						<i>Your parent account can be used to print and mark your children’s missions as well as track their progress.</i>
					</DIV>
					
					<DIV style="text-align:left;">
						&nbsp;&nbsp;&nbsp;&nbsp;1. Go to www.mashpia.com (must use Firefox for the website to work properly)
					</DIV>
					
					<DIV style="text-align:left;">
						&nbsp;&nbsp;&nbsp;&nbsp;2. Click 'Create a Parent Account'
					</DIV>
					
					<DIV style="text-align:left;">
						&nbsp;&nbsp;&nbsp;&nbsp;3. Provide all information requested and add your child to your account by entering his/her 20 digit account number (above)
					</DIV>
					<br />
					
<!--					<DIV style="text-align:left;">
						Enclosed, you will find the registration brochure and the first Hachayol Magazine 
						<br />
						for Elul 5771. 
					</DIV>
					<br />
-->					
					<? 
					if ( $h_school ) {
					    $cost = 60;
                        $subsidized = 10;
					} else {
					    $cost = 180;
                        $subsidized = 50;
					}
					?>
					
					<DIV style="text-align:left;">
						If you are new to our school and your child was previously registered in Tzivos Hashem and has an existing account, please let me know which school your child was previously in so your child’s account can be transferred to our school.
					</DIV>
					<br />
					<br />
					
					<DIV style="text-align:left;">
						Sincerely,
					</DIV>
					
					<DIV style="text-align:left;">
						<br />
						<?=$director_title;?> <?=$director_first;?> <?=$director_last;?> (Base Commander)
						<?
						if (!empty($director_phone)) {
						    echo "<br />";
                            echo $director_phone;
						}
                        ?>
					</DIV>
						
					<DIV style="height:100px;">
					</DIV>
					
					<DIV style="page-break-after:always">
					</DIV>
				<? endfor; ?>									
				
			</DIV>
<? else : ?>

			<FORM method="post" action="parent_children_barcodes.php">
			<input type="hidden" name="action" value="print">
			<?=$select;?>
			<input type="submit" value="GO">
			</FORM>
<? endif; ?>			

		
	</BODY>
	
</HTML>

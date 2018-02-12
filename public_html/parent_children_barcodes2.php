
<?php
$admin_auth = array('school');
require('header.php');
$admin_id = $admin_user['admin_id'];

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
	
	if ($school_id == 0) $school_id = $_POST['school_id'];
	
	// PROGRAM DIRECTOR //
	$sql = "SELECT a.first, a.last FROM admin_auths AS aa JOIN admins AS a USING (admin_id) WHERE aa.auth='school' AND aa.id=" . $school_id . " AND aa.role_id=18";
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$director_first = $row["first"];
	$director_last = $row["last"];
	
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
	$sql = $sql . "ORDER BY u.last, u.first";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$child = new user($row);
		$child->get_childs_parent();
		$child->get_school_class();
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
			<FORM name="setup_guide" method="post" action="admin_setup_guide.php">
				<input type="hidden" name="admin_id" value="<?=$admin_id;?>">
				<input type="button" value="RETURN" onclick="document.forms['setup_guide'].submit();">
			</FORM>
			</DIV>
			
			<br />
					
			<DIV style="position:absolute; left:50px;">
			
				<? for ($cno = 0; $cno < count($children); $cno++) : ?>
					<? $child = $children[$cno]; ?>
						
					<DIV style="text-align:left;">						
						To the parents of: <?=$child->first;?> <?=$child->last;?>
						<br />
						Grade: <?=$child->school_class->class_grade;?> Class: <?=$child->school_class->class_sub;?>
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
						We are thrilled to announce that our school, together with schools world wide, will 
						<br />
						be participating in Chayolei Tzivos Hashem 5771.
					</DIV>
					<br />
					
					<DIV style="text-align:left;">
						We have simplified the program with additional campaigns that will be suitable 
						<br />
						for grades as low as Pre 1A.
					</DIV>
					<br />
					
					<DIV style="text-align:left;">
						New for this year!! 
					</DIV>
					<br />
					
					<DIV style="text-align:left;">
						You can now create your own account to register and manage your children, print 
						<br />
						and mark your children's missions as well as track their progress. 
					</DIV>
					<br />
					
					<DIV style="text-align:left;">
						To set up your account:
					</DIV>
					
					<DIV style="text-align:left;">
						&nbsp;&nbsp;&nbsp;&nbsp;1)Go to www.mashpia.com/register_parent.php
					</DIV>
					
					<DIV style="text-align:left;">
						&nbsp;&nbsp;&nbsp;&nbsp;2)Enter your personal info to create a login
					</DIV>
					
					<DIV style="text-align:left;">
						&nbsp;&nbsp;&nbsp;&nbsp;3)Add your child to your account by entering his/her 20 digit account number (above)
					</DIV>
					<br />
					
					<DIV style="text-align:left;">
						Enclosed, you will find the registration brochure and the first Hachayol Magazine 
						<br />
						for Tishrei. 
					</DIV>
					<br />
					
					<DIV style="text-align:left;">
						The program cost per child is well over $180. Thanks to the partnership of Merkos
						<br />
						L'Inyonei Chinuch, Tzivos Hashem Headquarters, the Rohr Family, Anash.com
						<br />
						and generous donations from excited members of our community; your child is
						<br />
						able to join the program for a greatly subsidized price. 
					</DIV>
					<br />
					
					<DIV style="text-align:left;">
						For your convenience, you can register your child with cash, check or credit card.
					</DIV>
					<br />
					
					<DIV style="text-align:left;">
						To pay with cash or check: fill out the registration form and send it into your
						<br />
						school office. Please make checks payable to our school. 
					</DIV>
					<br />
					
					<DIV style="text-align:left;">
						To pay with credit card: create your own account (see above). 
					</DIV>
					<br />
					<br />
					
					<DIV style="text-align:left;">
						Sincerely,
					</DIV>
					
					<DIV style="text-align:left;">
						Your Program Director
						<br />
						<?=$director_first;?> <?=$director_last;?>
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

<?
$admin_auth = array('school'); 
require('header.php');
?>
<!doctype html public "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Send Weekly Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 6px;
                border: 1px solid black;
                vertical-align: middle;
            }
            td.heading {
            	height: 30px;
            	color: white;
            	background-color: black;
            	font-size: 14px;
            }
            .sticker {
            	text-align: center;
            }
            .sticker img {
            	height: 40px;
            }
            td.total {
            	text-align: center;
            	width: 80px;
            }
            div.info {
            	line-height: 1.4;
            	margin-bottom: 10px;
            }
        </style>
    </head>
    
    <body>
		<?
		//get school
		require_once 'class.adminSchools.php';  
		$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
		$schools = $as->getSchools();
		
		require_once 'class.parshos.php';
		$parshos = Parshos::getParshos(5774);
		
		if (isset($_POST['date']) && (count($schools) == 1 || (isset($_POST['school']) && $_POST['school'] > 0))) {
			$school_id = $_POST['school'];
			$dates = array();
			foreach ($_POST['date'] as $date) {
				$dates[] = $date;
			}
			
			//get parents of school that have an account
			$parents = array();
			$sql = "select distinct a.admin_id, a.admin_email from admins a 
					join admin_auths aa using (admin_id) 
					where aa.id in (
						select user_id from users where user_registered > 0 and school_id = $school_id
					) 
					and aa.auth = 'user'";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$parents[$row['admin_id']] = $row['admin_email'];
			}
			
			//echo "<pre>"; print_r($parents); echo "</pre>"; exit;
			
			//create report per parent for each child in school
			require_once 'classes/admin.php';
			require_once 'class.personalizedReport.php';
			foreach ($parents as $parent => $email) {
				$sql = "SELECT * FROM admins WHERE admin_id=" . $parent;
				$query = mysql_query($sql);
				$row = mysql_fetch_assoc($query);
				$admin = new \classes\admin($row);
				$admin->get_markable_children();
				
				$children = array();
				$childrenInfo = array();
				foreach ($admin->children as $child) {
					if (!empty($child->school_id) && !empty($child->class_id) && $child->school_id == $school_id) {
						$children[] = $child->user_id;
						$childrenInfo[$child->user_id] = (array)$child;
						//echo "<pre>"; print_r((array)$child); echo "</pre>";
					}
				}
				
				foreach ($dates as $date) {
					$start = $date;
					$end = $date + 6;
					$r = new PersonalizedReport($start, $end, $children);
					//ob_start();
					$r->createReport($childrenInfo);
					//$report = ob_get_contents();
					//echo $report;
					//ob_end_clean();
					
					//create and send email to parent
				}
			}
		} else {
			echo "<form action='sendWeeklyReport.php' method='post'>";
			if (count($schools) > 1) {
				echo "<select name='school'>";
				echo "<option value='0'>Select School</option>";
				foreach ($schools as $id => $school) {
					echo "<option value='$id'>" . $school . "</option>";
				}
				echo "</select><br />";
			} else {
				$keys = array_keys($schools);
				echo "<input type='hidden' name='school' value='" . $keys[0] . "' />";
			}
			
			$d = unixtojd();
			$day = date("N");
			$end = $d;
			
			switch ($day) {
			    case 1: //Monday
			        $end -= 2;
			        break;
			    case 2:
			        $end -= 3;
			        break;
			    case 3:
			        $end -= 4;
			        break;
			    case 4:
			        $end -= 5;
			        break;
			    case 5:
			        $end -= 6;
			        break;
			    case 6:
			        $end = $d;
			        break;
			    case 7: //Sunday
			    	$end -= 1;
			        break;
			    default:
			        break;
			}
			$start = $end - 6;
			
			echo "<select name='date[]' multiple>";
			foreach ($parshos as $parsha) {
				echo "<option value='" . $parsha['start'] . "'";
				if ($parsha['start'] == $start) {
					echo " selected='selected'";
				}
				echo ">" . $parsha['name'] . "</option>";				
			}
			echo "</select><br />";
			echo "<input type='submit' name='submit' value='create' />"; 
			echo "</form>";
		} 
		?>
	</body>
</html>
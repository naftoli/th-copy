<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Student Prizes</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Student Prizes</h1>
        <? 
        require_once 'class.adminSchools.php';
        require_once 'class.schoolsUsers.php';         
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $schoolsUsers = array();
        
        foreach ( $schools as $id => $school ) {
            $s = new SchoolsUsers( $id );
            $schoolsUsers[$id] = $s->getUsers();
        }
        
        foreach ( $schoolsUsers as $school => $users ) {
        	$prizes = array();
        	$user_prizes = array();
        	$sql = "select u.user_id, u.last, u.first, c.class_grade, c.class_sub, 
        			aup.prize_id, p.prize_name, aup.quantity
        			from auction_user_prizes aup 
        			join prizes_auction p using (prize_id) 
        			join users u using (user_id) 
        			join classes c on c.class_id = u.class_id 
					where aup.auction_id = 63 
					and u.school_id = $school 
					order by p.prize_name, c.class_grade, c.class_sub, u.last, u.first";
			//echo $sql;
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$prizes[$row['prize_id']] = $row['prize_name'];
				$user_prizes[$row['prize_id']][$row['user_id']] = $row;
			}
			
			?>
			<table>
				<caption><?=(count($schools) > 1 ? $schools[$school] : '')?></caption>
				<tr>
					<th>Prize ID</th>
					<th>Prize</th>
					<th>Grade</th>
					<th>Student ID</th>
					<th>Student</th>
					<th>Tickets Entered</th>
				</tr>
				<?
				foreach ($user_prizes as $prize => $info) {
					foreach ($info as $user_id => $data) {
						echo "<tr><td>" . $prize . "</td><td>" . $prizes[$prize] . "</td><td>" . 
							 $data['class_grade'] . ($data['class_sub'] ? '-' . $data['class_sub'] : '') . 
							 "</td><td>" . $user_id . "</td><td>" . $data['first'] . ' ' . $data['last'] . 
							 "</td><td>" . $data['quantity'] . "</td></tr>"; 
					}
				}
				?>
			</table>
			<?
		}
		?>
	</body>
</html>
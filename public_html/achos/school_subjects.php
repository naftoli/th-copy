<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>School Subjects Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
                vertical-align: top;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>School Subjects Report</h1>
        <? 
        require_once 'class.adminSchools.php';       
        $as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
        $schools = $as->getSchools();
        
        $schoolCampaigns = array();
        $sql = "select sc.school_name, s.subject_name, u.first, u.last, ut.enrolled from subjects s 
                join school_subjects ss using (subject_id) 
                join schools sc using (school_id) 
                join users u using (school_id) 
                join user_tracks ut on (ut.user_id = u.user_id and ut.subject_id = s.subject_id) 
                where s.subject_type in ('', 'WWTC') 
                and s.subject_id not in (21, 27)";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $schoolCampaigns[$row['school_name']][$row['subject_name']][$row['first'] . ' ' . $row['last']] = $row['enrolled'];
        }
        
        foreach ($schoolCampaigns as $school => $campaigns) {
            echo "<h2>" . $school . "</h2>";
            echo "<table>";
            echo "<tr><th>Subject</th><th>Students</th>";
            foreach ($campaigns as $campaign => $users) {
                echo "<tr><td>" . $campaign . "</td><td>";
                foreach ($users as $user => $enrolled) {
                    if ($enrolled)
                        echo $user . "<br />";
                }
                echo "</td></tr>";
            }
            echo "</table>";
        }
        echo "<br />";
        ?>
    </body>
</html>
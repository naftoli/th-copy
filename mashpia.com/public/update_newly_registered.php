<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Newly Registered Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script type="text/javascript">
            $( function() {
                $.each($("td").find("input").length, function() {
                    $(this).attr('text-align', 'center');
                });
            });            
        </script>
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
                vertical-align: top;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Newly Registered Report</h1>
        <? 
        require_once 'class.adminSchools.php';
        require_once 'class.schoolsUsers.php';        
       
        $as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
        $schools = $as->getSchools();
        $schoolsUsers = array();
        $totals = array();
        
        foreach ($schools as $id => $school) {
            $s = new SchoolsUsers($id);
            $schoolsUsers[$id] = $s->getUsers();
        }
        
        $reg_year = 5774; 
        foreach ($schoolsUsers as $school => $users) {
            foreach ($users as $user) {
                if ($user['user_start_date'] > 2456522) {
                    $sql = "insert into newly_joined values($user[user_id], $user[user_start_date], null, null)";
                    //echo $sql . "<br />";
                    mysql_query($sql);
                } 
            }    
        }        
        ?>
    </body>
</html>
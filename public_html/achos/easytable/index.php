<?php
ini_set('display_errors',1);
$admin_auth = array('school'); 
require('../header.php');

foreach ($_POST as $k => $v) {
    if (!is_array($v)) {
        $_POST[$k] =  mysql_real_escape_string($v);
    } else {
        foreach ($v as $key => $val) {
            $_POST[$k][$key] = mysql_real_escape_string($val);
        }
    }
}

//echo "<pre>"; print_r($_POST); echo "</pre>";

$start = $_POST['from'];
$end = $_POST['to'];
$grades = $_POST['grade'];
if ($grades[0] == 0) $allClasses = true;
else $allClasses = false;

$subjects = array();
if (isset($_POST['hoo'])) $subjects[] = 2;
if (isset($_POST['fc'])) $subjects[] = 3;
if (isset($_POST['personal'])) $subjects[] = 4;

$data = array();
if (isset($_POST['visits'])) $data[] = 'visits';
if (isset($_POST['points'])) $data[] = 'points';
if (isset($_POST['minutes'])) $data[] = 'minutes';

$personalInfo['emails'] = isset($_POST['emails']) ? 1 : 0;
$personalInfo['numbers'] = isset($_POST['numbers']) ? 1 : 0;

//$sortby = $_POST['sortby'];

// build sql based on options chosen
// get parshos
$parshos = array();
$sql = "select * from parshos where start >= " . $start . " and end <= " . $end;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $parshos[] = $row;
}

// get users signed up to chosen subjects
$users = array();
$sql = "select * from users u
        join user_tracks ut using (user_id)
        join classes c on u.class_id = c.class_id 
        where ut.subject_id in (" . implode(',', $subjects) . ")
        and u.school_id = " . $admin_user['auths']['school'][0];
if (!$allClasses) $sql .= " and u.class_id in (" . implode(',', $grades) . ")";
$sql .= " order by class_grade, class_sub, last, first";
//echo $sql; exit;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}
//echo "<pre>"; print_r($users); echo "</pre>"; exit;
if (count($users)) {
    $userInfo = array();
    $sql = "select u.user_id, u.first, u.last, c.class_grade, c.class_sub, ut.subject_id 
            from classes c
            join users u using (class_id)
            join user_tracks ut using (user_id) 
            where u.user_id in (" . implode(',', $users) . ")";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $userInfo[$row['user_id']]['name'] = $row['first'] . ' ' . $row['last'];
        $userInfo[$row['user_id']]['class'] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $subject = 0;
        switch ($row['subject_id']) {
            case 2:
                $subject = "Hoo";
                break;
            case 3:
                $subject = "FC";
                break;
            case 4:
                $subject = "Personal";
                break;
        }
        if (!$subject) continue;
        $userInfo[$row['user_id']]['subject'] = $subject;
    }
    
    if (count($personalInfo)) {
        foreach ($users as $user) {
            $sql = "select a.admin_email, a.admin_phone_mobile
                    from admins a
                    join admin_auths aa using (admin_id)
                    where aa.auth = 'user'
                    and aa.role_id = 1 
                    and aa.id = " . $user;
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $userInfo[$user]['email'] = $row['admin_email'];
                $userInfo[$user]['phone'] = $row['admin_phone_mobile'];
            }
        }
    }
    
    $info = array();
    require '../mobile/classes/hoo.php';
    foreach ($users as $user) {
        $h = new Hoo($user);
        foreach ($parshos as $parsha) {
            foreach ($data as $val) {
                $fn = 'calc' . ucwords($val);
                $info[$user][$parsha['name']][$val] = $h->$fn($parsha['start'], $parsha['end']);
            }
        }
    }
    //echo "<pre>"; print_r($info); echo "</pre>"; exit;
    $showTotals = $_POST['totals'];
} else {
    echo "There are no students that match your criteria.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>Class Report</title>
      <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.12/css/jquery.dataTables.css">
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
      <script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.js"></script>
      <!--
      <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"  rel="stylesheet">
      <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css"  rel="stylesheet">
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
      <link href="easyTable.css"  rel="stylesheet">
      -->
      <style>
        body {
            font-size: 12px;
        }
        td {
            text-align: center;
        }
        .container {
            padding-left: 4%;
            padding-right: 5%;
        }
      </style>
   </head>
   <body>

      <br>
      <div class="container">
         <div class="panel panel-default">
            <div class="panel-heading">
               <h3 class="panel-title">Class Report</h3>
            </div>
            <div class="panel-body">
               
                <table id="table">
                    <thead>
                        <th>Class</th>
                        <th>Student</th>
                        <th>Type</th>
                        <?php if ($personalInfo['emails']) : ?>
                        <th>Email</th>
                        <? endif; ?>
                        <?php if ($personalInfo['numbers']) : ?>
                        <th>Phone Number</th>
                        <? endif; ?>
                        <?php
                        if ($showTotals == 1) {
                            foreach ($parshos as $parsha) {
                                foreach ($data as $val) {
                                    echo "<th>" . $parsha['name'] . "<br />" . $val . "</th>";
                                }
                            }
                        }
                        ?>
                        <?php
                        if ($showTotals) {
                            foreach ($data as $val) {
                                echo "<th>Total " . $val . "</th>";
                            }
                        }
                        ?>
                    </thead>
                    <tbody>
                    <?php
                    $totals = array();
                    foreach ($info as $user => $more) {
                        if (!isset($userInfo[$user])) continue;
                        $totals[$user] = array();
                        echo "<tr><td>" . $userInfo[$user]['class'] . "</td><td>" . $userInfo[$user]['name'] . "</td><td>" .
                            $userInfo[$user]['subject'] . "</td>";
                        if ($personalInfo['emails']) echo "<td>" . $userInfo[$user]['email'] . "</td>";
                        if ($personalInfo['numbers']) echo "<td>" . $userInfo[$user]['phone'] . "</td>";
                        foreach ($more as $parsha => $other) {
                            foreach ($data as $val) {
                                if ($showTotals == 1) echo "<td>" . $other[$val] . "</td>";
                                if (isset($totals[$user][$val])) $totals[$user][$val] += $other[$val];
                                else $totals[$user][$val] = $other[$val];
                            }
                        }
                        if ($showTotals) {
                            foreach ($data as $val) {
                                echo "<td>" . $totals[$user][$val] . "</td>";
                            }
                        }
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>

            </div>
         </div>
      </div>

      <!--<script src="easyTable.js"></script>-->
      <script>
        $('#table').DataTable({
            paging : false
        });
        /*
         $("#table").easyTable({
            buttons: false
         });
         */
      </script>
   </body>
</html>

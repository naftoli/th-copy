<?php
chdir('../');
require_once 'db.php';
$id = mysql_real_escape_string($_POST['school']);

require_once 'class.shabbosMevorchim.php';
$sm = new ShabbosMevorchim();
$sm->setReportDates();
$reportDates = $sm->getReportDates();
$date = end($reportDates);
$key = key($reportDates);
$sm->setSchoolResults( $id, true );
$results = $sm->getSchoolResults();
$doneResults = $sm->getSchoolDoneResults();
$sm->setStudentResults($id);
$doneQuotas = $sm->getDoneQuotas();
//echo "<pre>"; print_r($doneQuotas); echo "</pre>";
//echo "<pre>"; print_r($results); print_r($doneResults); echo "</pre>";
$sql = "select count(*) as total from users u 
        join user_tracks ut using (user_id)
        where u.user_registered > 0
        and u.school_id = " . $id . "
        and ut.subject_id = 1
        and ut.enrolled = 1";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$totalUsers = $row['total'];
?>
<style>
    .liveStats tr, th, td {
        font-size: 12px;
        padding: 5px;
        vertical-align: top;
    }
    .liveStats tr:first-child {
        border-bottom: 1px dashed grey;
        background:rgba(255,255,120,0.5);
    }
    .liveStats td {
        background:rgba(255,255,62,0.5);
    }
    .liveStats p {
        font-size: 18px;
        font-weight: bold;
        color: red;
        margin-top: 0;
        margin-bottom: 0;
    }
</style>
<div class="liveStats" align="center">
    <p>
        Your Stats from Shabbos Mevorchim <?=$key?>
    </p>
    <table>
        <tr>
            <th># Chayolim</th>
            <th># Chayolim<br />Completed Quota</th>
            <th>Kapitelach<br />Base Goal</th>
            <th>Kapitelach<br />Accomplished</th>
            <th>Minutes<br />Base Goal</th>
            <th>Minutes<br />Accomplished</th>
        </tr>
        <tr>
            <td><?=$totalUsers?></td>
            <td><?=$doneQuotas['Kapitelach'][$id]?></td>
            <td><?=$results['Kapitelach'][$date]?></td>
            <td><?=$doneResults['Kapitelach'][$date]?></td>
            <td><?=$results['Minutes'][$date]?></td>
            <td><?=$doneResults['Minutes'][$date]?></td>
        </tr>
    </table>
</div>
<?php $debug = false;
/***************** DEBUGGING **********************/
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
    echo "<pre>";
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** IMPORTS **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/class.schoolsUsers.php';

/***************** LOAD USERS **********************/
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
$schoolsUsers = array();
// for each school get its users
foreach ( $schools as $id => $school ) {
    $s = new SchoolsUsers( $id );
    $schoolsUsers[$id] = $s->getUsers(true, true);
}

function get_user_email($user_id){
    $query = mysql_query("SELECT admin_email FROM admins JOIN admin_auths aa USING (admin_id) WHERE aa.auth ='user' and id=$user_id");
    if(mysql_num_rows($query) == 0){
        return "N/A";
    } else {
        return mysql_fetch_assoc($query)['admin_email'];
    }
}

function get_school_user_emails($school_id){
    $sql = "SELECT user_id, admin_email FROM admins JOIN admin_auths aa USING (admin_id) JOIN users ON aa.id=users.user_id AND aa.auth ='user' WHERE users.school_id=$school_id";
    $query = mysql_query($sql);
    $result = [];
    while($row = mysql_fetch_assoc($query)) {
        $result[$row['user_id']] = $row['admin_email'];
    }
    return $result;
}

//if($debug) print_r($schoolsUsers);
if($debug) echo "</pre>";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Student - Registered Soldier Parent Account Email Report</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <style>
            .options{text-align: center;} .options a{display: inline-block;}
            table {width: 100%;}td{padding: 4px 8px;}
        </style>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    </head>
    <body>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
        
        <h1>Student - Parent Email Report</h1>
        
        <div class="options">
            <a class="button" id="generate_csv"><i class="fa fa-save" aria-hidden="true"></i> Export to CSV</a>
        </div>
        <div id="report">
            <? foreach($schools as $school_id => $school_name) {
                $emails = get_school_user_emails($school_id);?>
                <h2><?=$school_name?></h2>
                <table>
                    <thead>
                        <th>Serial</th><th>Name</th><th>Grade</th><th>Email (Parent)</th>
                    </thead>
                    <tbody>
                        <? foreach($schoolsUsers[$school_id] as $user) {?>
                        <tr class="users">
                            <td><?=$user['user_serial']?></td>
                            <td><?=$user['first']?>, <?=$user['last']?></td>
                            <td><?=$user['class_grade'] . ($user['class_sub'] ? " - ".$user['class_sub'] : "")?></td>
                            <td><?=isset($emails[$user['user_id']]) ? $emails[$user['user_id']] : "N/A";?></td>
                        </tr>
                        <?}?>
                    </tbody>
                </table>
            <?}?>
        </div>
        <script>
        var debug = <?= $debug ? "true" : "false" ?>;
        $(document).ready(function(){
           $("#generate_csv").click(generate_csv);
        });
        
        function generate_csv() {
            alert("Please note that is feature is in BETA and may not work correctly in all readers. It has been only been tested with Microsoft Excel 2016 and your milage may vary.");
            var rows = []; // the rows for the csv export
            var csvContent = "Serial,First,Last,Grade,Email\n"; // the baisc csv file
            var universalBOM = "\uFEFF";
            // TODO add headers
            $.each($("tr.users"), function(index, item) {
                item = $(item); // cast to jquery;
                var row = [];
                $.each(item.find("td"), function(index, item) {
                    row.push($(item).text());
                });
                rows.push(row); // add the row to the csv export
                row = row.join(",");
                csvContent += row + "\n";
            });
            
            var hiddenElement = document.createElement('a');
            hiddenElement.href = "data:text/csv;charset=utf-8," + encodeURIComponent(universalBOM+csvContent); // set the data
            hiddenElement.target = '_blank'; // in a new tab
            hiddenElement.download = 'email-report.csv'; // with this file_name
            hiddenElement.click(); // and click it
        }
        </script>
    </body>
</html>
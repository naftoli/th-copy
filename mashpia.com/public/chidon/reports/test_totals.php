<?php
//echo "Needs update for this report.";
//exit;

$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

require 'vars.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grades = $_POST['grade'];
    $gender = mysql_real_escape_string($_POST['gender']);
    
    $info = [];
    $sql = "SELECT test_table, grade, school_rep, test_lang, COUNT(*) as total "
        ." FROM th_chidon tc LEFT JOIN users u USING (user_id) "
        ." WHERE grade IN ($grades) AND tc.year = '$year' AND date_paid IS NOT NULL AND u.gender = '$gender' "
        ." GROUP BY test_table, grade, school_rep, test_lang "
        ." ORDER BY test_table, grade, school_rep; ";
    // echo $sql;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info[$row['test_table']][] = $row;
    }
    ?>
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="UTF-8" />
            <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
            <style>
                body {
                    font-family: Arial, Helvetica, sans-serif;
                    font-size: 14px;
                }
                caption {
                    font-size: 20px;
                    font-weight: bold;
                }
                table {
                    page-break-after: always;
                    width: 700px;
                    margin: 50px auto 40px;
                    font-size: 1.5em;
                }
                td {
                    text-align: center;
                    border: 1px solid;
                }
            </style>
        </head>
        
        <body>
            <?php
            foreach($info as $test_table => $students) { ?>
                <table>
                    <caption>Test Table: <?= $test_table !== 0 ? $test_table : "Upstairs" ?></caption>
                    <tr>
                        <th>Test Table</th><th>Grade</th><th>Type</th><th>Language</th><th>Total</th>
                    </tr>
                    <?php foreach($students as $student) {?>
                    <tr>
                        <td><?=$student['test_table']?></td>
                        <td><?=$student['grade']?></td>
                        <td><?=$student['school_rep'] ? "Representative" : "Contestant"?></td>
                        <td><?=$student['test_lang'] == "yi" ? "Yiddish" : "English"?></td>
                        <td><?=$student['total']?></td>
                        <td><i class="fa fa-square-o" aria-hidden="true"></i></td>
                    </tr>
                    <?php } ?>
                </table>
            <?php  
            } // end foreach table ?>
            
        </body>
    </html>
<?php }  else { ?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Test Totals</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
    </head>
    </head>
    
    <body>
        <?php // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Generate Test Totals</h1>
        <form method="post">
            <label for="grade">Grades</label>
            <select id="grade" name="grade">
                <option value="'4','5'">4 - 5th</option>
                <option value="'6','7','8'">6 - 8th</option>
            </select> <br/>
            
            <label for="gender">Gender</label>
            <select id="gender" name="gender">
                <option value="M">Boys</option>
                <option value="F">Girls</option>
            </select> <br/>
            
            <input type="submit" />
        </form>
    </body>
</html>

<?php } ?>
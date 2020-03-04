<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
if ( $admin_user['auth'] != 'super' ) {
    echo "No Permission.";
    exit;
}

if ( isset( $_POST['submit'] ) ) {
    // echo "<pre>"; print_r( $_POST ); echo "</pre>";
    if ( !(isset( $_POST['grade'] ) && isset( $_POST['cat'] ) && isset( $_POST['gender'] )) ) {
        echo "You must choose at least one grade, category and gender.";
        exit;
    }

    // secure values
    foreach ( $_POST['grade'] as $k => $val ) {
        $_POST['grade'][$k] = mysql_real_escape_string( $val );
    }

    require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
    $year = GlobalSettings::getChidonYear();

    $grades = "'" . implode("','", $_POST['grade']) . "'";
    $genders = "'" . implode("','", $_POST['gender']) . "'";
    $stmt = $MASHPIA_DB->prepare("
        SELECT 
            u.gender, tc.*
        FROM
            th_chidon tc
                JOIN
            users u USING (user_id)
        WHERE
            grade IN ($grades) AND year = 5780 
                AND deleted = 0
                AND date_paid > 0 
                AND u.gender IN ($genders)
        ORDER BY
            grade
    ");
    $stmt->execute([':year' => $year]);
    $rows = $stmt->fetchAll();

    // create summary
    $info = [];
    foreach ( $rows as $row ) {
        foreach ( $_POST['cat'] as $cat ) {
            if ( intval($row[$cat]) == 1 ) {
                if ( isset( $info[$row['gender']][$row['grade']][$cat] ) ) $info[$row['gender']][$row['grade']][$cat]++;
                else $info[$row['gender']][$row['grade']][$cat] = 1;
                break;
            }
        }
    }
    // echo "<pre>"; print_r( $info ); echo "</pre>";
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                font-family: Arial;
                font-size: 14px;
                padding: 5px;
            }
            fieldset {
                padding: 10px;
                border-radius: 20px;
                border-color: #DBDBDB;
            }
            legend {
                padding: 10px;
                margin-left: 20px;
            }
        </style>
    </head>
    <body>
        <?php if ( !isset( $_POST['submit'] ) ) : ?>
            <form action="shabbaton_summary.php" method="post">
                <fieldset>
                    <legend>Choose Grades</legend>
                    <?php
                    for ($i = 4; $i < 9; $i++) {
                        echo "<input type='checkbox' name='grade[]' value='" . $i . "' /> Grade: " . $i . "<br />";
                    }
                    ?>  
                </fieldset>
                <fieldset>
                    <legend>Choose Categories</legend>
                    <?php
                    $categories = ['khk', 'school_rep', 'trophy_contestant', 'contestant'];
                    foreach ( $categories as $cat ) {
                        echo "<input type='checkbox' name='cat[]' value='" . $cat . "' /> " . $cat . "<br />";
                    }
                    ?>  
                </fieldset>
                <fieldset>
                    <legend>Choose Gender</legend>
                    <input type="checkbox" name="gender[]" value='M' /> Boys<br />
                    <input type="checkbox" name="gender[]" value='F' /> Girls<br />
                </fieldset>
                <br />
                <input type="submit" name="submit" value="submit" />
            </form>
        <?php else : ?>
            <?php foreach ( $info as $gender => $more ) : ?>
                <h1>
                <?php
                if ( $gender == 'M' ) echo 'Boys';
                else if ( $gender == 'F' ) echo 'Girls';
                ?>
                </h1>
                <table>
                    <tr>
                        <th>Grade</th>
                        <th>Category</th>
                        <th>Total</th>
                    </tr>
                    <?php
                    $grandTotal = 0;
                    foreach ( $more as $grade => $other ) {
                        $gTotal = 0;
                        foreach ( $other as $cat => $total ) {
                            $gTotal += $total;
                            $grandTotal += $total;
                            echo "<tr><td>" . $grade . "</td><td>" . $cat . "</td><td>" . $total . "</td></tr>";
                        }
                        echo "<tr><th colspan='2' style='text-align: right;'>Total:</th><th>" . $gTotal . "</th></tr>";
                    }
                    echo "<tr><th colspan='2' style='text-align: right;'>Grand Total:</th><th>" . $grandTotal . "</th></tr>";
                    ?>
                </table>
            <?php endforeach; ?>
        <?php endif; ?>
    </body>
</html>
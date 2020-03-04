<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
if ( $admin_user['auth'] != 'super' ) {
    echo "No Permission.";
    exit;
}

if ( isset( $_POST['submit'] ) ) {
    // echo "<pre>"; print_r( $_POST ); echo "</pre>";
    if ( !(isset( $_POST['cat'] ) && isset( $_POST['gender'] )) ) {
        echo "You must choose at least one category and gender.";
        exit;
    }

    // secure values
    // foreach ( $_POST['grade'] as $k => $val ) {
    //     $_POST['grade'][$k] = mysql_real_escape_string( $val );
    // }

    require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
    $year = GlobalSettings::getChidonYear();

    // $grades = "'" . implode("','", $_POST['grade']) . "'";
    $cats = implode(',', $_POST['cat']);
    $genders = "'" . implode("','", $_POST['gender']) . "'";
    $stmt = $MASHPIA_DB->prepare("
        SELECT 
            *
        FROM
            th_chidon_chaps
        WHERE
            year = :year AND chidon_type in ($genders) 
                AND chap_type in ($cats)
    ");
    $stmt->execute([':year' => $year]);
    $rows = $stmt->fetchAll();

    // create summary
    $info = [];
    foreach ( $rows as $row ) {
        switch ( intval($row['chap_type']) ) {
            case 1:
                $type = "Chaperone";
            break;
            case 2:
                $type = "Walking Supervisor";
            break;
            case 3:
                $type = "Principal";
            break;
            case 4:
                $type = "Other";
            break;
        }
        if ( isset( $info[$row['chidon_type']][$type] ) ) $info[$row['chidon_type']][$type]++;
        else $info[$row['chidon_type']][$type] = 1;
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
            <form action="school_staff_summary.php" method="post">
                <!-- <fieldset>
                    <legend>Choose Grades</legend>
                    <?php
                    for ($i = 4; $i < 9; $i++) {
                        echo "<input type='checkbox' name='grade[]' value='" . $i . "' /> Grade: " . $i . "<br />";
                    }
                    ?>  
                </fieldset> -->
                <fieldset>
                    <legend>Choose Categories</legend>
                    <?php
                    $categories = [
                        1 => 'Chaperone', 
                        2 => 'Walking Supervisor', 
                        3 => 'Principal', 
                        4 => 'Other'
                    ];
                    foreach ( $categories as $val => $cat ) {
                        echo "<input type='checkbox' name='cat[]' value='" . $val . "' /> " . $cat . "<br />";
                    }
                    ?>  
                </fieldset>
                <fieldset>
                    <legend>Choose Gender</legend>
                    <input type="checkbox" name="gender[]" value='boys' /> Boys<br />
                    <input type="checkbox" name="gender[]" value='girls' /> Girls<br />
                </fieldset>
                <br />
                <input type="submit" name="submit" value="submit" />
            </form>
        <?php else : ?>
            <?php foreach ( $info as $gender => $more ) : ?>
                <h1><?= ucwords($gender); ?></h1>
                <table>
                    <tr>
                        <th>Category</th>
                        <th>Total</th>
                    </tr>
                    <?php
                    foreach ( $more as $cat => $total ) {
                        echo "<tr><td>" . $cat . "</td><td>" . $total . "</td></tr>";
                    }
                    ?>
                </table>
            <?php endforeach; ?>
        <?php endif; ?>
    </body>
</html>
<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
if ( $admin_user['auth'] != 'super' ) {
    echo "No Permission.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/PHPExcel/IOFactory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$year = GlobalSettings::getChidonYear();

$tracks = [
    'yesod'     => 'maven',
    'yediah'    => 'pro',
    'havanah'   => 'expert',
    'iyun'      => 'genius'
];

$chidon_prizes = [];
$prize_qry = $MASHPIA_DB->prepare("select * from chidon_prizes where year = :year");
$prize_qry->execute([':year' => $year]);
foreach ($prize_qry->fetchAll() as $prize) {
    $chidon_prizes[$prize['prize_id']] = empty($prize['personalization']) ? 0 : 1;
}

if ( isset( $_FILES['file'] ) ) {
    // prepared statements
    $schoolStmt = $MASHPIA_DB->prepare("SELECT * FROM non_th_schools WHERE school_name = ':school'");
    $userStmt = $MASHPIA_DB->prepare("select user_id, school_id from users where user_serial = :serial");
    $adminStmt = $MASHPIA_DB->prepare("select admin_id from admin_auths where id = :user_id");
    $chidonStmt = $MASHPIA_DB->prepare("insert into th_chidon 
                                              set year = :year, 
                                              school_id = :school_id, 
                                              user_id = :user_id, 
                                              yarmulka = :yarmulka,
                                              size = :size, 
                                              parent_id = :parent_id,
                                              test_type = :type, 
                                              book = :book, 
                                              name_pref = :name, 
                                              reg_date = now()");

    $prizeStmt1 = $MASHPIA_DB->prepare("insert ignore into chidon_user_prizes 
                                    set user_id = :user, prize_id = :prize, year = :year
                                ");
    $prizeStmt2 = $MASHPIA_DB->prepare("insert ignore into chidon_user_prizes 
                                    set user_id = :user, prize_id = :prize, year = :year, he_name = :name
                                ");

    $userStmt1 = $MASHPIA_DB->prepare("UPDATE users SET non_th_school_id = :id WHERE user_id = :user");
    $userStmt2 = $MASHPIA_DB->prepare("UPDATE users SET non_th_school = :school WHERE user_id = :user");

    $regStmt = $MASHPIA_DB->prepare("INSERT INTO registration_charges
                                            SET user_id = :user, 
                                            school_id = :school, 
                                            type = 'chidon', 
                                            amount = :amount, 
                                            date = now(), 
                                            year = :year");

    $objPHPExcel = PHPExcel_IOFactory::load( $_FILES['file']['tmp_name'] );
    $objWorksheet = $objPHPExcel->getActiveSheet();

    $info = [];
    foreach ( $objWorksheet->getRowIterator() as $row ) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $values = [];
        foreach ( $cellIterator as $cell ) {
            $values[] = trim( $cell->getValue() );
        }
        $info[] = $values;
    }

//    echo "<pre>"; print_r( $info ); echo "</pre>";

    $MASHPIA_DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $MASHPIA_DB->beginTransaction();
    $success = true;
    $error = false;
    $updated = 0;
    $missingParentAccounts = [];

    $track = "pro";
    
    // loop through info array and register kids
    $i = 0;
    foreach ( $info as $values ) {
        echo "<pre>" . "$i: "; print_r($values); echo "</pre>";
        $user_serial = $values[$i++];
        $prizes = $values[$i++];
        $arrPrizes = explode(',', $prizes);
        $he_name = $values[$i++];
        $non_th_school = $values[$i++];
        $sweater = $values[$i++];
        $book = $values[$i++];
        $paid = $values[$i++];
        $name = $values[$i++];
        $yarmulka = $values[$i++];
        if (empty($yarmulka)) $yarmulka = 0;
//        $track = $values[$i++];
//        $learning_method = $values[$i++];

        // find out what the user id is
        if ( $userStmt->execute([':serial' => $user_serial]) ) {
            $result = $userStmt->fetch();
            $user_id = $result['user_id'];
            $school_id = $result['school_id'];

            if ( $user_id > 0 ) {
                // find out if non th school already exists and has an ID
                if ($schoolStmt->execute(['school' => $non_th_school])) {
                    if ($res = $schoolStmt->fetch()) {
                        $non_th_school_id = $result['non_th_school_id'];
                        $userStmt1->execute([
                            'id'    => $non_th_school_id,
                            'user'  => $user_id
                        ]);
                    } else {
                        $userStmt2->execute([
                            'school'  => $non_th_school,
                            'user'    => $user_id
                        ]);
                    }
                }

                if ( $adminStmt->execute([':user_id' => $user_id]) ) {
                    $result = $adminStmt->fetch();
                    if ( empty( $result ) ) {
                        $missingParentAccounts[] = $user_id;
                        continue;
                    }
                    $admin_id = $result['admin_id'];

                    if (
                        $chidonStmt->execute([
                            ':year'         =>  $year,
                            ':school_id'    =>  $school_id,
                            ':user_id'      =>  $user_id,
                            ':yarmulka'     =>  $yarmulka,
                            ':size'         =>  strtolower($sweater),
                            ':parent_id'    =>  $admin_id,
                            ':type'         =>  $track,
                            ':book'         =>  $book,
                            ':name'         =>  $name
                        ])
                    ) {
                        if (!
                            $regStmt->execute([
                                'user'    => $user_id,
                                'school'  => $school_id,
                                'year'    => $year,
                                'amount'  => $paid
                            ])
                        ) {
                            echo "problem creating registration charge.<br />";
                            $error = true;
                            break;
                        }

                        foreach ($arrPrizes as $prize) {
                            // if we need to add the he name
                            if ($chidon_prizes[$prize]) {
                                $res = $prizeStmt2->execute([
                                            ':user' => $user_id,
                                            ':prize' => $prize,
                                            ':year' => $year,
                                            ':name' => $he_name
                                        ]);
                            } else {
                                $res = $prizeStmt1->execute([
                                    ':user' => $user_id,
                                    ':prize' => $prize,
                                    ':year' => $year
                                ]);
                            }
                            if (!$res) {
                                echo "Can't insert into prizes.<br />";
                                $error = true;
                                break 2;
                            }
                        }
                        $updated++;
                    } else {
                        echo "Can't insert/update th_chidon.<br />";
                        $chidonStmt->debugDumpParams();
                        $error = true;
                        break;
                    }
                } else {
                    echo "Can't get Admin ID.<br />";
                    $error = true;
                    break;
                }
            }
        } else {
            echo "Can't get User ID.<br />";
            $error = true;
            break;
        }
        // set i back to 0
        $i = 0;
    }

    $success = !$error;

    if ( $success ) {
        $MASHPIA_DB->commit();
        echo "Successfully updated " . $updated . " entries.";
    } else {
        $MASHPIA_DB->rollBack();
        echo "Error updating.";
    }
    if ( !empty( $missingParentAccounts ) ) {
        echo "<br />Missing Parent Accounts:";
        echo "<pre>"; print_r( $missingParentAccounts ); echo "</pre>";
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
    </head>

    <body>
        <form action="register_for_chidon.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
            <br /><input type="file" name="file" class="file"><br />
            <br /><input type="submit" name="submit" value="upload" />
        </form>
    </body>
</html>
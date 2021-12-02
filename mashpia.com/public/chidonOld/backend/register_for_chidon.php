<?php
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

    //echo "<pre>"; print_r( $info ); echo "</pre>";

    $MASHPIA_DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $MASHPIA_DB->beginTransaction();
    $success = true;
    $error = false;
    $updated = 0;
    $missingParentAccounts = [];
    
    // loop through info array and register kids
    // index 0 is the user serial and index 1 is the school id
    $i = 0;
    foreach ( $info as $values ) {
        $user_serial = $values[$i++];
        $yarmulka = empty($values[$i++]) ? $values[1] : 0;
        $sweater = $values[$i++];
        $track = $values[$i++];
        $book = $values[$i++];
        $learning_method = $values[$i++];
        $school_name = $values[$i++];
        $prizes = $values[$i++];
        $prize_name = $values[$i++];

        // find out what the user id is
        if ( $handle = $MASHPIA_DB->prepare("select user_id, school_id from users where user_serial = :serial") ) {
            if ( $handle->execute([':serial' => $user_serial]) ) {
                $result = $handle->fetch();
                $user_id = $result['user_id'];
                $school_id = $result['school_id'];

                if ( $user_id > 0 ) {
//                    // first find out if user already is registered in chidon db
//                    if ( $handle = $MASHPIA_DB->prepare("select * from th_chidon where year = :year and user_id = :user") ) {
//                        if ( $handle->execute([
//                            ':year'     =>  $year,
//                            ':user'     =>  $user_id
//                        ]) ) {
//
//                            $found = $handle->fetch();
//                            if ( empty( $found ) ) {
                                if ( $handle = $MASHPIA_DB->prepare("select admin_id from admin_auths where id = :user_id") ) {
                                    if ( $handle->execute([':user_id' => $user_id]) ) {
                                        $result = $handle->fetch();
                                        if ( empty( $result ) ) {
                                            $missingParentAccounts[] = $user_id;
                                            continue;
                                        }
                                        $admin_id = $result['admin_id'];

                                        if ( $handle = $MASHPIA_DB->prepare("insert into th_chidon 
                                                                        set year = :year, 
                                                                        school_id = :school_id, 
                                                                        user_id = :user_id, 
                                                                        yarmulka = :yarmulka,
                                                                        size = :size, 
                                                                        parent_id = :parent_id,
                                                                        test_type = :type, 
                                                                        reward_type = :type, 
                                                                        book = :book,
                                                                        poll = :learning_method 
                                                                        on duplicate key update 
                                                                        school_id = :school_id, 
                                                                        yarmulka = :yarmulka,
                                                                        size = :size, 
                                                                        parent_id = :parent_id,
                                                                        test_type = :type, 
                                                                        reward_type = :type, 
                                                                        book = :book,
                                                                        poll = :learning_method 
                                                                        ") ) {
                                            if ( 
                                                !$handle->execute([
                                                    ':year'         =>  $year, 
                                                    ':school_id'    =>  $school_id, 
                                                    ':user_id'      =>  $user_id,
                                                    ':yarmulka'     =>  $yarmulka,
                                                    ':size'         =>  $sweater,
                                                    ':parent_id'    =>  $admin_id,
                                                    ':type'         =>  $tracks[strtolower($track)],
                                                    ':book'         =>  $book,
                                                    ':poll'         =>  $learning_method
                                                ]) 
                                            ) {
                                                $error = true;
                                                $success = false;
                                                break;
                                            } else {
                                                $stmt1 = $MASHPIA_DB->prepare("insert ignore into chidon_user_prizes 
                                                            set user_id = :user, prize_id = :prize, year = :year
                                                        ");
                                                $stmt2 = $MASHPIA_DB->prepare("insert ignore into chidon_user_prizes 
                                                            set user_id = :user, prize_id = :prize, year = :year, he_name = :name
                                                        ");
                                                foreach (explode(',', $prizes) as $prize) {
                                                    // if we need to add the he name
                                                    if ($chidon_prizes[$prize]) {
                                                        if (
                                                            $stmt2->execute([
                                                                ':user'     => $user_id,
                                                                ':prize'    => $prize,
                                                                ':year'     => $year,
                                                                ':name'     => $prize_name
                                                            ])
                                                        )
                                                            $updated++;
                                                        else {
                                                            $error = true;
                                                            break 2;
                                                        }
                                                    } else {
                                                        if (
                                                            $stmt1->execute([
                                                                ':user'     => $user_id,
                                                                ':prize'    => $prize,
                                                                ':year'     => $year
                                                            ])
                                                        )
                                                            $updated++;
                                                        else {
                                                            $error = true;
                                                            break 2;
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            $error = true;
                                        }
                                    } else {
                                        $error = true;
                                    }
                                } else {
                                    $error = true;
                                }
//                            }
//                        } else {
//                            $error = true;
//                        }
//                    } else {
//                        $error = true;
//                    }
                }
            } else {
                $error = true;
            }
        } else {
            $error = true;
        }
    }

    if ( $error ) {
        $success = false;
        echo "<pre>"; print_r( $MASHPIA_DB->errorInfo() ); echo "</pre>";
    }

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
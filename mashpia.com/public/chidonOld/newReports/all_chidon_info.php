<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission to be here.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/chidon_drive/site/enrollment/class.tripRegistration.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/coupons/class.couponCode.php';

$year = GlobalSettings::getChidonYear();
$ct = new ChidonTests($year);
$c = new CouponCode($MASHPIA_DB, $year);

function getChidonInfo($chidon_ids, $user_ids, $user_serials) {
    global $MASHPIA_DB, $year;

    if ($chidon_ids) {
        // remove extra whitespace from list of chidon IDs
        $chidon_ids = str_replace(' ', '', $chidon_ids);
        // make sure that there's no characters other than numbers or commas
        $chidon_ids = preg_replace('/[^0-9,]+/', '', $chidon_ids);
        $stmt = $MASHPIA_DB->prepare("
            SELECT *, u.school_id as school_id, tc.school_id as school_id_chidon 
            FROM th_chidon tc 
            JOIN users u USING (user_id) 
            JOIN classes USING (class_id) 
            WHERE year = :year AND th_chidon_id in ($chidon_ids)
        ");
        $stmt->execute([
            ':year' => $year,
        ]);
        $rows = $stmt->fetchAll();
        return $rows;
    } else if ($user_ids) {
        // remove extra whitespace from list of user IDs
        $user_ids = str_replace(' ', '', $user_ids);
        // make sure that there's no characters other than numbers and or commas
        $user_ids = preg_replace('/[^0-9,]+/', '', $user_ids);
        $stmt = $MASHPIA_DB->prepare("
            SELECT *, u.school_id as school_id, tc.school_id as school_id_chidon 
            FROM th_chidon tc 
            JOIN users u USING (user_id) 
            JOIN classes USING (class_id) 
            WHERE year = :year AND user_id in ($user_ids)
        ");
        $stmt->execute([
            ':year' => $year,
        ]);
        $rows = $stmt->fetchAll();
        return $rows;
    } else if ($user_serials) {
        // remove extra whitespace from list of user serials
        $user_serials = str_replace(' ', '', $user_serials);
        // make sure that there's no characters other than numbers and or commas
        $user_serials = preg_replace('/[^0-9,]+/', '', $user_serials);
        $stmt = $MASHPIA_DB->prepare("
            SELECT *, u.school_id as school_id, tc.school_id as school_id_chidon 
            FROM th_chidon tc 
            JOIN users u USING (user_id) 
            JOIN classes USING (class_id) 
            WHERE year = :year AND user_id in (
                SELECT user_id FROM users WHERE user_serial in ($user_serials)
            )
        ");
        $stmt->execute([
            ':year' => $year,
        ]);
        $rows = $stmt->fetchAll();
        return $rows;
    }
}

function setTracks(array &$info) {
    global $ct;

    $types = $ct->getTypes();
    foreach ($info as &$row) {
        // if the track was set already, skip
        if (! in_array($row['reward_type'], ['', ' ', 'highest track passed'])) {
            $track = $row['reward_type'];
        } else {
            $ct->setStudents($row['school_id'], $row['class_id'], $row['user_id']);
            $ct->setScores();
            $ct->calculateMarks();
            $marks = $ct->getMarks();
            if (isset($marks[$row['th_chidon_id']])) {
                $track = $ct->getHighestTrack($marks[$row['th_chidon_id']], $row['user_id']);
            }
        }
        $row['track_passed'] = array_key_exists($track, $types) ? $types[ $track ] : $track;
    }
}

function setKHK(array &$info) {
    $ids = [];
    foreach ($info as $row) {
        if (intval($row['class_grade']) == 8) $ids[] = $row['user_id'];
    }
    if ($ids) {
        $khk = KHK::getUltimateTripEligibility($ids)[0];
        foreach ($info as &$row) {
            $row['khk_override'] = $khk[$row['user_id']] ?? 0;
        }
    } else {
        foreach ($info as &$row) {
            $row['khk_override'] = 0;
        }
    }
}

function getExtraPurchases(array &$info) {
    global $MASHPIA_DB, $year;

    // extra purchases are in the table extra_purchases and if there's a payment for shipping, then the shipping 
    // address is in the table purchase_addresses
    $stmt = $MASHPIA_DB->prepare("
        SELECT * FROM extra_purchases 
        JOIN purchase_addresses USING (purchase_id) 
        WHERE year = :year AND admin_id = :admin
    ");
    foreach ($info as &$row) {
        $stmt->execute([
            ':year' => $year,
            ':admin' => $row['admin_id']
        ]);
        $purchases = $stmt->fetchAll();
        $row['extra_purchases'] = $purchases;
    }
}

function setFamilyBalance(array &$info) {
    global $MASHPIA_DB, $year;

     // find out admin id for each user
     $stmtAdmin = $MASHPIA_DB->prepare("
        SELECT admin_id FROM admin_auths 
        WHERE id = :id AND auth = 'user'
    ");

    // get info from family balance
    foreach ($info as &$row) {
        $stmtAdmin->execute([
            ':id' => $row['user_id']
        ]);
        $rowAdmin = $stmtAdmin->fetch();
        if ($rowAdmin) {
            $row['admin_id'] = $rowAdmin['admin_id'];
            $tr = new TripRegistration($row['admin_id'], $year);
            $balance = $tr->getFamilyBalance();
            $row['family_balance'] = $balance;
        } else {
            $row['family_balance'] = 0;
        }
    }
}

function setChidonDriveAmounts(array &$info) {
    global $MASHPIA_DB, $year;
    // get info from chidon drive
    $stmt = $MASHPIA_DB->prepare("
        SELECT IFNULL(SUM(subsidy_amount), 0) as raised 
        FROM chidon_user_subsidies 
        WHERE chidon_year = :year AND user_id = :user
    ");
    foreach ($info as &$row) {
        $stmt->execute([
            ':year' => $year,
            ':user' => $row['user_id']
        ]);
        $rowDrive = $stmt->fetch();
        if ($rowDrive['raised']) $row['raised'] = $rowDrive['raised'];
        else $row['raised'] = 0;
    }
}

function setPersonalCredit(array &$info) {
    global $MASHPIA_DB, $year;
    // get info from personal credit
    $stmtPersonalCredit = $MASHPIA_DB->prepare("
        SELECT IFNULL(SUM(amount), 0) as personal_credit 
        FROM registration_charges 
        WHERE year = :year AND user_id = :user 
            AND type in ('RRYSD', 'RRYDA', 'RRHVN')
    ");
    foreach ($info as &$row) {
        $stmtPersonalCredit->execute([
            ':year' => $year,
            ':user' => $row['user_id']
        ]);
        $rowCredit = $stmtPersonalCredit->fetch();
        if ($rowCredit['personal_credit']) $row['personal_credit'] = $rowCredit['personal_credit'];
        else $row['personal_credit'] = 0;
    }
}

function setCoupons(array &$info) {
    global $c;
    foreach ($info as &$row) {
        $row['coupon_code'] = $c->checkForUserCode($row['user_serial']);
    }
}

function getRegistrationCharges(array &$info) {
    global $MASHPIA_DB, $year;
    $stmt = $MASHPIA_DB->prepare("
        SELECT * FROM registration_charges 
        WHERE year = :year AND user_id = :user
    ");
    foreach ($info as &$row) {
        $stmt->execute([
            ':year' => $year,
            ':user' => $row['user_id']
        ]);
        $rows = $stmt->fetchAll();
        $row['charges'] = $rows;
    }
}

$info = [];
if (isset($_POST['chidon_ids']) || isset($_POST['user_ids']) || isset($_POST['user_serials'])) {
    $info = getChidonInfo($_POST['chidon_ids'], $_POST['user_ids'], $_POST['user_serials']);
    setTracks($info);
    setPersonalCredit($info);
    setCoupons($info);
    getRegistrationCharges($info);
    setChidonDriveAmounts($info);
    setFamilyBalance($info);
    setKHK($info);
    getExtraPurchases($info);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Chidon Info</title>
    <style>
        .tableInfo {
            float: left;
            margin: 10px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        /* Clear the float after the tables */
        div:has(> .tableInfo) {
            overflow: hidden;
        }
        
        .tableInfo table {
            margin-bottom: 10px;
        }
        
        .tableInfo td {
            padding: 5px;
        }
        
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 12px;
            padding: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        td {
            height: 40px;
        }
        button {
            padding: 10px;
            border: 1px solid #ccc;
            cursor: pointer;
        }
        input, select {
            padding: 8px;
            border: 1px solid #bbb;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- show form for entering a list of chidon IDs or a list of user IDs or a list of user serial numbers -->
    <h1>All Chidon Info</h1>
    <form action="all_chidon_info.php" method="post">
        <table class="selection">
            <tr>
                <th>Year</th>
                <td>
                    <select name="year" id="year">
                        <?php
                        $cur_yr = $year;
                        $req_yr = $_POST['year'] ?? $year;
                        for ($i = 0; $i < 5; $i++) {
                            echo "<option value='" . $cur_yr . "'";
                            if ($req_yr && $req_yr == $cur_yr) echo " selected ";
                            echo ">" . $cur_yr . "</option>";
                            $cur_yr--;
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Chidon ID's</th>
                <td>
                    <input type="text" name="chidon_ids" id="chidon_ids" size="50" value="<?= $_POST['chidon_ids'] ?? '' ?>" />
                </td>
            </tr>
            <tr>
                <th>User ID's</th>
                <td>
                    <input type="text" name="user_ids" id="user_ids" size="50" value="<?= $_POST['user_ids'] ?? '' ?>" />
                </td>
            </tr>
            <tr>
                <th>User Serial Numbers</th>
                <td>
                    <input type="text" name="user_serials" id="user_serials" size="50" value="<?= $_POST['user_serials'] ?? '' ?>" />
                </td>
            </tr>
        </table>
        <br />
        <button>GO</button>
    </form>
    <br />
    <div>
        <?php
        if (! empty($info)) {
            // get the track types
            $tracks = $ct->getTypes();

            $fields_to_use = [
                "highest_track",
                "test_type",
                "khk_override",
                "personal_credit",
                "coupon_code",
                "raised",
                "family_balance",
                "extra_purchases",
                "th_chidon_id",
                "year",
                "reg_date",
                "size",
                "paid",
                "date_paid",
                "paid_by",
                "book",
                "parent_id",
                "notes",
                "answers",
                "sandwich",
                "height",
                "weight",
                "ski",
                "outerwear",
                "shoe_size",
                "host",
                "between_streets1",
                "between_streets2",
                "host_number",
                "allergies",
                "walking_zone",
                "approval",
                "deleted",
                "host_street",
                "host_street_num",
                "host_street_num_suffix",
                "host_street_apt",
                "yarmulka",
                "walking_group",
                "recruited_by",
                "poll",
                "khk",
                "trip",
                "ultimate_trip",
                "reward_type",
                "award_type",
                "dropped_out",
                "reason",
                "thurs_walking",
                "ms_walking",
                "confirmed_info"
            ];

            $not_to_edit = [
                'reg_date',
                'personal_credit',
                'class_grade',
                'extra_purchases',
                'year',
                'school_id',
                'class_sub',
                'class_id',
                'last',
                'admin_id',
                'user_id',
                'coupon_code',
                'family_balance',
                'th_chidon_id',
                'first',
                'raised',
                'user_serial', 
                'track_passed'
            ];
            
            $fields = ['first', 'last', 'admin_id', 'user_id', 'user_serial', 'school_id', 'class_id', 'class_grade', 'class_sub', 'school_id_chidon', 
                'personal_credit', 'coupon_code', 'raised', 'family_balance', 'track_passed'];
            $stmt = $MASHPIA_DB->query("show columns from th_chidon");
            foreach ( $stmt->fetchAll() as $row ) {
                if (in_array($row['Field'], $fields_to_use)) $fields[] = $row['Field'];
            }
            // $fields[] = 'extra_purchases';

            $boolSelection = [
                0   => 'No',
                1   => 'Yes'
            ];

            $links = [
                'coupon_code' => 'https://mashpia.com/chidonOld/coupons/coupons.php',
                'raised' => 'https://chidondrive.com/site/family-single.html?id='
            ];

            foreach ($info as $row) {
                $links['raised'] .= $row['admin_id'];
                echo "<div class='tableInfo'><table>";
                foreach ($fields as $field) {
                    echo "<tr><td><b>";
                    if (in_array($field, ['coupon_code', 'raised'])) {
                        echo "<a href='" . $links[$field] . "' target='_blank'>" . $field . "</a>";
                    } else {
                        echo $field;
                    }
                    echo "</b></td>";
                    echo "<td>";
                    if (is_array($row[$field])) {
                        echo "<ul>";
                        foreach ($row[$field] as $val) echo "<li>" . $val . "</li>";
                        echo "</ul>";
                    } else {
                        if (in_array($field, $not_to_edit)) {
                            echo $row[$field];
                        // } else if ($field == 'track_passed') {
                        //     echo "<select id='" . $field . "' name='" . $field . "' class='edit' data-old='" . strtolower($row[$field]) . "'>";
                        //         echo "<option value=''></option>";
                        //         foreach ($tracks as $track) {
                        //             echo "<option value='" . strtolower($track) . "'";
                        //             if (strtolower($track) == strtolower($row[$field])) echo " selected ";
                        //             echo ">" . $track . "</option>";
                        //         }
                        //         echo "</select>";
                        } else {
                            if (in_array($field, ['test_type', 'reward_type', 'award_type'])) {
                                echo "<select id='" . $field . "' name='" . $field . "' class='edit' data-old='" . $row[$field] . "'>";
                                echo "<option value=''></option>";
                                foreach ($tracks as $old_track => $new_track) {
                                    echo "<option value='" . $old_track . "'";
                                    if ($old_track == $row[$field]) echo " selected ";
                                    echo ">" . $new_track . "</option>";
                                }
                                echo "</select>";
                            } else if (in_array($field, ['khk_override', 'deleted', 'walking', 'khk', 'ultimate_trip', 'dropped_out', 'confirmed_info'])) {
                                echo "<select id='" . $field . "' name='" . $field . "' class='edit' data-old='" . $row[$field] . "'>";
                                foreach ($boolSelection as $key => $val) {
                                    echo "<option value='" . $key . "'";
                                    if ($key == $row[$field]) echo " selected ";
                                    echo ">" . $val . "</option>";
                                }
                                echo "</select>";
                            } else {
                                echo "<input type='text' value='" . $row[$field] . "' id='" . $field . "' name='" . $field . "' class='edit' 
                                    data-old='" . $row[$field] . "' />";
                            }
                        }
                    }
                    echo "</td></tr>";
                }
                echo "</table><br />";
                echo "<button class='save' id='" . $row['user_id'] . "'>Save</button></div>";
            }
        }
        ?>
    </div>
    <br />
</body>
<script 
    src="https://code.jquery.com/jquery-1.12.4.min.js" 
    integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" 
    crossorigin="anonymous"></script>
<script>
    $(".save").on("click", function() {
        const user_id = $(this).attr('id')
        const changes = {}
        changes[user_id] = {}
        $('.edit').each(function() {
            const val = $(this).val()
            const field = $(this).attr('id')
            const old_val = $(this).data('old')
            if (val != old_val) {
                if (['track_passed', 'test_type', 'reward_type', 'award_type'].includes(field)) {
                    // make sure the value is not empty
                    if (val != '') changes[user_id][field] = val
                    else alert('Please select a value for ' + field)
                } else {
                    changes[user_id][field] = val
                }
            }
        })
        const res = fetch('api/update_chidon_info.php', {
            method: 'POST',
            body: JSON.stringify(changes)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Saved.')
            } else {
                alert('Error saving.')
            }
        })
    })
</script>
</html>
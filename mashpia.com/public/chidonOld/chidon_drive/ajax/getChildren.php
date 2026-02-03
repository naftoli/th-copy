<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once '../../../api/header/db.php';
require_once '../../../class.globalSettings.php';
require_once '../../../chidonTests/class.chidonTests.php';
require_once '../../coupons/class.couponCode.php';
require_once '../site/enrollment/class.tripRegistration.php';
require_once '../encrypt.php';

$year = GlobalSettings::getChidonYear();
$admin = mysql_real_escape_string( $_POST['admin'] );
$admin_id = encrypt_decrypt('decrypt', $admin);

if (! $admin_id) {
    echo json_encode([
        'success'   => false,
        'error'     => 'Missing Admin ID.'
    ]);
    exit;
}

function getChildren() {
    global $MASHPIA_DB, $admin_id, $year;

    $children = [];
    // track, raised, grade, trip location
    $sql = "select u.user_id, u.school_id, u.class_id, u.mobile_pic, u.user_photo_id, u.first, u.last, u.user_serial, u.open_reg_5786,  
                UPPER(u.gender) as gender, 
                c.class_grade, 
                tc.*, 
                conf.chidon_confirmation_id as schoolConfirmed, 
                cor.open_reg as openRegForSchool,
                a.admin_id, a.admin_country, a.admin_address1, a.admin_address2, a.admin_city, a.admin_state, a.admin_postal 
            from users u 
            join schools s using (school_id)
            join th_chidon tc using (user_id)  
            join classes c on c.class_id = u.class_id 
            left join chidon_confirmations conf on (u.school_id = conf.school_id and conf.year = :year) 
            left join chidon_open_reg cor on (cor.school_id = u.school_id and cor.year = :year) 
            join admin_auths aa on aa.id = u.user_id 
            join admins a using (admin_id) 
            where tc.year = :year 
            and a.admin_id = :admin";
    $stmt = $MASHPIA_DB->prepare($sql);
    if (
        $stmt->execute([
        ':year'     => $year,
        ':admin'    => $admin_id
    ])) {
        $children = $stmt->fetchAll();

        // get family balance
        $r = new TripRegistration($admin_id, $year);
        $familyBalance = $r->getFamilyBalance();

        // find sum of chidon drive raised for child
        $stmt = $MASHPIA_DB->prepare("
            SELECT IFNULL(SUM(subsidy_amount), 0) as raised 
            FROM chidon_user_subsidies 
            WHERE chidon_year = :year AND user_id = :user
        ");

        $stmtPersonalCredit = $MASHPIA_DB->prepare("
            SELECT IFNULL(SUM(amount), 0) as personal_credit 
            FROM registration_charges 
            WHERE year = :year AND user_id = :user 
                AND type in ('RRYSD', 'RRYDA', 'RRHVN')
        ");

        $c = new CouponCode($MASHPIA_DB, $year);

        for ($i = 0; $i < count($children); $i++) {
            $children[$i]['familyBalance'] = $familyBalance;

            $child = $children[$i];
            $stmt->execute([
                ':year'     => $year,
                ':user'     => $child['user_id']
            ]);
            $result = $stmt->fetch();
            if ($result['raised']) $children[$i]['raised'] = $result['raised'];

            $stmtPersonalCredit->execute([
                ':year'     => $year,
                ':user'     => $child['user_id']
            ]);
            $res = $stmtPersonalCredit->fetch();
            if ($res['personal_credit']) $children[$i]['personal_credit'] = $res['personal_credit'];

            $children[$i]['coupon'] = $c->checkForUserCode($child['user_serial']);
        }
    }
    else $stmt->debugDumpParams();

    return $children;
}

// get shools and users to keep chidon reg open for
$keepOpen = [];
$sql = "select * from keep_reg_open where year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $keepOpen[$row['type']][] = $row['id'];
}
$info['keepOpen'] = $keepOpen;

$children = getChildren();
$info['children'] = $children;
if (! $children) {
    echo json_encode([
        'success'   => false,
        'error'     => 'You do not have any children that are eligible for the Chidon.'
    ]);
} else {
    echo json_encode([
        'success'   => true,
        'info'      => $info
    ]);
}
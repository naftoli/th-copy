<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
echo "<pre>"; // Add pre-formatting for better readability
echo "Script started\n";

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.campaignEnrollment.php';
$year = GlobalSettings::getChidonYear();
echo "Dependencies loaded\n";

if ($admin_user['auth'] != 'super') {
    echo "No Permission";
    exit;
}

function generateSerial(){
    global $MASHPIA_DB;
    $query = $MASHPIA_DB->query(
        "SELECT IFNULL( MAX( user_serial ), 0 ) + 1 AS user_serial FROM users"
    );
    return $query->fetch()['user_serial'];
}

function generateBarcode(){
    global $MASHPIA_DB;
    // prepare the sql queries
    $check_duplicate = $MASHPIA_DB->prepare( "SELECT COUNT(*) as total FROM users WHERE user_code = ?;" );
    $generate_barcode = $MASHPIA_DB->prepare( "SELECT FLOOR(RAND() * 9223372036854775807) as user_code" );
    // counters
    $count = 0; $valid_code = false;
    // while we do not have a valid code, generate a new one and validate it.
    while( !$valid_code ) {
        // at 1,000 iterations ( and 2,000 queries ) just abort saving the model.
        if ( $count++ > 1000 ) 
            return false;
        // generate the barcode
        $generate_barcode->execute();
        $user_code = $generate_barcode->fetch()['user_code'];
        // make sure it is unique
        $check_duplicate->execute([ $user_code ]);
        $valid_code = $check_duplicate->fetch()['total'] == 0;
    }
    return '3'.$user_code;
}

$stmtRank = $MASHPIA_DB->prepare(
    "INSERT INTO rank_marks (rank_ord, user_id, date_promoted) VALUES (1, ?, ?) 
");

$stmtCampaign = $MASHPIA_DB->prepare("
    insert into user_tracks
    set subject_id = :subject,
    level = :level,
    user_id = :user,
    track_id = :track,
    enrolled = 1
");

$stmtChild = $MASHPIA_DB->prepare("
    INSERT INTO users (
        user_serial,
        user_code,
        first,
        last,
        gender,
        school_id,
        class_id, 
        user_start_date, 
        user_registered, 
        school_type_id,
        lang_id, 
        lang, 
        dob, 
        khk_eligible, 
        chayolei,
        chidon
    ) VALUES (
        :user_serial,
        :user_code,
        :first_name,
        :last_name,
        :gender,
        :school_id,
        :class_id, 
        :user_start_date,
        :user_registered,
        :school_type_id,
        :lang_id, 
        :lang, 
        :dob, 
        1,
        1,
        1
    )
");

$stmtChidon = $MASHPIA_DB->prepare("
    INSERT INTO th_chidon (
        year,
        school_id,
        user_id,
        size,
        reg_date,
        grade,
        book,
        shoe_size,
        yarmulka,
        khk,
        test_type,
        name_pref,
        reward_type
    ) VALUES (
        :year,
        :school_id,
        :user_id,
        :size,
        :reg_date,
        :grade,
        :book,
        :shoe_size,
        :yarmulka,
        :khk,
        :test_type,
        :name_pref,
        :reward_type
    )
");

$faker = \Faker\Factory::create();
$classes = [
    61 => [
        'M' => 6390,
        'F' => 3339
    ],
    269 => [
        'M' => 6349,
        'F' => 3182
    ]
];

$subjects = [1, 4, 12, 13, 15, 16, 21, 27, 40, 41, 42, 45, 90, 100];

$success = true;
$MASHPIA_DB->beginTransaction();
for ($i = 0; $i < 50; $i++) {
    $last_name = 'chidon_test_' . ($i + 1);
    $gender = $i % 2 == 0 ? 'M' : 'F';
    $first_name = $faker->firstName($gender == 'M' ? 'male' : 'female');
    $lang = 'en';
    $lang_id = 1;
    $school_type_id = 2;
    $school_id = $i % 2 == 0 ? 61 : 269;
    $class_id = $classes[$school_id][$gender];
    $user_start = 2455448;
    $dob = '2011-01-01';
    $user_serial = generateSerial();
    $user_code = generateBarcode();
    // echo $i . ": First Name: " . $first_name . ", Last Name: " . $last_name . ", Gender: " . $gender . ", School ID: " . $school_id . ", Class ID: " . $class_id . ", User Start: " . $user_start . ", DOB: " . $dob . "<br />";
    $res = $stmtChild->execute([
        'user_serial' => $user_serial,
        'user_code' => $user_code,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'gender' => $gender,
        'school_id' => $school_id,
        'class_id' => $class_id,
        'user_start_date' => $user_start,
        'school_type_id' => $school_type_id,
        'lang_id' => $lang_id,
        'lang' => $lang,
        'dob' => $dob, 
        'user_registered' => '2025-01-01'
    ]);
    if ( !$res ) {
        $success = false;
        $stmtChild->debugDumpParams();
        break;
    } else {
        // get generated user id
        $user_id = $MASHPIA_DB->lastInsertId();
        echo "Created user with ID: " . $user_id . "\n";
        
        // create rank
        $stmtRank->execute([ $user_id, unixtojd() ]);
        echo "Created rank for user: " . $user_id . "\n";
        
        // enroll into campaigns
        foreach ($subjects as $subject) {
            if ($subject == 1) $track = 3;
            else $track = 1;
            echo "Enrolling user {$user_id} in subject {$subject} track {$track}\n";
            $resC = $stmtCampaign->execute([
                'subject'   => $subject,
                'level'     => 14,
                'user'      => $user_id,
                'track'     => $track
            ]);
            if ( !$resC ) {
                $success = false;
                $stmtCampaign->debugDumpParams();
                break;
            }
        }
        // prepare for chidon
        $size = 'adult m';
        $reg_date = '2025-01-01';
        $grade = '8';
        $book = '5';
        $shoe = 'adult 8';
        $yarmulka = $gender == 'M' ? '5' : '0';
        $test_type = 'pro';
        $award_type = 'expert';
        $name_pref = $first_name;
        $res2 = $stmtChidon->execute([
            'year' => $year,
            'school_id' => $school_id,
            'user_id' => $user_id,
            'size' => $size,
            'reg_date' => $reg_date,
            'grade' => $grade,
            'book' => $book,
            'shoe_size' => $shoe,
            'yarmulka' => $yarmulka,
            'test_type' => $test_type,
            'name_pref' => $name_pref,
            'reward_type' => $award_type,
            'khk' => '1'
        ]);
        if (!$res2) {
            $success = false;
            $stmtChidon->debugDumpParams();
            break;
        }
    }
}

if (!$success) {
    $MASHPIA_DB->rollBack();
    echo "Error creating children\n";
} else {
    $MASHPIA_DB->commit();
    echo "Success\n";
}
echo "</pre>"; // Close the pre-formatting
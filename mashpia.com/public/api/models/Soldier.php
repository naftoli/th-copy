<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

include_once( __DIR__ . "/../tools/functions/format/parents.php" );
include_once( __DIR__ . '/../tools/functions/files/images.php' );
include_once( __DIR__ . '/../auth/classes/Auth.php' );
include_once( __DIR__ . '/traits/BuildModel.php' );
require_once( __DIR__ . '/../../class.points.php' );
require_once( __DIR__ . '/../../class.campaignEnrollment.php');
require_once( __DIR__ . '/../../chidonTests/class.chidonTests.php');
require_once( __DIR__ . '/../../class.globalSettings.php');
// LEGACY CODE
require_once( __DIR__ . '/../../calendar.php' );

class Soldier extends \ActiveRecord\Model implements \JsonSerializable {
    use \traits\BuildModel;

    static $table_name = 'users';
    static $primary_key = 'user_id';
    
    static $before_create = [ 'generateSchoolType', 'generateSerial', 'generateBarcode'];
    static $after_create = [ 
        'enrollInCampaigns',        'generateRank',
        'setupBirthdayMissions',    'afterCreate'
    ];
    static $before_destroy = [ 'canDestroy' ];

    // relationships
    static $belongs_to = [
        [ 'school' ], [ 'platoon', 'foreign_key' => 'class_id' ]
    ];

    // cache
    private $miles; // total miles that the user has earned
    private $store_miles; // miles available for the store
    private $rank; // the users current rank
    private $missions = [];
    public $parent; // needed for chidon registration
    public $needs_new_pic = 1; // needs new Picture entered from mobile site

    // Access validation - takes a login and returns true or false if it can access the user
    public function validateAccess( $login ){
        if ( $login->code === 'HQ' ) {
            return true;
        } else if ( $login->code === 'INST' ) {
            return !!$this->school->inst_id == $login->id;
        } else if ( $login->code === 'BC' ) {
            return $this->school_id == $login->id;
        } else if ( $login->code === 'TEACHER' ) {
            return $this->class_id == $login->id;
        } else if ( $login->code === 'PARENT' ) {
            return !!\AdminAuth::findAuth( $login->id, 'user', $this->user_id );
        }
        return false;
    }
    
    // ******************************* HELPER FUNCTIONS *******************************
    // * returns profile picture path from (mashpia.com)/
    public function profilePicture() {
        if ( $this->mobile_pic ) {
            $this->needs_new_pic = 0;
            return '/mobile/reg/' . $this->mobile_pic;
        } else if ( $this->user_photo_id ) {
            return '/file_view.php?id=' . $this->user_photo_id;
        }
        return '/mobile/reg/images/profile-photo-default.jpg';
    }
    // * returns whether we need a new pic
    public function newPic() {
        return $this->needs_new_pic;
    }
    // *returns name based on language?
    public function name() {
        return $this->first . ' ' . $this->last;
    }
    // * returns full barcode
    public function barcode(){
        return "3$this->user_code";
    }
    // * returns the current rank
    public function rank( $cache = false ) {
        global $MASHPIA_DB;

        if ( $cache )
            $this->rank = $cache;

        if ( $this->rank )
            return $this->rank;

        $query = $MASHPIA_DB->prepare(
             'SELECT rank_ord, rank_name as name FROM rank_marks JOIN ranks USING (rank_ord) '
            .'WHERE user_id = ? ORDER BY rank_ord DESC LIMIT 1'
        );
        $query->execute([ $this->user_id ]);

        return $this->rank = $query->fetch();
    }

    // * get the current miles
    public function miles( $force_refresh = false ) {
        if ( $this->miles && !$force_refresh )
            return $this->miles;
        $points = new Points( $this->user_id );
        return $this->miles = intval( $points->getTotalPoints() );
    }
    // * get what they can spend in the store
    public function storeMiles( $force_refresh = false ) {
        if ( $this->store_miles && !$force_refresh )
            return $this->store_miles;
        $points = new Points( $this->user_id );
        return $this->store_miles = intval( $points->getStorePoints() );
    }

    // ******************************* CHAYOLEI MISSIONS ******************************* //
    public function missions( $parsha, $limit_to = ['daily', 'weekly', 'shabbos', 'no_label'] ) {
        if ( isset( $this->missions[ $parsha->id ] ) )
            return $this->missions[ $parsha->id ];
        
        require_once( API_ROOT . '/../mission_report/classes/missions.php' );
        $legacy_missions = new Missions( $parsha->start, $parsha->end, $this->user_id);
        $legacy_missions = $legacy_missions->getMissions()[0];
        $this->missions[ $parsha->id ] = [];

        // needless iteration due to not wanting to have duplicate code. When Missions is replced plase keep this code in mind
        if ( in_array( 'daily', $limit_to ) )
            foreach( $legacy_missions->sorted_daily_labels as $label ) {
                $label = explode( ':', $label )[0];
                foreach( $legacy_missions->daily_tasks as $task ) {
                    if ( $task->label_name === $label ) $this->missions[ $parsha->id ][] = $task;
                }
            }
        // weekly goes after daily
        if ( in_array( 'weekly', $limit_to ) )
            foreach( $legacy_missions->sorted_weekly_labels as $label ) {
                foreach( $legacy_missions->weekly_tasks as $task ) {
                    if ( $task->label_name === $label ) $this->missions[ $parsha->id ][] = $task;
                }
            }
        // shabbos goes after weekly
        if ( in_array( 'shabbos', $limit_to ) )
            foreach( $legacy_missions->sorted_shabbos_labels as $label ) {
                foreach( $legacy_missions->shabbos_tasks as $task ) {
                    if ( $task->label_name === $label ) $this->missions[ $parsha->id ][] = $task;
                }
            }
        // then comes everything else
        if ( in_array( 'no_label', $limit_to ) || in_array( 'other', $limit_to ) )
            foreach( $legacy_missions->no_label_subjects as $label ) {
                $label = implode( ' - ', explode( ':', $label ) );
                foreach( $legacy_missions->no_label_tasks as $task ) {
                    if ( $task->label_name === $label ) $this->missions[ $parsha->id ][] = $task;
                }
            }

        return $this->missions[ $parsha->id ];
    }

    // ******************************* CHAYOLEI BOARDS ******************************* //
    // returns the current rank and how they got there
    public function rankBoard() {
        global $MASHPIA_DB; $result = [];
        // get all ranks earned
        $rank_query = $MASHPIA_DB->prepare(
            "SELECT r.rank_ord, r.rank_name, r.rank_color, r.medals_required, date_promoted "
            ."FROM rank_marks JOIN ranks AS r USING(rank_ord) "
            ."WHERE user_id=? ORDER BY rank_ord"
        );

        // get all medals earned
        $medals_query = $MASHPIA_DB->prepare(
            "SELECT ms.*, date_awarded FROM medal_marks "
            ."JOIN medals_subjects AS ms USING(subject_id, medal_ord) "
            ."WHERE user_id=? ORDER BY date_awarded, medal_ord"
        );
        $medals_query->execute( [ $this->user_id ] );
        $medals = [];
        while( $medal = $medals_query->fetch() ){
            $medal['date_awarded_he'] = dateToHebrew( $medal['date_awarded'] );
            $medal['date_awarded'] = date('Y-m-d H:i:s', jdtounix( $medal['date_awarded'] ));
            $medal['photo'] = $medal['profile_photo_id'] ? 
                '/file_view.php?id='.$medal['profile_photo_id'] : 
                '/kiosk/images/medals/holder.png';
            $medals[] = $medal;
        }

        // get the amounts for each rank
        $medals_required = $MASHPIA_DB->query(
            "SELECT medals_required FROM ranks ORDER BY rank_ord"
        )->fetchAll( PDO::FETCH_COLUMN, 0 ); // fetch from the dbs
        $medals_required[] = count( $medals ) > 133 ? count( $medals ) : 133;

        // get all the ranks
        $rank_query->execute( [ $this->user_id ] );
        $ranks = $rank_query->fetchAll();
        // set the current rank
        $result['rank'] = intval( end( $ranks )['rank_ord'] );
        $result['name'] = end( $ranks )['rank_name'];
        // update the rank contents
        $medals_index = 0;
        foreach( $ranks as $index => $rank ){
            $ranks[$index]['medals'] = [];
            $ranks[$index]['date_promoted'] = date( SQL_DATE_FORMAT, jdtounix( $rank['date_promoted'] ) );

            $medals_in_rank = intval( $medals_required[ $index + 1 ] );
            $ranks[$index]['total_medals'] = $medals_in_rank;

            while( isset( $medals[ $medals_index ] ) && $medals_index < $medals_in_rank )
                $ranks[$index]['medals'][] = $medals[ $medals_index++ ];
        };
        $result['ranks'] = $ranks;
        return $result;
    }
    // returns the medal board
    public function medalBoard() {
        global $MASHPIA_DB; $medal_board = [];

        try {
            $c = new CampaignEnrollment( $this->user_id );
            $c->setType( $this->school_type_id );
            $subject_ids = implode( ', ', $c->getCampaigns() );
        } catch (EnrollmentException $e) {
            return false;
        }
        // * load what the soldier did
        $marks_query = $MASHPIA_DB->query(
            'SELECT subject_id, subject_name, IFNULL( earned, 0 ) as earned '
            .'FROM subjects LEFT JOIN ('
                .'SELECT subject_id, SUM( mission_count ) AS earned FROM date_tasks_mission_marks '
                .'WHERE user_id = '. $this->user_id .' GROUP BY subject_id '
            .') as marks USING (subject_id) '
            .'WHERE subject_id IN (' . $subject_ids . ') '
        );
        while( $subject = $marks_query->fetch() ) {
            $subject['medals'] = [];
            $medal_board[ $subject['subject_id'] ] = $subject;
        }
            
        // * load the data for each medal
        $medals_query = $MASHPIA_DB->query(
             'SELECT subject_id, missions_required, profile_photo_id, medal_name '
            .'FROM medals_subjects JOIN medals USING (medal_ord) '
            .'WHERE subject_id IN (' . $subject_ids . ') ORDER BY subject_id, medal_ord ASC;'
        );
        // * parse the data for each medal
        $subject_total = 0;
        while( $medal = $medals_query->fetch() ) {
            // if we do not have any data for this subject,
            if ( count( $medal_board[ $medal[ 'subject_id' ] ][ 'medals' ] ) === 0 ) {
                $subject_total = 0;
                $medal_board[ $medal[ 'subject_id' ] ][ 'medals' ][] = [
                    'missions' => 0,   'color' => 'No',
                    'picture' => '/kiosk/images/medals/holder.png'
                ];
            }
            // compound the missions_required
            $subject_total += intval( $medal['missions_required'] );
            // get the picture
            $picture = '/images/stickers/campaigns/'.$medal[ 'subject_id' ].'.gif';
            if ( $medal['profile_photo_id'] )
                $picture = '/file_view.php?id='.$medal['profile_photo_id'];
            // get the medal_board
            $medal_board[ $medal[ 'subject_id' ] ][ 'medals' ][] = [
                'picture' => $picture,
                'missions' => $subject_total,
                'color' => $medal['medal_name'],
            ];
        }
        return array_values( $medal_board );
    }

    // ******************************* PARENT ACCOUNT ******************************* //
    // * get parent account
    public function parentAccount() {
        global $MASHPIA_DB;
        $query = $MASHPIA_DB->prepare(
            'SELECT admin_id, first, father, mother, last, admin_phone_mobile AS phone, admin_phone_mobile2 AS phone2, admin_email as email, admin_address1, admin_address2, admin_city, admin_state, admin_postal, admin_country '
            .'FROM admins JOIN admin_auths aa USING (admin_id) WHERE aa.auth="user" and id=?;'
        );
        $query->execute( [$this->user_id] );
        $parent = $query->fetch();
        if ( !$parent ) return false;
        // set the admin key if we have a parent
        $parent['key'] = mashpia\api\auth\Auth::mobileKey( $parent['admin_id'] );
        $parent['first'] = formatParentName( $parent['father'], $parent['mother'], $parent['first'] );
        return $parent;
    }

    // get regestration discount for soldier
    public function getDiscount() {
        global $MASHPIA_DB;
        require_once $_SERVER['DOCUMENT_ROOT'] . '/reports/registration/Discount.php';
        $year = GlobalSettings::getRegistrationYear();
        $d = new DiscountManager($MASHPIA_DB);
        $discount = $d->getDiscountForUserYear($year, $this->user_id);
        if (!empty($discount) && $discount[0]['used'] > 0) return [];
        return $discount;
    }

    // get chidon info for child
    public function getChidonInfo() {
        global $MASHPIA_DB;
        $info = [];
        $query = $MASHPIA_DB->prepare("
            SELECT 
                *
            FROM
                th_chidon tc
                    JOIN
                registration_charges rc USING (year , user_id)
                    LEFT JOIN
                yahadus_book_purchases ybp USING (year , user_id)
            WHERE
                tc.year = :year AND tc.user_id = :user
                    AND rc.type = 'chidon'
        ");
        $year = GlobalSettings::getChidonRegYear();
        $res = $query->execute([
            ':user' => $this->user_id,
            ':year' => $year
        ]);
//        echo $query->debugDumpParams();

        if ($res) $info = $query->fetch();

        return $info;
    }

    // ******************************* IMAGES ******************************* //
    // takes an uploaded file and sets it as the profile picture
    public function setProfilePicture( $file ){
        $upload = self::uploadProfilePicture( $this->user_id, $file );
        if ( !is_array( $upload ) ) return $upload;
        // update the mobile_pic column
        $this->mobile_pic = $upload['mobile_pic'];
        $this->save();
        return true;
    }
    // validates and moves the uploaded profile picture...
    public static function uploadProfilePicture( $user_id, $file ){
        $type = exif_imagetype( $file['tmp_name'] );
        $extension = image_type_to_extension( $type );
        if ( !in_array( $type, [ IMAGETYPE_JPEG, IMAGETYPE_PNG ] ) )
            return 'Invalid File Type. Only JPG/JPEG/PNG are supported at the moment.';
        // all other upload errors
        if ( $file['error'] !== UPLOAD_ERR_OK )
            return codeToMessage( $file['error'] ); // api/funcitons/files/images.php#10
        // generate the file name
        $file_name = getProfileDestination( $user_id, $extension ); // api/funcitons/files/images.php#35
        $target = __DIR__ . "/../../mobile/reg/$file_name";
        // remove duplicate files
        if ( file_exists( $target ) ) unlink( $target );
        // save file
        $result = move_uploaded_file( $file['tmp_name'], $target );
        if ( !$result ) 
            return 'Unable to save Image. Please check if your file is corrupt before trying again.';
        return [
            'mobile_pic' => $file_name,
            'profilePicture' => '/mobile/reg/' . $file_name
        ];
    }

    // ******************************* REGISTRATION *******************************
    // returns array of registration rates. Call $this->registrationStatus() to get each ones status
    public function registrationRates() {
        global $current_user;
        // calculate chayolei rate
        $result = [ 'chayolei' => $this->school->soldierFee( true ) ];
        // add chidon if user is in grade 3+
        if ( $this->platoon && $this->platoon->class_grade > 2 )
            $result[ 'chidon' ] = GlobalSettings::getChidonCost( $this->school_id );
        return $result;
    }

    public function registrationCharge( $type, $amount, $trans_id = '', $year = false, $discount = 0 ) {
        global $MASHPIA_DB;
        // set default year.
        $year = $year ? $year : $type == 'chidon' ? GlobalSettings::getChidonRegYear() : GlobalSettings::getRegistrationYear( $this->school_id );
        // make sure we don't already have such a registration charge in system - avoid duplication
        $check_qry = $MASHPIA_DB->prepare("
            SELECT 
                count(*) as total
            FROM
                registration_charges
            WHERE
                user_id = :id AND year = :year
                    AND type = :type
        ");
        $check_qry->execute([
            ':id'   =>  $this->user_id, 
            ':year' =>  $year, 
            ':type' =>  $type
        ]);
        $row = $check_qry->fetch();
        $total = $row['total'];
        if (!$total) {
//        if ($row['total']) {
//            $registration_info_query = $MASHPIA_DB->prepare("
//                UPDATE registration_charges SET trans_id = :trans, amount = :amount
//                WHERE user_id = :user AND year = :year AND type = :type
//            ");
//            return $registration_info_query->execute([
//                'type' => $type,        'trans' => $trans_id,
//                'year' => $year,        'user'  => $this->user_id,
//                'amount' => $amount
//            ]);
//        }
            // * prepare the query
            $registration_info_query = $MASHPIA_DB->prepare(
                "INSERT INTO registration_charges (trans_id, user_id, school_id, type, amount, year, discount) "
                . "VALUES( :trans_id, :user_id, :school_id, :type, :amount, :year, :discount )"
            );
            // * execte the query
            return $registration_info_query->execute([
                'type' => $type, 'trans_id' => $trans_id,
                'year' => $year, 'user_id' => $this->user_id,
                'amount' => $amount, 'school_id' => $this->school_id,
                'discount' => $discount
            ]);
        }
        return true;
    }
    //get all of the soldiers registration charges
    public function registrationCharges() {
        global $MASHPIA_DB;
        $query = $MASHPIA_DB->query(
            'SELECT rc.type, rc.year, rc.amount, rc.date, s.school_id, s.school_number, s.school_name, '
            .'t.trans_id, t.description, t.amount as total, t.response FROM registration_charges rc '
            .'LEFT JOIN schools s USING (school_id) LEFT JOIN transactions t USING (trans_id) '
            .'WHERE rc.user_id = '.$this->user_id.' ORDER BY date DESC;'
        );
        return $query->fetchAll();
    }

    // returns array with the status of the various registration types for the current year.
    public function registrationStatus( $year = false, $chidon_year = false, $isBC = false ) {
        global $MASHPIA_DB;

        $year = $year ? $year : GlobalSettings::getRegistrationYear( $this->school_id );
        $chidon_year = $chidon_year ? $chidon_year : GlobalSettings::getChidonRegYear();

        // fetch the status from the two other tables, with prepared statements for security ;-)
        $user_status_query = $MASHPIA_DB->prepare(
            "SELECT user_reg_id, ur.paid, u.chayolei, th_chidon_id, u.chidon, s.reg_type FROM users u "
            ."LEFT JOIN user_registration ur ON ur.user_id = u.user_id AND ur.year = :year "
            ."LEFT JOIN th_chidon tc ON tc.user_id = u.user_id AND tc.year = :chidon_year "
            ."JOIN schools s ON u.school_id = s.school_id "
            ."WHERE u.user_id = :user_id;"
        );
        $user_status_query->execute([ ':year' => $year, ':chidon_year' => $chidon_year, ':user_id' => $this->user_id ]);
        $row = $user_status_query->fetch();

        // for some reason Mendel programmed it such that "true" means NOT to register, and "false" means YES to register
        $result = [];

        if ( $row['chayolei'] && !$isBC ) {
            $result[ 'chayolei' ] = !!$row['user_reg_id'];
        } else if ( $row['chayolei'] ) {
            $result[ 'chayolei' ] = !!$row['user_reg_id'] && !is_null($row['paid']);
        } else {
            $result['chayolei'] = true;
        }
        
        // only add th_chidon_id if the user is in grade 4-8
        // chidonEdit key indicates true/false the way it's meant to mean
        $exceptions = [482,544,583];
        if ( $this->platoon && $this->platoon->class_grade >= 3 && $this->platoon->class_grade <= 8 &&
            $row['chidon'] && !in_array( $this->school_id, $exceptions )
        ) {
            $result['chidon'] = !!$row['th_chidon_id'];
            $result['chidonEdit'] = !!$row['th_chidon_id'];
        }
        else {
            $result['chidon'] = true;
            $result['chidonEdit'] = false;
        }

        // turn off chayolei and chidon reg if school has not registered yet
        if (
            (isset($result['chayolei']) && $result['chayolei'] == false)
            ||
            (isset($result['chidon']) && $result['chidon'] == false)
        ) {
            $school_registered = false;
            $reg_info = $this->school->school_registrations;
            foreach ($reg_info as $reg) {
                if ($reg->year == $year) {
                    $school_registered = true;
                    break;
                }
            }
            if (!$school_registered && !GlobalSettings::isAustralian($this->school_id)) {
                $result['chayolei'] = true; // disable chayolei reg if school is not registered
                $result['chidon'] = true; // disable chidon reg if school is not registered
            }
        }
        // disable chayolei
        $result['chayolei'] = true;

        // check if child is eligible for khk when registering for chidon
        $eligibility = KHK::getKHKEligibility([$this->user_id]);
        $eligible = $eligibility[0];
        $result['khk'] = !$eligible[$this->user_id]; // true means not eligible for khk

        // check if child is new to chidon
        $result['new_to_chidon'] = 1;
        $stmt = $MASHPIA_DB->prepare("select * from th_chidon where user_id = :user and year != :year");
        $stmt->execute([
            ':user' => $this->user_id,
            ':year' => $chidon_year
        ]);
        $rows = $stmt->fetchAll();
        if ( !empty($rows) ) $result['new_to_chidon'] = 0;

        // if school is tuition type, and school registered child, we still need parent to confirm info if coming from parent acct
        // only if not australian schools
        $result['confirmation'] = false;
        if ( !$isBC && $result['chayolei'] && intval($row['reg_type']) == 1 && !GlobalSettings::isAustralian( $this->school_id ) ) {
            $confStmt = $MASHPIA_DB->prepare("
                SELECT * FROM reg_confirmations WHERE user_id = :user AND year = :year
            ");
            $confStmt->execute([
                ':user' => $this->user_id,
                ':year' => $year
            ]);
            $rows = $confStmt->fetchAll();
            if ( empty( $rows ) ) {
                $result['chayolei'] = false;
                $result['confirmation'] = true;
            }
        }

        return $result;
    }

    /*
     * regYears
     * retreives the years for chayolei/chidon registration for this student
     */
    public function regYears() {
        $chayolei_year = GlobalSettings::getRegistrationYear( $this->school_id );
        $chidon_year = GlobalSettings::getChidonRegYear();
        return [
            'chayolei'  => $chayolei_year,
            'chidon'    => $chidon_year
        ];
    }

    /**
     * registerChayolei
     * 
     * registers the user for Tzivos Hashem and returns an array of errors
     *
     * @param int $admin_id
     * @param string $year
     * @param int $amount
     * @return array
     */
    public function registerChayolei( $admin_id, $year, $amount, $trans_id = 0, $lite = 0, $ckids = 0, $discount = 0 ){
        global $MASHPIA_DB;
        $errors = [];
        if ( !$admin_id ) {
            $errors[] = "Missing Admin ID." . "<br />" . "Admin ID: " . $admin_id;
            return $errors;
        }
        // Insert into user_registration
        // make sure to only register child if there's an amount, otherwise it's only a parent confirming information for a tuition school
        // only applies for parents registering, not schools
        $parent = true;
        $stmt = $MASHPIA_DB->prepare("select * from admin_auths where admin_id = :admin");
        $stmt->execute([':admin' => $admin_id]);
        $row = $stmt->fetch();
        if ($row['auth'] == 'school') $parent = false;
        if (!$parent || ($parent && (floatval($amount) > 0 || $discount))) {
            $reg_query = $MASHPIA_DB->prepare(
                "INSERT INTO user_registration (user_id, admin_id, year, reg_date, paid, school_id) "
                . "VALUES (:user_id, :admin_id, :year, NOW(), :paid, :school_id)"
                . "ON DUPLICATE KEY UPDATE admin_id=:admin_id, paid=:paid"
            );
            // $x = [
            //     'year' => $year,                'admin_id' => $admin_id,
            //     'user_id' => $this->user_id,    'school_id' => $this->school_id,
            //     'paid' => isset($amount) ? $amount : null,
            // ];
            // register the soldier
            if (!$reg_query->execute([
                'year' => $year, 'admin_id' => $admin_id,
                'user_id' => $this->user_id, 'school_id' => $this->school_id,
                'paid' => $amount
            ])) {
                $errors[] = "Could not insert into user_registration. (Not Registered).";
                return $errors;
            }
            // save the charge
            $type = 'chayolei';
            if ($lite) $type = 'chayolei-lite';
            if ($ckids) $type = 'ckids';
            if (!$this->registrationCharge($type, $amount, $trans_id, false, $discount)) {
                $errors[] = "Could not insert into registration_charges. (Not Registered)";
                // remove from user_registration
                $unreg_qry = $MASHPIA_DB->prepare("DELETE FROM user_registration WHERE user_id = :user, AND year = :year");
                $unreg_qry->execute([
                    ':user' => $this->user_id,
                    ':year' => $year
                ]);
                return $errors;
            }
            // set discount to used
            if ($discount) {
                $stmt = $MASHPIA_DB->prepare("
                    update discounts set used = now() where amount = :amount and user_id = :user and year = :year
                ");
                $stmt->execute([
                    ':amount'   => $discount,
                    ':user'     => $this->user_id,
                    ':year'     => $year
                ]);
            }
            // update fields to mark registered
            if (!$this->user_registered) $this->user_registered = new \Datetime();
            if (!$this->user_start_date) $this->user_start_date = unixtojd();
            // update field for chayolei lite registration
            if ($lite) $this->lite_edition = 1;
            $this->generateRank();
            $this->save();
            // create campaigns and birthday missions
            $this->enrollInCampaigns();
            $this->setupBirthdayMissions();
        } else {
            // make sure admin is parent
            $stmt = $MASHPIA_DB->prepare("
                SELECT * FROM admin_auths WHERE admin_id = :admin AND auth = 'user'
            ");
            $res = $stmt->execute([':admin' => $admin_id]);
            $rows = $stmt->fetch();
            if ($res && !empty($rows)) {
                $stmt = $MASHPIA_DB->prepare("
                    INSERT INTO reg_confirmations 
                    SET 
                        year = :year, 
                        admin_id = :admin, 
                        user_id = :user
                ");
                if (!$stmt->execute([
                    ':year' => $year,
                    ':admin' => $admin_id,
                    ':user' => $this->user_id
                ])) {
                    $errors[] = "Could not register child.";
                }
            }
        }
        return $errors;
    }
    /**
     * registerChidon
     * 
     * adds user to th_chidon
     *
     * @param int $year
     * @param string $size
     * @param string $book
     * @param int $yarmulka
     * @param string $name_pref
     * @param integer $parent_id
     * @param float $amount
     * @param string $trans_id
     * @param boolean $recruited
     * @param int $recruited_by
     * @return void
     */
    public function registerChidon(
        $year, $size, $book, $yarmulka = 5, $name_pref, $parent_id = 0, $amount = null, $trans_id = '', $recruited = false, $recruited_by = 0, $poll = '',
        $comments = '', $track = ''
    ){
        global $MASHPIA_DB;

        // save the charge
        if ( !is_null( $amount ) ) {
            $this->registrationCharge( 'chidon', $amount, $trans_id, $year );
        }
        // make sure we have parent id
        if ( !$parent_id ) {
            $stmt = $MASHPIA_DB->prepare("
                SELECT admin_id FROM admin_auths 
                WHERE role_id = 1 AND id = :id
            ");
            $res = $stmt->execute([':id' => $this->user_id]);
            if ( $res ) {
                $row = $stmt->fetch();
                $parent_id = $row['admin_id'];
            }
        }

        // check if we are inserting or updating
        $qry = $MASHPIA_DB->prepare("SELECT count(*) as total FROM th_chidon WHERE year = :year AND user_id = :user");
        $qry->execute([
            'year'  => $year,
            'user'  => $this->user_id
        ]);
        $row = $qry->fetch();
        $total = $row['total'];
        if ($total) {
            // we are updating
            $chidonQry = $MASHPIA_DB->prepare("
                UPDATE th_chidon SET 
                    size = :size, 
                    book = :book,
                    yarmulka = :yarmulka,
                    name_pref = :pref,
                    poll = :poll,
                    comments = :comments,
                    test_type = :test_type, 
                    recruited_by = :recruit 
                WHERE
                    year = :year AND school_id = :school
                        AND user_id = :user
            ");
        } else {
            $chidonQry = $MASHPIA_DB->prepare("
                INSERT INTO th_chidon SET 
                year = :year,
                school_id = :school,
                user_id = :user,
                size = :size, 
                book = :book,
                yarmulka = :yarmulka,
                name_pref = :pref,
                poll = :poll,
                comments = :comments,
                test_type = :test_type, 
                recruited_by = :recruit
            ");
        }
        return $chidonQry->execute([
            'year'  => $year,
            'school'=> $this->school_id,
            'user'  => $this->user_id,
            'size'  => $size,
            'book'  => $book,
            'yarmulka'  => $yarmulka,
            'pref'  => $name_pref,
            'poll'  => $poll,
            'comments'  => $comments,
            'test_type' => $track,
            'recruit'   => $recruited_by
        ]);
    }

    /**
     * addBookPurchase
     * 
     * adds book purchase info to db
     * 
     * @param int $year
     * @param int $user_id
     * @param string $location
     * @param string $trans_id 
     * @param string $store_name
     * @param string $store_city
     */
    public function addBookPurchase( $year, $user_id, $location, $trans_id = '', $store_name = '', $store_city = '', $version = 0 ) {
        global $MASHPIA_DB;

        // figure out if adding or updating
        $qry = $MASHPIA_DB->prepare("
            SELECT count(*) as total FROM yahadus_book_purchases 
            WHERE year = :year and user_id = :user
        ");
        $qry->execute([
            'year'  => $year,
            'user'  => $user_id
        ]);
        $row = $qry->fetch();
        $total = $row['total'];
        if ($total) {
            // updating
            // don't update trans_id
            $qry = $MASHPIA_DB->prepare("
                UPDATE yahadus_book_purchases SET 
                    location = :location, 
                    store_name = :store_name, 
                    store_city = :store_city, 
                    version = :version  
                WHERE
                    user_id = :user AND year = :year
            ");
            $qry->execute([
                ':year' =>  $year,
                ':user' =>  $user_id,
                ':location' =>  $location,
                ':store_name'   =>  $store_name,
                ':store_city'   =>  $store_city,
                ':version'      => intval($version)
            ]);
        } else {
            $qry = $MASHPIA_DB->prepare(
                "insert into yahadus_book_purchases 
                set year = :year, 
                user_id = :user, 
                location = :location, 
                trans_id = :trans_id, 
                store_name = :store_name, 
                store_city = :store_city, 
                version = :version"
            );
            $qry->execute([
                ':year' =>  $year,
                ':user' =>  $user_id,
                ':location' =>  $location,
                ':trans_id' =>  $trans_id,
                ':store_name'   =>  $store_name,
                ':store_city'   =>  $store_city,
                ':version'      => intval($version)
            ]);
        }
//        echo $qry->debugDumpParams();
    }

    // set khk_reg flag in db to true
    public function addKhkReg( $year, $user_id ) {
        global $MASHPIA_DB;
        $stmt = $MASHPIA_DB->prepare("update th_chidon set khk_reg = 1 where user_id = :user and year = :year");
        $stmt->execute([
            ':user' => $user_id,
            ':year' => $year
        ]);
    }

    /**
     * @param $prizes
     * @param $year
     *
     * add chidon prizes that user has selected during registration
     */
    public function addChidonPrizes($prizes, $year) {
        global $MASHPIA_DB;

        // first remove previous prizes
        $stmt = $MASHPIA_DB->prepare("
            delete from chidon_user_prizes 
            where year = :year 
            and user_id = :user
        ");
        $stmt->execute([
            ':user'     => $this->user_id,
            ':year'     => $year
        ]);

        // then add current prizes
        $stmt = $MASHPIA_DB->prepare("
                insert into chidon_user_prizes 
                set user_id = :user, 
                prize_id = :prize, 
                year = :year, 
                he_name = :he_name");
        foreach ($prizes as $prize) {
            $he = $prize['personalization'] ? $prize['he_name'] : '';
            $stmt->execute([
                ':user'     => $this->user_id,
                ':prize'    => $prize['id'],
                ':year'     => $year,
                ':he_name'  => $he
            ]);
        }
    }

    // ******************************* SETUP WITH EXTERNAL CODE *******************************
    public function enrollInCampaigns() {
        try {
            $c = new CampaignEnrollment($this->user_id);
            $c->enroll();
        } catch (EnrollmentException $e) {}
    }
    public function setupBirthdayMissions(){
        require_once( __DIR__ . '/../../class.birthdayEn.php' );
        require_once( __DIR__ . '/../../class.birthdayYi.php' );
        require_once( __DIR__ . '/../../class.birthdayHe.php' );
        require_once( __DIR__ . '/../../class.heDob.php' );
        require_once( __DIR__ . '/../../class.wpBirthday.php' );

        // run the functions
        $b = new BirthdayEn( $this->user_id );      
        @$b->enablePrevious();
        @$b->setBirthday();
        $bi = new BirthdayYi( $this->user_id );   
        @$bi->enablePrevious();
        @$bi->setBirthday();
        $bh = new BirthdayHe( $this->user_id );
        @$bh->enablePrevious();
        @$bh->setBirthday();
        $hdob = new HeDob( $this->user_id );      @$hdob->setHeDob();
        $wpb = new WpBirthday( $this->user_id );  @$wpb->syncToWp();
    }

    // ******************************* ONCREATE FUNCTIONS *******************************
    public function generateSerial(){
        global $MASHPIA_DB;
        if ( !$this->user_serial ) {
            $query = $MASHPIA_DB->query(
                "SELECT IFNULL( MAX( user_serial ), 0 ) + 1 AS user_serial FROM users"
            );
            $this->user_serial = $query->fetch()['user_serial'];
        }
    }

    public function generateBarcode(){
        global $MASHPIA_DB;
        if ( !$this->user_code ) {
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
                $this->user_code = $generate_barcode->fetch()['user_code'];
                // make sure it is unique
                $check_duplicate->execute([ $this->user_code ]);
                $valid_code = $check_duplicate->fetch()['total'] == 0;
            }
        }
    }

    public function reGenerateBarcode(){
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
            $this->user_code = $generate_barcode->fetch()['user_code'];
            // make sure it is unique
            $check_duplicate->execute([ $this->user_code ]);
            $valid_code = $check_duplicate->fetch()['total'] == 0;
        }
    }

    public function generateSchoolType() {
        // if we have a school type, return it
        if ( $this->school_type_id != 0 ) {
            // ensure correct type id
            $type_id = intval( $this->school_type_id );
            if ( $this->gender == 'F' && in_array( $type_id, [2, 12] ) ) $type_id++;
            else if ( $this->gender == 'M' && in_array( $type_id, [3, 13] ) ) $type_id--;
            return $this->school_type_id = $type_id;
        }
        // if it is 0, then make it 2 for boys
        if ( $this->gender == 'M' )
            return $this->school_type_id = 2;
        // and 3 for girls
        if ( $this->gender == 'F' )
            return $this->school_type_id = 3;
    }

    public function generateRank(){
        global $MASHPIA_DB;
        // make sure we have at least one rank
        $rank_query = $MASHPIA_DB->prepare( "SELECT * FROM rank_marks WHERE user_id = ?" );
        $rank_query->execute([ $this->user_id ]);
        if( $rank_query->rowCount() == 0 ){
            $MASHPIA_DB->prepare(
                "INSERT INTO rank_marks (rank_ord, user_id, date_promoted) VALUES (1, ?, ?) "
            )->execute([ $this->user_id, unixtojd() ]);
        }
    }

    // when setting a users tanya setting to off, we need to update the summary tables so that there's no discrepancy in campaign totals
    public function removeFromBpSummary() {
        global $MASHPIA_DB;
        // find current campaign id for tanya / mishna
        $year = GlobalSettings::getCurrentYear();
        $campaign_qry = $MASHPIA_DB->prepare( "select id from line_campaigns where year = ?" );
        $campaign_qry->execute([ $year ]);
        $campaigns = $campaign_qry->fetchAll();
        // remove user from summary tables
        $remove_qry = $MASHPIA_DB->prepare("delete from bp_user_summary where user_id = ? and campaign_id = ?");
        foreach ( $campaigns as $campaign ) {
            $remove_qry->execute([ $this->user_id, $campaign['id'] ]);
        }
    }

    public function afterCreate(){
        // update the enrollment info to match the school
        $this->chayolei = $this->school->chayolei;
        $this->chidon = $this->school->chidon;
        $this->yan = $this->school->tanya;
        $this->save();
    }

    // ******************************* ONDELETE FUNCTIONS *******************************
    public function canDestroy(){
        return $this->miles() == 0;
    }

    // ******************************* SERIALIZERS *******************************
    // serialize to array for json responses
    public function jsonSerialize(){
        $result = $this->to_array([
            'only' => [
                'user_id', 'user_serial', 'first', 'last', 'first_he', 'last_he', 'lang_id', 'dob', 'dob_he',
                'school_type_id', 'gender', 'user_registered', 'allow_parent_tasks', 'print_parent_tasks',
                'chayolei', 'yan', 'chidon', 'mobile_pic', 'school_id', 'class_id'
            ],
            'methods' => [ 'profilePicture', 'barcode', 'miles', 'rank' ],
            'include' => [ 
                'school' => [ 
                    'only' => [ 'school_id', 'school_name', 'shipping_city', 'school_era' ]                     
                ],
                'platoon' => [ 'only' => [ 'class_id', 'class_grade', 'class_sub' ], 'methods' => [ 'name' ] ]
            ]
        ]);
        // other functions
        $result['start_date'] = dateToHebrew($this->user_start_date);

        return $result;
    }

    public function fullDetailSerialize(){
        $result = $this->to_array([
            'only' => [
                'user_id', 'user_serial', 'first', 'last', 'first_he', 'last_he', 'lang_id', 'dob', 'dob_he',
                'school_type_id', 'user_address1', 'user_address2', 'user_city', 'user_state',
                'user_postal', 'user_country', 'user_phone', 'gender', 'user_registered', 
                'chayolei', 'yan', 'chidon', 'allow_parent_tasks', 'print_parent_tasks', 'mobile_pic',
                'school_id', 'class_id', 'pic_mission_type'
            ],
            'methods' => [ 
                'profilePicture', 'barcode', 'miles', 'rank',
                'rankBoard', 'medalBoard', 
                'parentAccount', 'registrationCharges'
            ],
            'include' => [ 
                'school' => [ 
                    'only' => [ 'school_id', 'school_name', 'shipping_city', 'school_era' ]                     
                ],
                'platoon' => [ 'only' => [ 'class_id', 'class_grade', 'class_sub' ], 'methods' => [ 'name' ] ]
            ]
        ]);
        // other functions
        $result['start_date'] = dateToHebrew($this->user_start_date);

        return $result;
    }
}

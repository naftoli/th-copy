<?php
include_once( __DIR__ . "/../tools/functions/format/parents.php" );
include_once( __DIR__ . '/../tools/functions/files/images.php' );
include_once( __DIR__ . '/../auth/classes/Auth.php' );
include_once( __DIR__ . '/traits/BuildModel.php' );
// LEGACY CODE
require_once( __DIR__ . '/../../calendar.php' );
require_once( __DIR__ . '/../../class.points.php' );
require_once( __DIR__ . '/../../class.campaignEnrollment.php');

class Soldier extends \ActiveRecord\Model implements \JsonSerializable {
    use \traits\BuildModel;

    static $table_name = 'users';
    static $primary_key = 'user_id';
    
    static $before_create = ['generateSerial', 'generateBarcode'];
    static $after_create = [ 
        'generateRank','enrollInCampaigns', 'setupBirthdayMissions',
        'afterCreate'
    ];
    static $before_destroy = ['canDestroy'];

    // relationships
    static $belongs_to = [
        [ 'school' ], [ 'platoon', 'foreign_key' => 'class_id' ]
    ];

    // cache
    private $miles; // total miles that the user has earned
    private $store_miles; // miles available for the store
    private $rank; // the users current rank
    private $missions = [];

    // Access validation - takes a login and returns true or false if it can access the user
    public function validateAccess( $login ){
        if ( $login->code === 'HQ' ) return true;
        if ( $login->code === 'INST' ) return !!$this->school->inst_id == $login->id;
        if ( $login->code === 'BC' ) return $this->school_id == $login->id;
        if ( $login->code === 'TEACHER' ) return $this->class_id == $login->id;
        if ( $login->code === 'PARENT' ) return false; // TODO, check if parent can access child
        return false;
    }
    
    // ******************************* HELPER FUNCTIONS *******************************
    // * returns profile picture path from (mashpia.com)/
    public function profilePicture() {
        if ( $this->mobile_pic ) {
            return '/mobile/reg/' . $this->mobile_pic;
        } else if ( $this->user_photo_id ) {
            return '/file_view.php?id=' . $this->user_photo_id;
        }
        return '/mobile/reg/images/profile-photo-default.jpg';
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
            'SELECT admin_id, first, father, mother, last, admin_phone_mobile AS phone, admin_email as email '
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
        $reg_info = $this->school->getRegInfo(); // get the schools registration type
        $early_bird = $reg_info->early_bird > new DateTime();
        // calculate chayolei rate
        $result = [ 'chayolei' => $reg_info->getChildFee() ];
        // add chidon if user is in grade 4+
        if ( $this->platoon && $this->platoon->class_grade >= 4 )
            $result[ 'chidon' ] = GlobalSettings::getChidonCost( $this->school_id );
        return $result;
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
        $chidon_year = $chidon_year ? $chidon_year : GlobalSettings::getRegistrationYear();
        // fetch the status from the two other tables, with prepared statements for security ;-)
        $user_status_query = $MASHPIA_DB->prepare(
            "SELECT user_reg_id, ur.paid, chayolei, th_chidon_id, chidon FROM users u "
            ."LEFT JOIN user_registration ur ON ur.user_id = u.user_id AND ur.year = :year "
            ."LEFT JOIN th_chidon tc ON tc.user_id = u.user_id AND tc.year = :chidon_year "
            ."WHERE u.user_id = :user_id;"
        );
        $user_status_query->execute([ ':year' => $year, ':chidon_year' => $chidon_year, ':user_id' => $this->user_id ]);
        $row = $user_status_query->fetch();
        
        $result = [];

        if ( $row['chayolei'] && !$isBC ) {
            $result[ 'chayolei' ] = !!$row['user_reg_id'];
        } else if ( $row['chayolei'] ) {
            $result[ 'chayolei' ] = !!$row['user_reg_id'] && $row['paid'] > 0;
        }
        
        // only add th_chidon_id if the user is in grade 4+ and we are before October 16, 2018 12:00am
        if ( unixtojd() < 2458409 && $this->platoon && $this->platoon->class_grade >= 4 && $row['chidon'] )
            $result[ 'chidon' ] = !!$row[ 'th_chidon_id' ];
        return $result;
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
    public function registerChayolei( $admin_id, $year, $amount ){
        global $MASHPIA_DB;
        $errors = [];
        // Insert into user_registration
        $reg_query = $MASHPIA_DB->prepare(
            "INSERT INTO user_registration (user_id, admin_id, year, reg_date, paid, school_id) "
            ."VALUES (:user_id, :admin_id, :year, NOW(), :paid, :school_id)"
            ."ON DUPLICATE KEY UPDATE admin_id=:admin_id, paid=:paid"
        );
        if( !$reg_query->execute([ 
            'user_id' => $this->user_id, 
            'admin_id' => $admin_id, 
            'year' => $year, 
            'paid' => $amount, 
            'school_id' => $this->school_id 
        ])) $errors[] = "Could not insert into user_registration.";
        // update feilds to mark registered
        $this->user_registered = new \Datetime();
        if( !$this->user_start_date) $this->user_start_date = unixtojd();
        $this->generateRank();
        $this->save();
        // create campaigns and birthday missions
        $this->enrollInCampaigns();
        $this->setupBirthdayMissions();

        return $errors;
    }
    /**
     * registerChidon
     * 
     * adds user to th_chidon
     *
     * @param int $year
     * @param string $size
     * @param integer $parent_id
     * @return void
     */
    public function registerChidon( $year, $size, $parent_id = 0 ){
        global $MASHPIA_DB;

        $chidon_query = $MASHPIA_DB->prepare(
            "INSERT INTO th_chidon (year, school_id, user_id, size, parent_id) VALUES (?, ?, ?, ?, ?)"
        );
        return $chidon_query->execute( [ $year, $this->school_id, $this->user_id, $size, $parent_id ] );
    }

    // ******************************* SETUP WITH EXTERNAL CODE *******************************
    public function enrollInCampaigns() {
        try {
            $c = new CampaignEnrollment($this->user_id);
            $c->enroll();
        } catch (EnrollmentException $e) {}
    }
    public function setupBirthdayMissions(){
        require_once( __DIR__ . '/../../class.birthday.php' );
        require_once( __DIR__ . '/../../class.birthdayYi.php' );
        require_once( __DIR__ . '/../../class.heDob.php' );
        // run the functions
        $b = new Birthday( $this->user_id );      @$b->setBirthday();
        $bi = new BirthdayYi( $this->user_id );   @$bi->setBirthday();
        $hdob = new HeDob( $this->user_id );      @$hdob->setHeDob();
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
    public function afterCreate(){
        // update the enrollment info to match the school
        $this->chayolei = $this->school->chayolei;
        $this->chidon = $this->school->chidon;
        $this->yan = $this->school->tehillim;
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
                'school_id', 'class_id'
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

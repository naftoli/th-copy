<?php
include_once( __DIR__ . '/traits/BuildModel.php' );
include_once( __DIR__ . '/traits/SetRelatedModel.php' );
include_once( __DIR__ . '/../functions/files/images.php' );

class User extends ActiveRecord\Model implements JsonSerializable {
    use traits\BuildModel;
    use traits\SetRelatedModel;

    static $before_create = ['generateSerial', 'generateBarcode'];

    // relationships
    static $belongs_to = [
        [ 'school' ], [ 'platoon', 'foreign_key' => 'class_id' ]
    ];
    
    // ******************************* HELPER FUNCTIONS *******************************
    /**
     * profilePicture
     *
     * returns profile picture path from /
     * 
     * @return string
     */
    public function profilePicture() {
        if ( $this->mobile_pic ) {
            return "/mobile/reg/" . $this->mobile_pic;
        } else if ( $this->user_photo_id ) {
            return "/file_view.php?id=" . $this->user_photo_id;
        }
        return "/mobile/reg/images/profile-photo-default.jpg";
    }
    /**
     * setProfilePicture
     *
     * takes an uploaded file and sets it as the profile picture
     * 
     * @param array $file
     * @return string/array ( array if success and string on error )
     */
    public function setProfilePicture( $file ){
        $type = exif_imagetype( $file['tmp_name'] );
        $extension = image_type_to_extension($type);
        // only PNG's and JPEG's for profile pictures
        if ( !in_array( $type, [ IMAGETYPE_JPEG, IMAGETYPE_PNG ] ) )
            return 'Invalid File Type. Only JPG/JPEG/PNG are supported at the moment.';
        // all other upload errors
        if ( $file['error'] !== UPLOAD_ERR_OK )
            return codeToMessage( $file['error'] ); // api/funcitons/files/images.php#10
        // generate the file name
        $file_name = getProfileDestination( $this->user_id, $extension ); // api/funcitons/files/images.php#35
        $target = __DIR__ . "/../../mobile/reg/$file_name";
        // remove duplicate files
        if ( file_exists( $target ) ) unlink( $target );
        // save file
        $result = move_uploaded_file( $file['tmp_name'], $target );
        if ( !$result ) 
            return 'Unable to save Image. Please check if your file is corrupt before trying again.';
        // update the profile picture
        $this->mobile_pic = $file_name;
        $this->save();
        // return an array with the results
        return true;
    }
    public function barcode(){
        return '3'.$this->user_code;
    }

    // ******************************* REGISTRATION *******************************
    /**
     * registrationRates
     *
     * returns array of registration rates. Call $this->registrationStatus() to get each ones status
     * 
     * @return array
     */
    public function registrationRates() {
        $reg_info = $this->school->getRegInfo(); // get the schools registration type
        $early_bird = $reg_info->early_bird > new DateTime();
        // calculate chayolei rate
        $result = [ 'chayolei' => $reg_info->getChildFee() ];
        // add chidon if user is in grade 4+
        if ( $this->platoon->class_grade >= 4 )
            $result[ 'chidon' ] = GlobalSettings::getChidonCost();
        return $result;
    }
    /**
     * registrationStatus
     * 
     * returns array with the status of the various registration types for the current year.
     *
     * @param string $year
     * @return array
     */
    public function registrationStatus( $year = false ) {
        global $pdo;
        $year = $year ? $year : GlobalSettings::getRegistrationYear();
        // fetch the status from the two other tables, with prepared statements for security ;-)
        $user_status_query = $pdo->prepare(
            "SELECT user_reg_id, th_chidon_id FROM users u "
            ."LEFT JOIN user_registration ur ON ur.user_id = u.user_id AND ur.year = :year "
            ."LEFT JOIN th_chidon tc ON tc.user_id = u.user_id AND tc.year = :year "
            ."WHERE u.user_id = :user_id;"
        );
        $user_status_query->execute([ ':year' => $year, ':user_id' => $this->user_id ]);
        $row = $user_status_query->fetch();
        $result = [ 'chayolei'  => !!$row['user_reg_id'] ];
        // only add th_chidon_id if the user is in grade 4+
        if ( $this->platoon->class_grade >= 4 )
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
        global $pdo;
        $errors = [];
        // Insert into user_registration
        $reg_query = $pdo->prepare(
            "INSERT INTO user_registration (user_id, admin_id, year, reg_date, paid, school_id) "
            ."VALUES (:user_id, :admin_id, :year, NOW(), :paid, :school_id)"
            ."ON DUPLICATE KEY UPDATE paid=:paid"
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
        $this->save();
        // make sure we have at least one rank
        $rank_query = $pdo->prepare( "SELECT * FROM rank_marks WHERE user_id = ?" );
        $rank_query->execute([ $this->user_id ]);
        if( $rank_query->rowCount() == 0 ){
            if ( !$pdo->prepare(
                    "INSERT INTO rank_marks (rank_ord, user_id, date_promoted) VALUES (1, ?, ?) "
                )->execute([ $this->user_id, unixtojd() ])
            ) $errors[] = "Could not insert into rank_marks.";
        }
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
        global $pdo;

        $chidon_query = $pdo->prepare(
            "INSERT INTO th_chidon (year, school_id, user_id, size, parent_id) VALUES (?, ?, ?, ?, ?)"
        );
        return $chidon_query->execute( [ $year, $this->school_id, $this->user_id, $size, $parent_id ] );
    }

    // ******************************* SETUP WITH EXTERNAL CODE *******************************
    private function enrollInCampaigns() {
        require_once( __DIR__ . '/../../class.campaignEnrollment.php');
        try {
            $c = new CampaignEnrollment($this->user_id);
            $c->enroll();
        } catch (EnrollmentException $e) {}
    }
    private function setupBirthdayMissions(){
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
        global $pdo;
        if ( !$this->user_serial ) {
            $query = $pdo->query(
                "SELECT IFNULL( MAX( user_serial ), 0 ) + 1 AS user_serial FROM users"
            );
    
            $this->user_serial = $query->fetch()['user_serial'];
        }
    }
    public function generateBarcode(){
        global $pdo;
        if ( !$this->user_code ) {
            // prepare the sql queries
            $check_duplicate = $pdo->prepare( "SELECT COUNT(*) as total FROM users WHERE user_code = ?;" );
            $generate_barcode = $pdo->prepare( "SELECT FLOOR(RAND() * 9223372036854775807) as user_code" );
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

    // ******************************* SERIALIZERS *******************************
    /**
     * jsonSerialize
     * 
     * serialize object to array
     * 
     * @return array
     */
    public function jsonSerialize(){
        return $this->to_array([
            'only' => [
                'user_id', 'user_serial', 'first', 'last', 'first_he', 'last_he', 'lang_id', 'dob',
                'school_type_id', 'user_address1', 'user_address2', 'user_city', 'user_state',
                'user_postal', 'user_country', 'gender', 'user_start_date', 'user_registered',
                'chayolei', 'yan', 'chidon', 'allow_parent_tasks', 'print_parent_tasks', 'mobile_pic'
            ],
            'methods' => [ 'profilePicture', 'barcode' ],
            'include' => [ 
                'school' => [ 'only' => [ 'school_id', 'school_name', 'shipping_city', 'school_era' ] ],
                'platoon' => [ 'only' => [ 'class_id', 'class_grade', 'class_sub' ], 'methods' => [ 'name' ] ]
            ]
        ]);
    }
}
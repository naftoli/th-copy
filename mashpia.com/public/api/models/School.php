<?php
include_once( __DIR__ . '/traits/BuildModel.php' );
include_once( __DIR__ . '/../tools/functions/files/images.php' );

class School extends ActiveRecord\Model implements JsonSerializable {
    use traits\BuildModel;

    private $customer_profile;
    private $soldier_count_cache;
    // relationships
    static $has_many = [
        [ 'school_registrations' ], 
        [ 'platoons', 'order' => 'class_grade, class_sub', ], 
        [ 'soldiers', 'order' => 'last, first' ] 
    ];
    static $belongs_to = [ 'institution' ];
    // callbacks
    static $before_create = [ 'generateSchoolNumber', 'generateInitials' ];
    static $after_create = [ 'enrollIntoCampaigns' ];
    static $before_update = [ 'updateSoldiers' ];
    // valdiations and aliases
    public static $alias_attribute = [
        'name' => 'school_name',          'city' => 'school_city',
        'state' => 'school_state',        'zip' => 'school_postal',
        'country' => 'school_country',    'phone' => 'school_phone',
        'address' => 'school_address1',   'address2' => 'school_address2',
        'initials' => 'school_initials',
    ];

    static $validates_uniqueness_of = [ [ 'school_number' ] ];

    // ******************************* HELPER FUNCTIONS *******************************
    public function copyAddressToShipping(){
        $this->shipping_address1 = $this->address;  $this->shipping_address2 = $this->address2;
        $this->shipping_city = $this->city;         $this->shipping_state = $this->state;
        $this->shipping_postal = $this->zip;        $this->shipping_country = $this->country;
    }
    // get the staff accounts that have access to this account
    public function staff() {
        global $MASHPIA_DB;
        
        if ( !$this->school_id )
            return [];
        
        $staff_query = $MASHPIA_DB->prepare(
            'SELECT a.first, a.last, a.username, a.admin_email as email, a.admin_id FROM admins a '
            .'JOIN admin_auths aa USING( admin_id ) WHERE aa.auth="school" AND aa.id=?;'
        );
        $staff_query->execute([ $this->school_id ]);
        return $staff_query->fetchAll();
    }
    // get the soldier count
    public function soldier_count( $cache ) {
        global $MASHPIA_DB;
        // if we are provided with a precached total, cache it
        if ( $cache )
            return $this->soldier_count_cache = $cache;
        // if we loaded it before, return that value
        if ( $this->soldier_count_cache )
            return $this->soldier_count_cache;
        // load the total number of soldiers with this school id
        $query = $MASHPIA_DB->query( 'SELECT COUNT(*) AS soldier_count FROM users WHERE school_id = '.$this->school_id );
        return $this->soldier_count_cache = $query->fetch()['soldier_count'];
    }

    // **************************** CALLBACKS ***********************************
    public function generateSchoolNumber(){
        global $MASHPIA_DB;
        if ( !$this->school_number ) {
            $query = $MASHPIA_DB->query(
                "SELECT IFNULL( MAX( school_number ), 613769 ) + 1 AS school_number FROM schools"
            );
            $this->school_number = $query->fetch()['school_number'];
        }
    }
    // generate initials for the school when it is created
    public function generateInitials() {
        if ( !$this->initials ) {
            preg_match_all('/(?<=\s|^)[a-z]/i', $this->name, $matches);
            $this->initials = strtoupper( implode('', $matches[0]) );
        }
    }
    // update the soldiers and platoons that are connected to this base
    public function updateSoldiers() {
        global $MASHPIA_DB;

        $update_sql = 'UPDATE users u LEFT JOIN classes c USING ( class_id )';
        $filter_sql = 'WHERE u.school_id = :id';
        // allow_parent_tasks
        if ( $this->attribute_is_dirty('allow_parent_tasks') ){
            $update = "$update_sql SET u.allow_parent_tasks = :v, c.allow_parent_tasks = :v $filter_sql";
            $update = $MASHPIA_DB->prepare( $update );
            $update->execute([ ':v' => intval( $this->allow_parent_tasks ), ':id' => intval( $this->school_id ) ]);
        }
        // print_parent_tasks
        if ( $this->attribute_is_dirty('print_parent_tasks') ){
            $update = "$update_sql SET u.print_parent_tasks = :v, c.print_parent_tasks = :v $filter_sql";
            $update = $MASHPIA_DB->prepare( $update );
            $update->execute([ ':v' => intval( $this->print_parent_tasks ), ':id' => intval( $this->school_id ) ]);
        }
        // pic_mission_type
        if ( $this->attribute_is_dirty('pic_mission_type') ){
            $update = "$update_sql SET u.pic_mission_type = :v, c.pic_mission_type = :v $filter_sql";
            $update = $MASHPIA_DB->prepare( $update );
            $update->execute([ ':v' => intval( $this->pic_mission_type ), ':id' => intval( $this->school_id ) ]);
        }
        // save the platoon to the dbs
        return true;
    }

    // ******************************* REGISTRATION *******************************
    /**
     * soldierFee
     * 
     * @param bollean $to_soldier are we returning this fee to a soldier or not?
     * @param int $for_type 1, 2, or 3.
     * @param bollean $no_discount should we disable the discounts
     */
    public function soldierFee( $to_soldier = false, $for_type = false, $no_discount = false ) {
        global $MASHPIA_DB;
        
        if ( !$for_type )
            $for_type = $this->reg_type;

        $early_bird = new DateTime() <  $this->earlyBird() && $this->school_id != 269; // anash kinder doesn't get early bird

        // check if hq set the chayolei fee
//        $stmt = $MASHPIA_DB->prepare("select child_fee from schools where school_id = :id");
//        $result = $stmt->execute([':id' => $this->school_id]);
//        if ( $result ) {
//            $row = $stmt->fetch();
//            $child_fee = $row['child_fee'];
//            if ( $child_fee > 0 ) return $child_fee;
//        }

        if ( $this->child_fee > 0 ) return $this->child_fee;
        
        // pass in whether its a ckids school or not; added by Naftoli 5/8/2020
        $fee = GlobalSettings::calculateChildFee(
            $for_type,      $this->child_fee,
            $to_soldier,    $early_bird,    $no_discount, 
            $this->inst_id === 10
        );

//        if ( intval( $this->school_id ) == 61 ) {
//            if ( new DateTime() < new DateTime( '2020-09-25 00:00:00' ) ) {
//                $fee -= 5;
//            }
//        }

        return $fee;
    }

    public function getRegStatus( $year = false ) {
        $reg_info = $this->registration( $year );
        if ( !$reg_info ) return 'Base Registration Pending';
        return 'Soldier Registration Open';
    }

    /**
     * registration
     * 
     * get the registration recepit for a given year
     *
     * @param string $year
     * @return SchoolRegistration/false
     */
    public function registration( $year = false ){
        $year = $year ? $year : GlobalSettings::getRegistrationYear( $this->school_id );
        // check for non-default option
        if ( $this->school_registrations ) {
            foreach( $this->school_registrations as $reg_info ){
                if ( $reg_info->year == $year ) 
                    return $reg_info;
            }
        }
        // return the reg info
        return false;
    }
    // register the school
    public function register( $admin_id, $cart, $total, $cc, $year = false, $discount = 0 ) {
        global $MASHPIA_DB;

        // set the default year
        if ( !$year ) {
            $year = GlobalSettings::getRegistrationYear( $this->school_id );
        }

        $registration = $this->registration( $year );
        // if we do not have an existing registration, generate a new one.
        if ( !$registration ) {
            $registration = new SchoolRegistration([
                'school_id' => $this->school_id,    'year' => $year,
            ]);
        }

        // update the cart total
        $cart_total = 0;
        // only if we have a cart
        if ( $cart ) {
            foreach( $cart as $item ) {
                // update the cart total
                $cart_total += $item['price'];
                // validate that it matches the settings for this base, if not throw an error.
                if ( $this->{ $item['name'] . '_fee' } != $item['price'] ) {
                    throw new Exception('Invalid Total: '.$item['name'].' price incorrect. ');
                }
            }

            if ( $total != $cart_total + $this->balance - $discount ) {
                throw new Exception(
                    "Invalid Total: Total ($total) does not match cart (".( $cart_total + $this->balance - $discount ).")"
                );
            }
        }

        if ( $total > 0 ) {
            // create Transaction
            $create_transaction_query = $MASHPIA_DB->prepare(
                "INSERT INTO transactions (school_id, trans_date, description, amount, admin_id, zip, response) "
                ."VALUES (?, NOW(), ?, ?, ?, ?, ?)"
            );
            // get dat for transactions
            $description = "Base Registration for $year";
            $payment_profile_id = $this->getPaymentProfileId( $cc );
            // * submit the transaction
            $payment_response = $this->customerProfile()->chargeCard(
                $total, $payment_profile_id, null, null, $description
            );
            // * make sure we get a valid response
            if ( !is_array( $payment_response ) ) {
                throw new Exception( $payment_response );
            }
            // * save the transaction to our dbs
            $create_transaction_query->execute([
                $this->school_id,   $description,   $total,
                $admin_id,  $this->shipping_postal, json_encode( $payment_response ),
            ]);
        }

        // update the balances
        $registration->fee += $cart_total;
        $registration->balance += $this->balance;
        $registration->amount_paid += $total;

        $registration->bulkUpdate([
            'type' => $this->reg_type,  'early_bird' => $this->earlyBird(),
            'admin_id' => $admin_id,    'date_paid' => new DateTime(),
            'modules' => json_encode([
                'chayolei'  =>  !!$this->chayolei,  'chidon'    =>  !!$this->chidon,
                'tanya'     =>  !!$this->tanya,     'rewards'   =>  !!$this->rewards
            ])
        ]);
        // update the school_era
        $this->school_era = null;
        // check that the balance is paid
        $this->balance = 0;

        $saved = $registration->save() && $this->save();

        // add details to school_registration_details table; set discount to used (when applicable) - Naftoli 06/25/21
        if ($saved) {
            $stmt = $MASHPIA_DB->prepare("SELECT school_registration_id FROM school_registrations WHERE year = :year AND school_id = :school");
            $stmt->execute([
                ':year' => $year,
                ':school'   => $this->school_id
            ]);
            $row = $stmt->fetch();
            if ($row) {
                $school_reg_id = $row['school_registration_id'];
                $stmt = $MASHPIA_DB->prepare("
                    INSERT INTO school_registration_details
                    SET school_registration_id = :id,
                    type = :type,
                    amount = :amount,
                    school_id = :school,
                    year = :year");
                if ($cart) {
                    foreach ($cart as $item) {
                        $stmt->execute([
                            ':id' => $school_reg_id,
                            ':type' => $item['name'],
                            ':amount' => $item['price'],
                            ':school' => $this->school_id,
                            ':year' => $year
                        ]);
                    }
                }
                if ($this->balance) {
                    $stmt->execute([
                        ':id'       => $school_reg_id,
                        ':type'     => 'past_due',
                        ':amount'   => $this->balance,
                        ':school'   => $this->school_id,
                        ':year'     => $year
                    ]);
                }
                if ($discount) {
                    $stmt->execute([
                        ':id' => $school_reg_id,
                        ':type' => 'discount',
                        ':amount' => -$discount,
                        ':school' => $this->school_id,
                        ':year' => $year
                    ]);
                    $stmt = $MASHPIA_DB->prepare("UPDATE discounts SET used = now() WHERE year = :year AND school_id = :school AND amount = :amount");
                    $stmt->execute([
                        ':year'     => $year,
                        ':school'   => $this->school_id,
                        ':amount'   => $discount
                    ]);
                }
            }
        }

        if ($saved) $this->sendConfirmationEmail();

        return $saved;
    }

    private function sendConfirmationEmail() {
        $to = $this->email;
        $subject = "Registration Confirmation";
        $from = 'cth@tzivoshashem.org';
        $msg = "This is to confirm that your registration for Chayolei " . GlobalSettings::getRegistrationYear( $this->school_id ) . " has been successful.";
        mail($to, $subject, $msg, "From: $from\r\n" . "Reply-To: $from\r\n" . "X-Mailer: PHP/" . phpversion());
    }

    // get the early bird, or the default
    public function earlyBird() {
        if ( $this->early_bird )
            return $this->early_bird;
        return GlobalSettings::earlyBird();
    }

    // get the current registration prices, subject to change at any time
    public function currentRegPrices() {
        // available discounts
        $discounts = [
            'early_bird' => GlobalSettings::getEarlyBird(),
            'guaranteed' => GlobalSettings::getGuaranteedDiscount()
        ];

        $now = new DateTime();
        if ($now > $this->earlyBird()) $early_bird = false;
        else $early_bird = true;

        // rates for all 3 registration types, by index (minus 1)
        $rates = [
            GlobalSettings::getRegCost( 1, $early_bird ),
            GlobalSettings::getRegCost( 2, $early_bird ),
            GlobalSettings::getRegCost( 3, $early_bird )
        ];

        return [ 'discounts' => $discounts, 'rates' => $rates ];
    }

    // ******************************* LOGOS *******************************
    public function setLogo( $logo_name, $file ){
        $filename = self::uploadLogo( $this->school_id, $logo_name, $file );
        // update the mobile_pic column
        $this->{ $logo_name } = $filename;
    }

    // validates and moves the uploaded profile picture...
    public static function uploadLogo( $school_id, $logo_name, $file ){
        $type = exif_imagetype( $file['tmp_name'] );
        $extension = image_type_to_extension( $type );
        if ( !in_array( $type, [ IMAGETYPE_JPEG, IMAGETYPE_PNG ] ) )
            throw new Exception('Invalid File Type. Only JPG/JPEG/PNG are supported at the moment.');
        // all other upload errors
        if ( $file['error'] !== UPLOAD_ERR_OK )
            throw new Exception( codeToMessage( $file['error'] ) ); // api/funcitons/files/images.php#10
        // generate the file name
        $file_name = getLogoDestination( $school_id, $extension ); // api/funcitons/files/images.php#35
        $target = __DIR__ . "/../../schoolLogos/$file_name";
        // remove duplicate files
        if ( file_exists( $target ) ) unlink( $target );
        // save file
        $result = move_uploaded_file( $file['tmp_name'], $target );
        if ( !$result ) 
            throw new Exception( 'Unable to save Image. Please check if your file is corrupt before trying again.' );
        return $file_name;
    }

    // default name
    public function name(){ return $this->school_name; }

    // logo
    public function logoPath(){ return "/schoolLogos/$this->logo"; }

    // all logos
    public function logoPaths() {
        return [
            'logo' => $this->logoPath(),
            'boys' => "/schoolLogos/$this->logo_boys",
            'girls' => "/schoolLogos/$this->logo_girls"
        ];
    }

    //********************************** PAYMENTS **********************************/
    /**
     * customerProfile
     * 
     * Attmpts to return customer profile from API, if not found returns false
     * If optional $payment_profile array provided it will attempt to create a payment profile and return it.
     *  If it encounters an error while preforming creation it will return the array from the API
     *
     * @param array $payment_profile
     * @return CustomerProfile/boolean/array
     */
    public function customerProfile(){
        if ( $this->authorize_customer_profile_id && !$this->customer_profile ) {
            $this->customer_profile = new classes\authorize\CustomerProfile(
                $this->authorize_customer_profile_id
            );
        }
        return $this->customer_profile;
    }

    public function getPaymentProfileId( $payment_info ) {
        global $current_user;
        // check if we are given an id
        if ( isset( $payment_info['payment_profile_id'] ) && $payment_info['payment_profile_id'] ) {
            $payment_profile_id = $payment_info['payment_profile_id'];
        // or connect the card to this account, throw any errros and get the profile id
        } else {
            $payment_profile  = $this->createPaymentProfile( $payment_info );
            if ( !($payment_profile instanceof classes\authorize\PaymentProfile) )
                throw new Exception( $payment_profile );
            // set the id
            $payment_profile_id = $payment_profile->customerPaymentProfileId; 
        }
        return $payment_profile_id;
    }

    // create a payment profile
    public function createPaymentProfile( $payment_info, $email = false ) {
        $email = $email ? $email : $this->accounting_email;

        // if we do not have a customer profile
        if ( !$this->customerProfile() instanceof classes\authorize\CustomerProfile ) {
            // create the account
            $payment_profile = classes\authorize\PaymentProfile::createBasicArray(
                $payment_info['cc-number'], $payment_info['cc-exp'], $payment_info['x_card_code']
            );
            $this->customer_profile = classes\authorize\CustomerProfile::create(
                "CTH_".$this->school_id, $email, $this->school_name, $payment_profile
            );
            // handle errors
            if ( !$this->customer_profile instanceof classes\authorize\CustomerProfile )
                return $this->customer_profile["message"];
            // save the valid information
            $this->authorize_customer_profile_id = $this->customer_profile->customerProfileId;
            $this->save();
            // return a new PaymentProfile instance
            return new classes\authorize\PaymentProfile(
                $this->customer_profile->paymentProfiles[0]['customerPaymentProfileId'],
                $this->customer_profile->customerProfileId
            );
        // if we do have a customer profile
        } else {
            $payment_profile = classes\authorize\PaymentProfile::create(
                $payment_info['cc-number'], $payment_info['cc-exp'], $payment_info['x_card_code'],
                $this->authorize_customer_profile_id
            );
            if ( !($payment_profile instanceof classes\authorize\PaymentProfile) )
                return $payment_profile['messages']['message'][0]['text'];
            return $payment_profile;
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
            // old columns that we no longer use
            'except' => [
                'school_makeup_id', 'school_settings', 'package_id', 'school_logo_kiosk_id',
                'school_no_logo', 'school_file_id', 'kiosk_print', 'school_store', 'camp_id', 'add_on_one',
                'add_on_two', 'big_prizes_won', 'store_only', 'he_name_principal', 'he_name_p2', 'conf_pushka_users',
                'tanya_ord', 'col_show', 'tuition'
            ],
            'methods' => [ 
                'earlyBird', 'registration', 'logoPaths', 'customerProfile', 'staff', 'currentRegPrices'
            ]
        ]);
    }

    /**
     * publicSerialize
     * 
     * serialze school for public endpoints
     *
     * @return array
     */
    public function publicSerialize(){
        return $this->to_array([
            'only' => [ 'school_id', 'school_name', 'hachayol_name' ]
        ]);
    }

    /**
     * assign school to appropriate campaigns
     */
    public function enrollIntoCampaigns() {
        global $MASHPIA_DB;

        $stmt = $MASHPIA_DB->prepare("
            INSERT INTO school_subjects VALUES( :school, :subject )
        ");

        $subjects = [];
        if ($this->inst_id === 10) {
            // ckids
//            $subjects = [];
            $stmtSubjects = $MASHPIA_DB->query("SELECT subject_id FROM subjects WHERE inst_id = 10");
//            $rows = $stmtSubjects->fetchAll();
//            foreach ( $rows as $row ) {
//                $subjects[] = $row['subject_id'];
//            }
        } else {
            // chayolei
//            $subjects = [1, 4, 12, 13, 15, 16, 21, 27, 40, 41, 42, 45, 90, 92, 93, 94, 100];
            // all subjects
//            $subjects = [];
            // all other schools
            $stmtSubjects = $MASHPIA_DB->query("
                select subject_id from subjects s 
                join school_type_subjects sts using (subject_id) 
                where s.subject_type in ('', 'WWTC', 'Tanya', 'Hakhel') 
                and sts.school_type_id in (2,3,12,13) 
                group by s.subject_id
            ");
        }
        $rows = $stmtSubjects->fetchAll();
        foreach ($rows as $row) {
            $subjects[] = $row['subject_id'];
        }
        foreach ( $subjects as $subject ) {
            $stmt->execute([
                ':school'   =>  $this->school_id, 
                ':subject'  =>  $subject
            ]);
        }
    }

    public function addDaySchoolCampaigns() {
        global $MASHPIA_DB;

        $subjects = [];
        $stmt = $MASHPIA_DB->prepare("
            INSERT INTO school_subjects VALUES( :school, :subject )
        ");
        $stmtSubjects = $MASHPIA_DB->query("SELECT subject_id FROM subjects WHERE inst_id = 10");
        $rows = $stmtSubjects->fetchAll();
        foreach ($rows as $row) {
            $subjects[] = $row['subject_id'];
        }
        foreach ( $subjects as $subject ) {
            $stmt->execute([
                ':school'   =>  $this->school_id,
                ':subject'  =>  $subject
            ]);
        }
    }
}

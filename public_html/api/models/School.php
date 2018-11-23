<?php
include_once( __DIR__ . '/traits/BuildModel.php' );
include_once( __DIR__ . '/../tools/functions/files/images.php' );

class School extends ActiveRecord\Model implements JsonSerializable {
    use traits\BuildModel;
    private $customer_profile;
    // relationships
    static $has_many = [
        [ 'school_registrations' ], 
        [ 'platoons', 'order' => 'class_grade, class_sub', ], 
        [ 'soldiers', 'order' => 'last, first' ] 
    ];
    static $belongs_to = [ 'institution' ];
    // callbacks
    static $before_create = [ 'generateSchoolNumber' ];
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

    public function generateInitials() {
        preg_match_all('/(?<=\s|^)[a-z]/i', $this->name, $matches);
        $this->initials = strtoupper( implode('', $matches[0]) );
    }

    public function staff() {
        global $MASHPIA_DB;
        $staff_query = $MASHPIA_DB->prepare(
            'SELECT a.first, a.last, a.username, a.admin_email as email, a.admin_id FROM admins a '
            .'JOIN admin_auths aa USING( admin_id ) WHERE aa.auth="school" AND aa.id=?;'
        );
        $staff_query->execute([ $this->school_id ]);
        return $staff_query->fetchAll();
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

    public function updateSoldiers() {
        global $MASHPIA_DB;

        $update_sql = 'UPDATE users u LEFT JOIN classes c USING ( class_id )';
        $filter_sql = 'WHERE u.school_id = :id';
        // allow_parent_tasks
        if ( $this->attribute_is_dirty('allow_parent_tasks') ){
            $update = "$update_sql SET u.allow_parent_tasks = :v, c.allow_parent_tasks = :v $filter_sql";
            $update = $MASHPIA_DB->prepare( $update );
            $update->execute([ ':v' => $this->allow_parent_tasks, ':id' => $this->school_id ]);
        }
        // print_parent_tasks
        if ( $this->attribute_is_dirty('print_parent_tasks') ){
            $update = "$update_sql SET u.print_parent_tasks = :v, c.print_parent_tasks = :v $filter_sql";
            $update = $MASHPIA_DB->prepare( $update );
            $update->execute([ ':v' => $this->print_parent_tasks, ':id' => $this->school_id ]);
        }
        // pic_mission_type
        if ( $this->attribute_is_dirty('pic_mission_type') ){
            $update = "$update_sql SET u.pic_mission_type = :v, c.pic_mission_type = :v $filter_sql";
            $update = $MASHPIA_DB->prepare( $update );
            $update->execute([ ':v' => $this->pic_mission_type, ':id' => $this->school_id ]);
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
        if ( !$for_type )
            $for_type = $this->reg_type;

        $early_bird = $this->early_bird > new DateTime();
        
        return GlobalSettings::calculateChildFee(
            $for_type,      $this->child_fee,
            $to_soldier,    $early_bird,    $no_discount
        );
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
        foreach( $this->school_registrations as $reg_info ){
            if ( $reg_info->year == $year ) 
                return $reg_info;
        }
        // return the reg info
        return false;
    }

    public function register( $year, $amount, $admin_id ) {
        $this->school_era = null;

        $registration = new SchoolRegistration([
            'school_id' => $this->school_id,
            'year' => $year,
            'type' => $this->reg_type, 
            'fee' => $this->chayolei_fee, 
            'balance' => $this->balance, 
            'early_bird' => $this->earlyBird(),
            'amount_paid' => $amount,
            'admin_id' => $admin_id,
            'date_paid' => new DateTime()
        ]);

        return $registration->save() && $this->save();
    }

    public function earlyBird() {
        if ( $this->early_bird )
            return $this->early_bird;
        return new DateTime( '2018-09-07 00:00:00' );
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
                'school_makeup_id', 'school_settings', 'package_id', 'school_logo_id', 'school_logo_kiosk_id',
                'school_no_logo', 'school_file_id', 'kiosk_print', 'school_store', 'camp_id', 'add_on_one',
                'add_on_two', 'big_prizes_won', 'store_only', 'he_name_principal', 'he_name_p2', 'conf_pushka_users',
                'tanya_ord', 'school_type', 'col_show', 'tuition', 'reg_type', 'authorize_customer_profile_id', 
            ],
            'methods' => [ 'registration', 'logoPaths', 'customerProfile', 'staff' ]
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
}

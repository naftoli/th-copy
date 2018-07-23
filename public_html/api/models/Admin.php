<?php
// This class uses the Authorize.net gateway

class Admin extends ActiveRecord\Model implements JsonSerializable {
    static $before_create = ['createHelpdeskAccount'];
    // relationships 
    static $has_many = [ [ 'admin_auths' ] ];
    // validations
    static $validates_uniqueness_of = [
        [ 'username', 'message' => 'Usernames must be unique' ],
        [ 'admin_email', 'message' => 'Email Addresses must be unique' ],
    ];
    static $validates_format_of = [
        [ 'admin_email', 'message' => 'Email Addresses must be valid',
            'with' => '/^[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/' ]
    ];
    // prop mapping
    static $alias_attribute = [ 'email' => 'admin_email' ];
    // internalCaches
    private $customer_profile;
    public $login = false;

    //******************************* AUTH *******************************/
    /**
     * getAuthTypes
     * 
     * returns array of auth types ('user', 'school', 'class', 'camp')
     * 
     * @return array
     */
    public function getAuthTypes(){
        $types = [];
        foreach( $this->admin_auths as $auth ) {
            if ( !in_array( $auth->auth, $types ) ) $types[] = $auth->auth;
        }
        return $types;
    }

    /**
     * getAuthIds
     * 
     * return all id's for a given auth type
     *
     * @param string $auth_type
     * @return void
     */
    public function getAuthIds( $auth_type ) {
        $auth_ids = [];
        foreach( $this->admin_auths as $auth ){
            if ( $auth->auth === $auth_type ) $auth_ids[] = $auth->id;
        }
        return $auth_ids;
    }

    /**
     * authCode
     * 
     * return Auth code for React site
     *
     * @return string
     */
    public function authCode() {
        if ( $this->isHQ() ) return 'HQ';
        if ( $this->auth === 'ckidssuper' ) return 'CKIDS-ADMIN';
        // not HQ
        $auth_types = $this->getAuthTypes();
        if ( in_array( 'school', $auth_types ) ) return 'BC';
    }

    //******************************* LOGIN *******************************/
    // get all logins
    public function logins(){
        $logins = [];
        // Special HQ login
        if ( $this->isHQ() ) $logins[] = [
            'type' => 'HQ', 'id' => $this->admin_id, 'name' => 'Tzivos Hashem Headquarters', 
            'img' => '/mobile/img_new/TH Logo-colorful-svg.svg', 'code' => 'HQ'
        ];
        // add all the schools
        foreach( $this->getAuthIds( 'school' ) as $school_id ){
            $school = School::find( $school_id );
            $logins[] = [ 'type' => 'school', 'id' => $school_id, 'code' => 'BC',
                'name' => $school->school_name, 'img' => $school->logoPath()
            ];
        };
        if ( count( $this->getAuthIds( 'user') ) > 0  ) {
            $logins[] = [ 'type' => 'user', 'id' => $this->admin_id, 'code' => 'PARENT',
                'name' => 'My Parent Portal', 'img' => '/mobile/img_new/TH Logo-colorful-svg.svg'
            ];
        }
        return $logins;
    }
    // set the current login
    public function setLogin( $type = false, $id = false ){
        $logins = $this->logins();
        if ( !$type || !$id ) return $this->login = $logins[0]; // default to the first login
        foreach( $logins as $login ) {
            if ( $login['id'] == $id && $login['type'] == $type ) return $this->login = $login;
        }
        return $this->login = $logins[0];
    }

    // are we HQ?
    public function isHQ() {
        return $this->auth === 'super';
    }

    /**
     * shippingZone
     * 
     * Returns shipping zone (1, 2 or 3)
     *
     * @return int
     */
    public function shippingZone(){
        if ( $this->admin_country == '' || $this->admin_country == 'USA' ) return 1;
        else if ( $this->admin_country == 'Canada' ) return 2;
        return 3;
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
    public function createPaymentProfile( $payment_info ) { 
        // if we do not have a customer profile
        if ( !$this->customerProfile() instanceof classes\authorize\CustomerProfile ) {
            // create the account
            $payment_profile = classes\authorize\PaymentProfile::createBasicArray(
                $payment_info['cc-number'], $payment_info['cc-exp'], $payment_info['x_card_code']
            );
            $this->customer_profile = classes\authorize\CustomerProfile::create(
                "cth_admin_".$this->admin_id, $this->admin_email, false, $payment_profile
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

    // OTHER
    /**
     * createHelpdeskAccount
     * 
     * creates a Helpdesk account for the admin. Called when admin is created in the DBS
     *
     * @return boolean
     */
    public function createHelpdeskAccount(){
        if ( !isset( $_SERVER['DOCUMENT_ROOT'] ) ) return false;
        // import the required functions
        require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/connect.php');
        require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/functions.php');
        require_once($_SERVER["DOCUMENT_ROOT"].'/tasks/forms/functions/helpdesk_account_migration.php');
        // create the admin
        return create_admin( $this->to_array([
            'only' => ['first', 'last', 'admin_email', 'password' ]
        ]) );
    }

    // SERIALIZERS
    public function jsonSerialize() {
        return $this->to_array([
            'only' => [
                "admin_id", "username", "title", "first", "last", "lang", 
                "father", "mother", "father_pic", "mother_pic",
                "home_phone", "cell_phone", "admin_email"
            ],
            'methods' => [ 'authCode' ]
        ]);
    }
}
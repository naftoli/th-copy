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
    // SERIALIZERS
    public function jsonSerialize() {
        return $this->to_array([
            'only' => [
                "admin_id", "username", "title", "first", "last", "lang", 
                "father", "mother", "father_pic", "mother_pic",
                "home_phone", "cell_phone", "admin_email"
            ]
        ]);
    }

    // AUTH FUNCTIONS
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

    // are we HQ?
    public function isHQ() {
        return $this->auth === 'super';
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
        // not HQ
        $auth_types = $this->getAuthTypes();
        if ( in_array( 'school', $auth_types ) ) return 'BC';
    }

    /**
     * shippingZone
     * 
     * Returns shipping zone (1, 2 or 3)
     *
     * @return int
     */
    public function shippingZone(){
        if ( $this->admin_country == '' || $this->admin_country == 'USA' )
            return 1;
        else if ( $this->admin_country == 'Canada' )
            return 2;
        return 3;
    }

    //********************************** PAYMENTS **********************************/
    public function customerProfile(){
        if ( $this->customer_profile instanceof CustomerProfile )
            return $this->customer_profile;
        if( !$this->authorize_customer_profile_id )
            return false;
        // load the required classes
        return $this->customer_profile = new classes\authorize\CustomerProfile(
            $this->authorize_customer_profile_id
        );
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
}
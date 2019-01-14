<?php
include_once( __DIR__ . '/../auth/classes/Auth.php' );
include_once( __DIR__ . '/../auth/classes/Login.php' );
include_once( __DIR__ . '/traits/BuildModel.php' );
include_once( __DIR__ . '/../tools/emails/index.php' );
// * This class uses the Authorize.net gateway

use mashpia\api\auth\Login as Login;

class Admin extends ActiveRecord\Model implements JsonSerializable {
    use \traits\BuildModel;
    
    static $before_create = ['createHelpdeskAccount'];
    static $before_update = ['handleChanges'];
    // relationships 
    static $has_many = [ [ 'admin_auths' ] ];
    // validations
    static $validates_uniqueness_of = [
        [ 'username', 'message' => 'must be unique' ],
        [ 'admin_email', 'message' => 'addresses must be unique' ],
        [ 'chabad_org_shliach_id', 'message' => 'connections must be unique' ],
        [ 'google_id', 'message' => 'connections must be unique' ],
    ];
    // static $validates_format_of = [
    //     [ 'admin_email', 'message' => 'addresses must be valid',
    //         'with' => '/^[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/' ]
    // ];
    // prop mapping
    public static $alias_attribute = [
        'city' => 'admin_city',          'zip' => 'admin_postal',
        'state' => 'admin_state',        'email' => 'admin_email',
        'country' => 'admin_country',    'work' => 'admin_phone_work',
        'cell' => 'admin_phone_mobile',  'address' => 'admin_address1',
        'address2' => 'admin_address2',  'shliach_id' => 'chabad_org_shliach_id'
    ];
    // internalCaches
    private $customer_profile;
    private $logins = [];
    public $login = false;

    //**********************************************************************/
    //****************************** HELPERS *******************************/
    //**********************************************************************/
    /**
     * name([ $withTitle = true ])
     * @return string the name with the title by default ( pass false to remove )
     */
    public function name( $withTitle = true ) {
        $name = $this->first . ' ' . $this->last;
        if ( $withTitle ) return $this->title . ' ' . $name;
        return $name;
    }

    //*******************************************************************************/
    //********************************** CALLBACKS **********************************/
    //*******************************************************************************/
    /**
     * call the correct functions when specific attributes are changed
     */
    public function handleChanges() {
        // if email was changed
        if ( $this->attribute_is_dirty('admin_email') ){}
        // if password was changed
        else if ( $this->attribute_is_dirty('username') || $this->attribute_is_dirty('password') ) {}
        // all's good, return false to prevent update
        return true;
    }

    /**
     * createHelpdeskAccount
     * creates a Helpdesk account for the admin. Called when admin is created in the DBS
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

    //*******************************************************************************/
    //*********************************** SECURITY **********************************/
    //*******************************************************************************/

    public function authenticate( $password ){
        return $password === $this->password;
    }

    public function resetPassword() {
        global $MASHPIA_DB;
        // generate an 8 digit selector and a 32 digit token
        $selector = bin2hex(random_bytes(8));
        $token = random_bytes(32);

        $url = sprintf('%s/api/password_reset?%s', MASHPIA_ABS_URL, http_build_query([
            'selector' => $selector,
            'validator' => bin2hex($token)
        ]));

        // Token expires in one hour
        $expires = new DateTime('NOW');
        $expires->add(new DateInterval('PT01H')); // 1 hour

        // delete any existing password reset requests
        $MASHPIA_DB->exec( 'DELETE FROM password_reset WHERE admin_id = '.$this->admin_id.';' );
        
        // insert the password_reset request into the database
        $reset = 'INSERT INTO password_reset ( admin_id, selector, token, expires, sent_to ) VALUES ( ?, ?, ?, ?, ? )';
        $reset = $MASHPIA_DB->prepare( $reset );
        $reset->execute([ $this->admin_id, $selector, hash('sha256', $token), $expires->format('U'), $this->email ]);

        return MashpiaEmails::passwordReset( $this->email, $this->username, $url );
    }

    public function sendParentEmail() {
        return MashpiaEmails::sendParentEmail( $this->email, $this->username, $this->password );
    }

    //*********************************************************************/
    //******************************* LOGIN *******************************/
    //*********************************************************************/
    // get all logins
    public function logins(){
        if ( $this->logins )
            return $this->logins;
        // Special HQ login
        if ( $this->isHQ() )
            try { $this->logins[] = new Login( 'HQ', $this->admin_id, $this ); }
            catch ( \ActiveRecord\RecordNotFound $e ) {}
        // add all the institutions
        foreach( $this->getAuthIds( 'institution' ) as $inst_id )
            try { $this->logins[] = new Login( 'institution', $inst_id ); }
            catch ( \ActiveRecord\RecordNotFound $e ) {}
        // add all the schools
        foreach( $this->getAuthIds( 'school' ) as $school_id )
            try { $this->logins[] = new Login( 'school', $school_id ); }
            catch ( \ActiveRecord\RecordNotFound $e ) {}
        // add all classes
        foreach( $this->getAuthIds( 'class' ) as $class_id )
            try { $this->logins[] = new Login( 'class', $class_id ); }
            catch ( \ActiveRecord\RecordNotFound $e ) {}
        // add parent account
        if ( count( $this->getAuthIds( 'user') ) > 0 || $this->is_parent ) {
            try { $this->logins[] = new Login( 'PARENT', $this->admin_id, $this ); }
            catch ( \ActiveRecord\RecordNotFound $e ) {}
        }
        // if there are no logins, send a 'BLANK' login code to the client
        if ( count( $this->logins ) === 0 ) {
            try { $this->logins[] = new Login( 'BLANK', $this->admin_id, $this ); }
            catch ( \ActiveRecord\RecordNotFound $e ) {}
        }
        return $this->logins;
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
        if ( !$this->admin_auths ) return $auth_ids;
        foreach( $this->admin_auths as $auth ){
            if ( $auth->auth === $auth_type ) $auth_ids[] = $auth->id;
        }
        return $auth_ids;
    }

    // set the current login
    public function setLogin( $type = false, $id = false, $override = false ){
        $logins = $this->logins();

        if ( $override ) {
            $this->login = new Login( $type, $id );
            $this->logins[] = $this->login;
            return $this->login;
        }

        if ( ( !$type || !$id ) && count( $logins ) > 0 ) {
            return $this->login = $logins[0]; // default to the first login
        }
            
        
        foreach( $logins as $login ) {
            if ( $login->id == $id && $login->type == $type ) {
                return $this->login = $login;
            }
        }

        if ( count( $logins ) > 0 )
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
        if ( $this->admin_country == '' || in_array( strtolower( $this->admin_country ), ['usa', 'united states'] ) ) return 1;
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

    /**
     * createPaymentProfile
     * 
     * adds this payment profile with the payment procesor. Creates Authorize.net account if one does not already exist.
     * 
     * @param array $payment_profile [ cc-number, cc-exp, x_card_code ]
     * 
     * @return
     *      success: classes\authorize\PaymentProfile
     *      error: string
     */
    public function createPaymentProfile( $payment_info ) {
        // if we do not have a customer profile
        if ( !$this->customerProfile() instanceof classes\authorize\CustomerProfile ) {
            // create the account
            $payment_profile = classes\authorize\PaymentProfile::createBasicArray(
                $payment_info['cc-number'], $payment_info['cc-exp'], $payment_info['x_card_code']
            );
            $this->customer_profile = classes\authorize\CustomerProfile::create(
                "cth_admin_".$this->admin_id, $this->admin_email, $this->name(), $payment_profile
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

    //*********************************************************************************/
    //********************************** SERIALIZERS **********************************/
    //*********************************************************************************/
    public function jsonSerialize() {
        return $this->to_array([
            'only' => [
                'admin_id', 'username',         'title',        'first',        'last',
                'lang',     'home_phone',       'cell_phone',   'admin_email',  'chabad_org_shliach_id',
                'photo',    'admin_phone_work', 'google_id',    'admin_phone_mobile',   'beta',
            ],
            'methods' => [ 'logins' ]
        ]);
    }
}

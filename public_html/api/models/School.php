<?php
include_once( __DIR__ . '/traits/BuildModel.php' );

class School extends ActiveRecord\Model implements JsonSerializable {
    use traits\BuildModel;
    private $customer_profile;
    // relationships
    static $has_many = [
        [ 'school_registrations' ], 
        [ 'platoons', 'order' => 'class_grade, class_sub', ], 
        [ 'users' ] 
    ];

    // ******************************* GETTERS *******************************
    /**
     * getRegInfo
     * 
     * get the current registratio info object for the school.
     * Returns defaults if none exist
     *
     * @param string $year
     * @return SchoolRegistration
     */
    public function getRegInfo( $year = false ){
        $year = $year ? $year : GlobalSettings::getRegistrationYear( $this->school_id );
        $reg_info = SchoolRegistration::getDefault( $this->school_id, $this->reg_type, $year );
        // check for non-default option
        foreach( $this->school_registrations as $custom_reg_info ){
            if ( $custom_reg_info->year == $year ) 
                return $reg_info = $custom_reg_info;
        }
        // return the reg info
        return $reg_info;
    }

    public function logoPath(){
        return "/schoolLogos/$this->logo";
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
    public function createPaymentProfile( $payment_info, $email='example@example.com' ) { 
        // if we do not have a customer profile
        if ( !$this->customerProfile() instanceof classes\authorize\CustomerProfile ) {
            // create the account
            $payment_profile = classes\authorize\PaymentProfile::createBasicArray(
                $payment_info['cc-number'], $payment_info['cc-exp'], $payment_info['x_card_code']
            );
            $this->customer_profile = classes\authorize\CustomerProfile::create(
                "CTH_".$this->school_id, $email, false, $payment_profile
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
            'except' => [
                'cc_first', 'cc_last', 'cc_address', 'cc_state', 'cc_zip', 'cc_number', 'cc_exp', 'cc_cvv',
                'cc_approval_number',  'authorize_customer_profile_id', 'authorize_payment_profile_id'
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
}
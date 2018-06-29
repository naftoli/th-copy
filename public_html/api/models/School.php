<?php
include_once( __DIR__ . '/traits/BuildModel.php' );

class School extends ActiveRecord\Model implements JsonSerializable {
    use traits\BuildModel;
    
    // relationships
    static $has_many = [ ['school_reg_infos'], [ 'platoons' ], [ 'users' ] ];

    // ******************************* GETTERS *******************************
    /**
     * getRegInfo
     * 
     * get the current registratio info object for the school.
     * Returns defaults if none exist
     *
     * @param string $year
     * @return SchoolRegInfo
     */
    public function getRegInfo( $year = false ){
        $year = $year ? $year : GlobalSettings::getRegistrationYear();
        $reg_info = SchoolRegInfo::getDefault( $this->school_id, $year );
        // check for non-default option
        foreach( $this->school_reg_infos as $custom_reg_info ){
            if ( $custom_reg_info->year == $year ) $reg_info = $custom_reg_info;
        }
        // return the reg info
        return $reg_info;
    }

    public function logoPath(){
        return "/schoolLogos/$this->logo";
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
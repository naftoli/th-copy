<?php
class School extends ActiveRecord\Model implements JsonSerializable {
    // relationships
    static $has_many = [ ['school_reg_infos'], [ 'plattons' ], [ 'users' ] ];


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
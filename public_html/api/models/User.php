<?php
class User extends ActiveRecord\Model implements JsonSerializable {
    // relationships
    static $belongs_to = [
        [ 'school' ], [ 'platton', 'foreign_key' => 'class_id' ]
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
        return "/mobile/img_new/boy-color-green-svg.svg";
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
        $result = [ 'chayolei'  => GlobalSettings::getRegCost( $reg_info->type, $early_bird ) ];
        // add chidon if user is in grade 4+
        if ( $this->platton->class_grade >= 4 )
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
        if ( $this->platton->class_grade >= 4 )
            $result[ 'chidon' ] = !!$row[ 'th_chidon_id' ];
        return $result;
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
            'include' => [ 
                'school' => [ 'only' => [ 'school_id', 'school_name' ] ],
                'platton' => [ 'only' => [ 'class_id', 'class_grade', 'class_sub' ] ]
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
            'only' => [ 'user_serial', 'first', 'last' ],
            'include' => [ 
                'school' => [ 'only' => [ 'school_id', 'school_name' ] ],
                'platton' => [ 'only' => [ 'class_id', 'class_grade', 'class_sub' ] ]
            ]
        ]);
    }
}
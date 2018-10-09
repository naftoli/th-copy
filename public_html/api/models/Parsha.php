<?php
include_once( __DIR__ . '/traits/BuildModel.php' );

class Parsha extends ActiveRecord\Model implements JsonSerializable {
    use \traits\BuildModel;
    
    static $table_name = 'parshos';

    // ******************************* HELPER FUNCTIONS *******************************
    public function start_date() {
        return ( new \DateTime('@' . jdtounix( $this->start ) ) )->format( SQL_DATE_FORMAT );
    }

    public function end_date() {
        return ( new \DateTime('@' . jdtounix( $this->end ) ) )->format( SQL_DATE_FORMAT );
    }

    // ******************************* SERIALIZERS *******************************
    public function jsonSerialize(){
        return $this->to_array([
            'methods' => [ 'start_date', 'end_date' ],
        ]);
    }
}
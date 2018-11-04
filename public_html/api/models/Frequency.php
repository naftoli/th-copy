<?php
include_once( __DIR__ . '/traits/BuildModel.php' );

class Frequency extends ActiveRecord\Model implements JsonSerializable {
    use \traits\BuildModel;

    static $table_name = 'frequencies';
    static $primary_key = 'frequency_id';

    // ******************************* SERIALIZERS *******************************
    public function jsonSerialize(){
        return $this->to_array();
    }
}

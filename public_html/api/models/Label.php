<?php
include_once( __DIR__ . '/traits/BuildModel.php' );

class Label extends ActiveRecord\Model implements JsonSerializable {
    use \traits\BuildModel;

    static $belongs_to = [ ['frequency', 'foreign_key' => 'frequency_id'] ];

    public function isDaily() {
        return $this->frequency->frequency_name == 'Daily';
    }

    // ******************************* SERIALIZERS *******************************
    public function jsonSerialize(){
        return $this->to_array([
            'only' => [
                'label_id', 'label_name', 'active'
            ],
            'include' => [ 'frequency' ]
        ]);
    }
}

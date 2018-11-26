<?php
namespace traits;

trait BuildModel {
    public static function build( $attributes ) {
        $instance = new self( [], true, false, true );
        $valid_attributes = $instance->attributes();
        $valid_attributes = array_merge( $valid_attributes, $instance::$alias_attribute );
        foreach( $attributes as $key => $value ){
            if ( array_key_exists( $key, $valid_attributes ) )
                $instance->{ $key } = $value;
        }
        return $instance;
    }

    public static function loadFromAttributes( $attributes ) {
        $instance = new self( [], true, false, false );
        $valid_attributes = $instance->attributes();
        foreach( $attributes as $key => $value ){
            if ( array_key_exists( $key, $valid_attributes ) )
                $instance->{ $key } = $value;
        }
        $instance->reset_dirty();
        return $instance;
    }

    public function bulkUpdate( $updates ) {
        $columns = array_keys( self::table()->columns );
        foreach( $updates as $key => $value ) {
            if ( in_array( $key, $columns ) )
                $this->{ $key } = $updates[ $key ];
        }
    }
}
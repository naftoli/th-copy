<?php
namespace traits;

trait BuildModel {
    public static function build( $attributes ) {
        $instance = new self( [], true, false, true );
        $valid_attributes = $instance->attributes();
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
}
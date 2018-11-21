<?php
require_once ( __DIR__ . '/../../api/header/db.php' );

class ChabadMosad
{
    public function __construct( $mosadInfo ) {
        $this->id = trim( $mosadInfo->Id );
        $this->mosad_id = trim( $mosadInfo->Id );
        $this->name = trim( $mosadInfo->Institution );
        $this->address = trim( $mosadInfo->Address );
        $this->address2 = trim( $mosadInfo->Address2 );
        $this->city = trim( $mosadInfo->City );
        $this->state = trim( $mosadInfo->State );
        $this->zip = trim( $mosadInfo->PostCode );
        $this->country = trim( $mosadInfo->Country );
        $this->phone = trim( $mosadInfo->Phone ); // add the + to note the country code
        $this->fax = trim( $mosadInfo->Fax );
        $this->url = trim( $mosadInfo->WebAddress );
        $this->getTypes();
        // add the country code
        if ( preg_match( '/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/', $this->phone ) )
            $this->phone = '+1'.$this->phone;
        else
            $this->phone = '+'.$this->phone;
    }

    private function getTypes() {
        global $MASHPIA_DB;

        $sql = "SELECT mosad_type FROM chabad_mosdos 
                WHERE mosad_id = " . $this->id;
                
        $result = $MASHPIA_DB->query( $sql );
        if ( $result->rowCount() > 0 ) {
            $this->hasTypes = true;
            while ( $row = $result->fetch() ) {
                $this->types[] = $row['mosad_type'];
            }
        } else {
            $this->hasTypes = false;
        }
    }
}

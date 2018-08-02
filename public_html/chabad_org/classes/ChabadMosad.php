<?php
require '../db.php';

class ChabadMosad
{
    public function __construct( $mosadInfo ) {
        $this->id = $mosadInfo->Id;
        $this->name = $mosadInfo->Institution;
        $this->address = $mosadInfo->Address;
        $this->address2 = $mosadInfo->Address2;
        $this->city = $mosadInfo->City;
        $this->state = $mosadInfo->State;
        $this->zip = $mosadInfo->PostCode;
        $this->country = $mosadInfo->Country;
        $this->phone = $mosadInfo->Phone;
        $this->fax = $mosadInfo->Fax;
        $this->url = $mosadInfo->WebAddress;
        $this->getTypes();
    }

    private function getTypes() {
        $sql = "SELECT mosad_type FROM chabad_mosdos 
                WHERE mosad_id = " . $this->id;
        $result = mysql_query( $sql );
        if ( mysql_num_rows( $result ) > 0 ) {
            $this->hasTypes = true;
            while ( $row = mysql_fetch_assoc( $result ) ) {
                $this->types[] = $row['mosad_type'];
            }
        } else {
            $this->hasTypes = false;
        }
    }
}

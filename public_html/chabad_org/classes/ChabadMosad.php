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
        $this->types = $this->getTypes();
    }

    private function getTypes() {
        $types = array();
        $sql = "SELECT mosad_type FROM chabad_mosdos 
                WHERE mosad_id = " . $this->id;
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $types[] = $row['mosad_type'];
        }
        return $types;
    }

    public function getIDs() {
        $ids = array();
        $sql = "SELECT DISTINCT mosad_id FROM chabad_mosdos 
                WHERE (mosad_id = " . $this->id . " or primary_mosad_id = " . $this->id . ")";
        $result = mysql_query( $sql );
        while ($row = mysql_fetch_assoc( $result )) {
            $ids[] = $row['mosad_id'];
        }
        return $ids;
    }
}

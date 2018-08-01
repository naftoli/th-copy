<?php
require '../db.php';

class ChabadMosad
{
    private $id;

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
        $this->types = $this->getMosadInfo();
    }

    private function getMosadInfo() {
        $info = array();
        $sql = "SELECT * FROM chabad_mosdos 
                WHERE (mosad_id = " . $this->id . " or primary_mosad_id = " . $this->id . ") 
                ORDER BY name, mosad_type, mosad_category";
        $result = mysql_query( $sql );
        while ($row = mysql_fetch_assoc( $result )) {
            $info[] = $row;
        }
        return $info;
    }
}

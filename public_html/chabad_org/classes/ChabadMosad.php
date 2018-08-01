<?php
require '../db.php';

class ChabadMosad
{
    private $id;
    private $info;

    public function __construct( $mosadInfo ) {
        $this->id = $mosadInfo->Id;
        $this->info['name'] = $mosadInfo->Institution;
        $this->info['address'] = $mosadInfo->Address;
        $this->info['address2'] = $mosadInfo->Address2;
        $this->info['city'] = $mosadInfo->City;
        $this->info['state'] = $mosadInfo->State;
        $this->info['zip'] = $mosadInfo->PostCode;
        $this->info['country'] = $mosadInfo->Country;
        $this->info['phone'] = $mosadInfo->Phone;
        $this->info['fax'] = $mosadInfo->Fax;
        $this->info['url'] = $mosadInfo->WebAddress;
        $this->info['types'] = $this->getMosadInfo();
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

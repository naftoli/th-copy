<?php
require 'ChabadAuth.php';
require 'ChabadMosad.php';

class ChabadShliach 
{
    private $key;
    private $shliachID;
    private $personalInfo;
    private $mosdos;
    private $error;

    public function __construct( $key ) {
        $this->key = $key;
        $this->shliachID = 0;
        $this->personalInfo = array();
        $this->mosdos = array();
        $this->error = null;
    }

    // check if user exists and is valid on chabad.org
    public function authenticate() {
        $url = '/api/login/authenticate';
        $info = json_decode( ChabadAuth::connectToApi( $url, $this->key ) );
        if ( isset( $info->IsValidUser ) && $info->IsValidUser == true ) {
            $this->shliachID = $info->ShliachId;
            return true;
        } else {
            $this->error = "Invalid username / password.";
            return false;
        }
    }

    // get shliach info from chabad.org api
    public function setPersonalInfo() {
        if ($this->shliachID) {
            $url = "/api/centers/people/$this->shliachID";
            $result = json_decode( ChabadAuth::connectToApi( $url, $this->key ) );
            $info = $result->People[0];
            // convert to more user friendly array
            $this->personalInfo['title'] = $info->Title;
            $this->personalInfo['first'] = $info->FirstName;
            $this->personalInfo['last']  = $info->LastName;
            $this->personalInfo['address'] = $info->Address;
            $this->personalInfo['address2'] = $info->Address2;
            $this->personalInfo['city'] = $info->City;
            $this->personalInfo['state'] = $info->State;
            $this->personalInfo['zip'] = $info->PostCode;
            $this->personalInfo['country'] = $info->Country;
            $this->personalInfo['phone'] = $info->HomePhone;
            $this->personalInfo['centers'] = $info->Centers;
            return true;
        } else {
            $this->error = "Invalid shliach.";
            return false;
        }
    }

    // get mosdos connected to shliach from chabad.org api
    public function setMosdos() {
        if ( $this->personalInfo['centers'] ) {
            $centers = $this->personalInfo['centers'];
            foreach ($centers as $centerID) {
                //$url = "/api/centers/$centerID?includeDepartments=true";
                $url = "/api/centers/$centerID";
                $mosdos = json_decode( ChabadAuth::connectToApi( $url, $this->key ) );
                foreach ( $mosdos->Centers as $mosad ) {
                    $chabadMosad = new ChabadMosad( $mosad );
                    // only add mosad if it exists in our database
                    if ( !empty( $chabadMosad['types'] ) ) {
                        $this->mosdos[$centerID][] = $chabadMosad;
                    }
                }
            }
            return true;
        } else {
            $this->error = "Invalid reponse from chabad.org";
            return false;
        }
    }

    public function getMosdos() {
        return $this->mosdos;
    }

    public function getError() {
        return $this->error;
    }
}
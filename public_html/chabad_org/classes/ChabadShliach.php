<?php
require 'ChabadAuth.php';
require 'ChabadMosad.php';

class ChabadShliach 
{
    private $key;
    private $shliachID;
    private $centers;
    private $error;
    private $debug;

    public function __construct( $key ) {
        $this->key = $key;
        $this->shliachID = 0;
        $this->centers = array();
        $this->error = null;
        $this->debug = false;
    }

    // check if user exists and is valid on chabad.org
    public function authenticate() {
        $url = '/api/login/authenticate';
        $info = json_decode( ChabadAuth::connectToApi( $url, $this->key ) );
        if ( isset( $info->IsValidUser ) && $info->IsValidUser == true ) {
            $this->shliachID = trim( $info->ShliachId );
            return true;
        } else {
            $this->error = "Invalid username / password.";
            return false;
        }
    }

    public function setDebug( $bool ) {
        $this->debug = $bool;
    }

    // get shliach info from chabad.org api
    public function setPersonalInfo() {
        if ($this->shliachID) {
            $url = "/api/centers/people/$this->shliachID";
            $result = json_decode( ChabadAuth::connectToApi( $url, $this->key ) );
            $info = $result->People[0];
            if ( $this->debug ) {
                echo "Personal Info:";
                print_r( $info );
            }
            $this->title = trim( $info->Title );
            $this->first = trim( $info->FirstName );
            $this->last  = trim( $info->LastName );
            $this->address = trim( $info->Address );
            $this->address2 = trim( $info->Address2 );
            $this->city = trim( $info->City );
            $this->state = trim( $info->State );
            $this->zip = trim( $info->PostCode );
            $this->country = trim( $info->Country );
            $this->phone = trim( $info->HomePhone );
            $this->centers = $info->Centers;
            return true;
        } else {
            $this->error = "Invalid shliach.";
            return false;
        }
    }

    // ability to hardcode centers for testing
    public function setCenters( array $centers ) {
        $this->centers = $centers;
    }

    // get mosdos connected to shliach from chabad.org api
    public function setMosdos() {
        if ( $this->centers ) {
            foreach ($this->centers as $centerID) {
                $url = "/api/centers/$centerID?includeDepartments=true";
                $mosdos = json_decode( ChabadAuth::connectToApi( $url, $this->key ) );
                if ( $this->debug ) {
                    echo "Centers Info:";
                    print_r( $mosdos );
                }

                foreach ( $mosdos->Centers as $mosad ) {
                    $chabadMosad = new ChabadMosad( $mosad );
                    if ( $chabadMosad->hasTypes ) $this->mosdos[] = $chabadMosad;
                }
            }
            
            if ( $this->debug ) {
                echo "Mosdos Info:";
                print_r( $this->mosdos );
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
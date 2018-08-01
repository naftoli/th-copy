<?php
require 'ChabadAuth.php';
require 'ChabadMosad.php';

class ChabadShliach 
{
    private $key;
    private $shliachID;
    private $centers;
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
            $this->title = $info->Title;
            $this->first = $info->FirstName;
            $this->last  = $info->LastName;
            $this->address = $info->Address;
            $this->address2 = $info->Address2;
            $this->city = $info->City;
            $this->state = $info->State;
            $this->zip = $info->PostCode;
            $this->country = $info->Country;
            $this->phone = $info->HomePhone;
            $this->centers = $info->Centers;
            return true;
        } else {
            $this->error = "Invalid shliach.";
            return false;
        }
    }

    // hardcode centers for testing
    public function setCenters( array $centers ) {
        $this->centers = $centers;
    }

    // get mosdos connected to shliach from chabad.org api
    public function setMosdos() {
        if ( $this->centers ) {
            $mosdosInfo = array();
            foreach ($this->centers as $centerID) {
                //$url = "/api/centers/$centerID?includeDepartments=true";
                $url = "/api/centers/$centerID";
                $mosdos = json_decode( ChabadAuth::connectToApi( $url, $this->key ) );
                foreach ( $mosdos->Centers as $mosad ) {
                    $chabadMosad = new ChabadMosad( $mosad );
                    // only add mosad if it exists in our database
                    if ( !empty( $chabadMosad->types ) ) {
                        $mosdosInfo[$centerID][] = $chabadMosad;
                    }
                }
            }

            // mosad info is complicated in terms of how it's returned from the api
            // one of the mosdos can be the "primary_mosad" which in our db is the "primary_mosad_id" 
            // this id can be connected with multiple mosdos which many of them is also returned by the api
            // so this function tries to make some sense out of the information
            $this->createMosdos( $mosdosInfo );           

            return true;
        } else {
            $this->error = "Invalid reponse from chabad.org";
            return false;
        }
    }

    private function createMosdos( $mosdos ) {
        // find mosad with largest array of types
        $num = count( $mosdos );
        $totals = array();
        for ($i = 0; $i < $num; $i++) {
            $total = count( $mosdos[$i]->types );
            $totals[$i] = $total;
        }
        // order by totals desc
        arsort( $totals );
        // get index of highest total
        $index = key( $totals );

        // set mosdos to all types of main mosad and see if passed in mosdos array has any extra info
        foreach ( $mosdos[$index]->types as $type ) {
            $id = $type['mosad_id'];
            $found = false; // flag to know if we found more info or not
            foreach ( $mosdos as $mosad ) {
                if ( $mosad->id == $id ) { // we have a match to give us more info
                    // add category and type info from db
                   $mosad->category = $type['mosad_category'];
                   $mosad->type = $type['mosad_type'];
                   $this->mosdos[] = $mosad;
                }
            }
            if ( !$found ) {
                $mosad = (object) array();
                $mosad->id = $type['mosad_id'];
                $mosad->name = $type['name'];
                $mosad->category = $type['mosad_category'];
                $mosad->type = $type['mosad_type'];
                $this->mosdos[] = $mosad;
            }
        }
        print_r( $this->mosdos );
    }

    public function getMosdos() {
        return $this->mosdos;
    }

    public function getError() {
        return $this->error;
    }
}
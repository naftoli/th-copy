<?php

namespace raffles\weekly;

use \DateTime;
use \Imagick;

class Prize {
    //***************************
    // variables
    // **************************
    
    public $prize_id;
    public $name;
    public $picture;
    public $thumbnail;
    public $type_of_prize;
    public $date_created;
    
    public $raffles = [];
    
    /*
     * Prize::load($prize_id)
     *
     * $prize_id => the prize_id in the database
     *
     * Loads a prize from the database and returns an instance of the Prize object
     */
    public static function load($prize_id){
        $instance = new self(); // create instance to return
        
        $prize_id = mysql_real_escape_string($prize_id); // protect from sql injection
        
        $sql = "SELECT * FROM prizes WHERE prize_id = $prize_id LIMIT 1"; // there should only be one object with this pk
        $query = mysql_query($sql); // run the query
        
        if ($query && mysql_num_rows($query) > 0){ // if there is a result
            $row = mysql_fetch_assoc($query); // get the row
            return self::loadFromRow($row);
        } else {
            if($debug) echo '<br/>MySQL error: ('.mysql_errno().') '.mysql_error();
            return false;
        }
    }
    
    /*
     * Prize::loadFromRow($row)
     *
     * $row => the row in the database
     *
     * Takes a row from the database and returns an instance of a Prize object
     */
    public static function loadFromRow($row){
        $instance = new self(); // create a new instance
        
        foreach ($row as $prop => $val) { // for each column returned from the database
            // convert the times to time values
            if($prop === "date_created" && $val) {
                $val = new DateTime($val); // set it to a datetime object for cool features
            }
            $instance->{$prop} = $val; // set the property on the instance
        } // end foreach
        return $instance; // return the instance that we created
    }
    
    /*
     * Prize::create($props_array)
     *
     * $props_array => associative array of the values used to create the object
     * $picture => the picture to use when creating the image
     *
     * Loads a prize from the database and returns an instance of the Prize object
     */
    public static function create($name, $type_of_prize, $picture){
        $instance = new self(); // the instance used to store the data and return it to the user if successful
        
        $instance->name = $name; // set the property
        $instance->type_of_prize = $type_of_prize; // set the property
        
        if(!$instance->add_image($picture)) return false;
        
        // compute the keys to insert
        $sql = "INSERT INTO prizes (name, type_of_prize, picture, thumbnail) VALUES ('".implode("', '", [$instance->name, $instance->type_of_prize, $instance->picture, $instance->thumbnail])."');";

        $query  = mysql_query($sql); // returns false if an error happens
        
        if($query){ // if it was inserted
            $instance->prize_id = mysql_insert_id(); // set the ID
            // create shipping code
            $instance->shipping_code = "WP" . $instance->prize_id;
            $sql = "UPDATE prizes SET shipping_code='".$instance->shipping_code."' WHERE prize_id=".$instance->prize_id.";";
            $query = mysql_query($sql);
            return $instance; // and return the instance
        } else { // the insert query failed
            echo 'MySQL error: ('.mysql_errno().') '.mysql_error(); // print the mysql error
            return false; // return false
        }
    }
    
    /*
     * Prize::loadAll($filter)
     *
     * $filter => SQL filter string
     *
     * retruns all the prizes within that filter
     */
    public static function loadAll($filter=""){
        $prizes = []; // the array to return
        
        $sql = "SELECT * FROM prizes $filter;"; // include the user filter in the query
        $query = mysql_query($sql);
        
        while($row = mysql_fetch_assoc($query)){
            $prizes[] = self::loadFromRow($row); // generate the raffe instance and add it to the array
        }
        
        if(!$query){
            echo 'MySQL error: ('.mysql_errno().') '.mysql_error(); // print the mysql error
        }
        return $prizes; // return the array
    }
    // ************************
    // functions
    // ************************
    
    // persist the current object to the database
    public function update() {
        $sql = "UPDATE prizes SET name='".$this->name."', type_of_prize='".$this->type_of_prize.
                "', picture='".$this->picture."', thumbnail='".$this->thumbnail."' WHERE prize_id=".$this->prize_id;
        // run the query
        $query = mysql_query($sql);
        
        return ($query ? true : false);
    }
    
    // remove the current object from the database
    public function destroy() {
        $sql = "DELETE FROM prizes WHERE prize_id=".$this->prize_id.";";
        // run the query
        $query = mysql_query($sql);
        // and set the result
        $result = $query ? true : false;
        // delete the images once it has been deleted
        if($result){
            unlink($_SERVER["DOCUMENT_ROOT"].$this->picture);
            unlink($_SERVER["DOCUMENT_ROOT"].$this->thumbnail);
        }
        
        return $result;
    }
    
    /*
     *  prize->add_image($file)
     *
     *  crop and store the image on the drive and set internal variables to what they need to be
     *
     *  $file #=> the uploaded file that we are adding as the image
    */
    public function add_image($file){
        //echo "<pre>"; print_r($file); echo "</pre>"; exit;
        if (is_uploaded_file($file['tmp_name'])) {
			$file_name = $file['name'];
            $target = $_SERVER["DOCUMENT_ROOT"].'/raffles/img/' . $file_name; // move the file to the right place
            // make sure file doesn't exist
            $i = 1;
            while (file_exists($target)) {
                $file_name = 'v' . $i++ .".". $file['name'];
                $target = $_SERVER["DOCUMENT_ROOT"].'/raffles/img/' . $file_name; // move the file to the right place
            }
            
            if(strlen($target) > 100) return false; // varchar is limited to 100 chars
            if (move_uploaded_file($file['tmp_name'], $target)) { // actually move it
                try { // the image was uploaded. so lets try to scale it down
                    //create thumb from image
                    $image = new Imagick( $target );
                    $thumb = new Imagick( $target );
                    $image->thumbnailImage( 250, 0, false, true ); // this will flip images that have bad orientation data
                    $thumb->thumbnailImage( 50, 0, false, true); // this will flip images that have bad orientation data
                    try {// attempt to fix the rotation
                        if ((exif_imagetype($target) == IMAGETYPE_JPEG || exif_imagetype($target) == IMAGETYPE_TIFF_II || exif_imagetype($target) == IMAGETYPE_TIFF_MM ) && exif_read_data($target)){
                            $exif = @exif_read_data($target); // read the data
                            $orientation = isset( $exif['Orientation'] ) ? $exif['Orientation'] : false; // get the orientation
                            if($orientation){ // this will only run if orientation is set which can only happen if $_POST['action'] is set to fix
                                switch($orientation) {  
                                    case 3: $image->rotateimage("#FFF", 180); $thumb->rotateimage("#FFF", 180); break; // upside down
                                    case 6: $image->rotateimage("#FFF", 90); $thumb->rotateimage("#FFF", 90); break; // rotate 90 degrees CW
                                    case 8: $image->rotateimage("#FFF", -90); $thumb->rotateimage("#FFF", -90);break; // rotate 90 degrees CCW
                                }
                            }
                        }
                    } catch (ImagickException $e) {} // rotation not fixed. do nothing about it.
                    // save the image
                    $image->writeImage($target);
                    // save the thumbnail
                    $thumb_target = '/raffles/thumb/' . $file_name; // move the file to the right place
                    $thumb->writeImage( $_SERVER["DOCUMENT_ROOT"]. $thumb_target ); // save it (document root added to ensure it is the correct place)
                    // destroy the objects
                    $image->destroy(); $thumb->destroy();// destroy the image
                    // delete the old images if they are there
                    if($this->picture) unlink($_SERVER["DOCUMENT_ROOT"].$this->picture);
                    if($this->thumbnail) unlink($_SERVER["DOCUMENT_ROOT"].$this->thumbnail);
                    // update the internal variable
                    $this->picture = '/raffles/img/' . $file_name; // the picture is the target without the document root
                    $this->thumbnail = $thumb_target;
                    // no crash so...
                    return true; // everything went well!
                } catch (ImagickException $e) {} // the image was not uploaded/cropped correctly. Internal variables not set
            } // end if file was moved from tmp to disk
        } // end is_uploaded_file
        return false; //something went wrong
    }// end add_image
    
    /*
     * get_prizes()
     *
     * returns an array of all the prizes 
     */
    public function get_raffles($year = 0){
        if ($year) $sql = "SELECT * FROM raffle_prizes rp JOIN raffles r USING (raffle_id) WHERE rp.prize_id=".$this->prize_id." AND r.year=".$year.";";
        else $sql = "SELECT * FROM raffle_prizes WHERE prize_id=".$this->prize_id.";";
        $query = mysql_query($sql);
        while($row = mysql_fetch_assoc($query)){ // for each row
            $raffle = Raffle::load($row['raffle_id']); // load the prize
            if($raffle){ // if there is a prize
                $raffle->qty = $row['qty']; // add the quantity to the object
                $this->raffles[$raffle->raffle_id] = $raffle; // push it into the array
            }
        }
        return $this->raffles;
    }
}
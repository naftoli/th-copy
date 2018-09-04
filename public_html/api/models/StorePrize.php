<?php
include_once( __DIR__ . '/../tools/functions/files/images.php' );
include_once( __DIR__ . '/traits/BuildModel.php' );

class StorePrize extends ActiveRecord\Model implements JsonSerializable {
    use \traits\BuildModel;

    static $connection = 'pointsDB';
    static $table_name = 'prizes';
    static $primary_key = 'prize_id';

    const IMG_PATH = '/v2/images/imgsrepo/';

    static $alias_attribute = [
        'miles' => 'points',
        'stock' => 'prize_count'
    ];

    public function image() {
        if ( $this->image_id > 0 )
            return self::IMG_PATH . $this->image_id;
        return self::IMG_PATH . 'default.png';
    }

    public function platoons() {
        global $POINTS_DB;

        $platoons = $POINTS_DB->prepare( 'SELECT class_id FROM prize_classes WHERE prize_id = ?;' );
        $platoons->execute([ $this->prize_id ]);
        return $platoons->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function setPlatoons( $class_ids ) {
        global $POINTS_DB;
        
        // delete all existing connections
        $delete = $POINTS_DB->prepare( 'DELETE FROM prize_classes WHERE prize_id = ?;' );
        if ( !$delete->execute([ $this->prize_id ]) )
            return false;
        
        // connect all the class_id's provided
        $insert = $POINTS_DB->prepare( 'INSERT INTO prize_classes ( prize_id, class_id ) VALUES ( ?, ? )' );
        foreach( $class_ids as $class_id ) {
            $insert->execute([ $this->prize_id, $class_id ]);
        }

        return true;
    }

    static $belongs_to = [
        [ 'school', 'class_name' => 'School', 'foreign_key' => 'institution_id' ],
        [ 'admin', 'class_name' => 'Admin', 'foreign_key' => 'created_by' ],
    //     [ 'subject', 'class_name' => 'Subject', 'foreign_key' => 'campaign_id' ],
    //     [ 'task', 'class_name' => 'AchievementTask', 'foreign_key' => 'task_id' ],
    ];

    // takes an uploaded file and sets it as the profile picture
    public function setImage( $file ){
        $upload = self::uploadImage( $this->prize_id, $file );
        // update the mobile_pic column
        $this->image_id = $upload;
        $this->save();
        return true;
    }
    // validates and moves the uploaded profile picture...
    public static function uploadImage( $prize_id, $file ){
        $type = exif_imagetype( $file['tmp_name'] );
        $extension = image_type_to_extension( $type );
        if ( !in_array( $type, [ IMAGETYPE_JPEG, IMAGETYPE_PNG ] ) )
            throw new Exception( 'Invalid File Type. Only JPG/JPEG/PNG are supported at the moment.' );
        // all other upload errors
        if ( $file['error'] !== UPLOAD_ERR_OK )
            throw new Exception( codeToMessage( $file['error'] ) ); // api/funcitons/files/images.php#10
        // generate the file name
        $file_name = getLogoDestination( $prize_id, $extension ); // api/funcitons/files/images.php#35
        $target = __DIR__ . "/../../" . self::IMG_PATH . "/$file_name";
        // remove duplicate files
        if ( file_exists( $target ) ) unlink( $target );
        // save file
        $result = move_uploaded_file( $file['tmp_name'], $target );
        if ( !$result ) 
            throw new Exception( 'Unable to save Image. Please check if your file is corrupt before trying again.' );
        return $file_name;
    }

    // serialize to json
    public function jsonSerialize( $options = [] ) {
        $default_options = [
            'only' => [
                'prize_id','prize_name', 'institution_id', 'points', 'prize_description', 
                'one_per_user', 'prize_count', 'is_active', 'modified'
            ],
            'include' => [ 'school' => [ 'only' => [ 'school_name', 'school_id', 'school_number' ] ] ],
            'methods' => [ 'image' ]
        ];

        return $this->to_array( array_merge_recursive( $default_options, $options ) );
    }
}
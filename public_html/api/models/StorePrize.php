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
        return self::IMG_PATH . $this->image_id;
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
    public function jsonSerialize() {
        return $this->to_array([
            'only' => [
                'prize_id','prize_name', 'institution_id', 'points', 'prize_description', 
                'one_per_user', 'prize_count', 'is_active', 'modified'
            ],
            // 'include' => [
            //     'school', 'class', 'task', 
            //     'subject' => [ 'only' => [ 'subject_name', 'subject_id', 'subject_type' ] ]
            // ],
            'methods' => [ 'image' ]
        ]);
    }
}
<?
class FileUploader {
    
    private $uploads;
    private $files;
    
    public function __construct() {
        $this->uploads = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";
        $this->files = array();
    }
    
    public function upload( $file ) {
        if ( !file_exists( $this->uploads . $file['name'] ) ) {
            if ( file_exists( $file['tmp_name'] ) ) {
                move_uploaded_file( $file['tmp_name'], $this->uploads . $file['name'] );                
            }
        }
    }
    
    public function showFiles() {
        $dir = dir( $this->uploads );
        while ( $file = $dir->read() ) {
            if ( $file == '.' || $file == '..' )
                continue;
            $link = "http://mashpia.com/uploads/" . $file;
            echo "<tr><td>" . $file . "</td><td><a href='" . $link . "'>" . $link . "</td></tr>";
        }
    }
}
?>
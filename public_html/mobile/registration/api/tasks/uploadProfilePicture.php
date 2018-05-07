<?php
include_once( dirname(__FILE__) . "/../header.php" );
// include_once( dirname(__FILE__) . "/../classes/ProfilePicture.php" );

// make sure that this is a post request
if ( $_SERVER['REQUEST_METHOD'] != "POST" )
    render_json_error( "Invalid Request", "Invalid Request Type. Expecting POST" );

// get the file
$file = $_FILES['profile'];

// get the type of image
$type = exif_imagetype( $file['tmp_name'] );
$extension = image_type_to_extension($type);

// check the file type
if ( !in_array( $type, [ IMAGETYPE_JPEG, IMAGETYPE_PNG ] ) )
    render_json_error( "Invalid Image. Only PNG and JPG/JPEG are supported at the moment." );

// return any file upload errors 
if ( $file['error'] !== UPLOAD_ERR_OK )
    render_json_error( codeToMessage( $file['error'] ) );

// generate file name
$file_name = getDestination( $admin_id, $extension );
$target_file = dirname(__FILE__) . "/../../../reg/$file_name"; // point to where we are expecting to put the file

// remove old files
if ( file_exists( $target_file ) )
    unlink( $target_file );

// move the file
$result = move_uploaded_file($file['tmp_name'], $target_file);

if ( !$result )
    render_json_error( "Failed to save file. Please check for file corruption." );
// show the response
render_json_response([
    "location" => "/mobile/reg/$file_name",
    "filename" => $file_name
]);

/**
 * codeToMessage
 * 
 * convert image error codes to error messages
 *
 * @param int $code
 * @return string
 */
function codeToMessage( $code ) {
    $errors = array(
        UPLOAD_ERR_INI_SIZE     => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE    => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        UPLOAD_ERR_PARTIAL      => 'The uploaded file was only partially uploaded',
        UPLOAD_ERR_NO_FILE      => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR   => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE   => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION    => 'File upload stopped by extension',
    );
    // return the error if we have one.
    if (array_key_exists($code, $errors))
        return $errors[$code];
    return 'Unknown upload error';
}

/**
 * getDestination
 * 
 * Generates the destination location of the image ( timestamp in a folder for the uploading user )
 *
 * @param string/int $id
 * @param string $extension
 * @return void
 */
function getDestination( $id, $extension ) {
    return "img/$id." . date('YmdHis') . $extension;
}
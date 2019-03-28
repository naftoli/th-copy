<?php
include_once( dirname(__FILE__) . "/../header.php" );
// include_once( dirname(__FILE__) . "/../classes/ProfilePicture.php" );

// make sure that this is a post request
if ( $_SERVER['REQUEST_METHOD'] != "POST" )
    render_json_error( "Invalid Request", "Invalid Request Type. Expecting POST" );

// get the file
$file = $_FILES['profile'];

if ( !$file['tmp_name'] )
    render_json_error('File upload error. File likely too large or corrupt.');

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
// limit to 500x500px
shrink_image( $target_file );
// show the response
return json_response([
    "location" => "/mobile/reg/$file_name",
    "filename" => $file_name
]);

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

/**
 * shrink_image
 *
 * @param string $target_file path to target file
 * @param int $size the size we want the image to become
 * @return boolean
 */
function shrink_image( $target_file, $size = 500 ) {
    try {
        $image = new Imagick( $target_file );
        $image->scaleImage( $size, 0 ); // compress the image down to a smaller size...

        // attempt to fix the rotation
		try {
			if ( in_array( exif_imagetype( $target_file ), [ IMAGETYPE_JPEG, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM ] ) && exif_read_data( $target_file )) {
				$exif = @exif_read_data( $target_file ); // read the data
				$orientation = $exif['Orientation']; // get the orientation
				if($orientation){ // this will only run if orientation is set which can only happen if $_POST['action'] is set to fix
					switch($orientation) {  
						case 3: $image->rotateimage("#FFF", 180); $thumb->rotateimage("#FFF", 180); break; // upside down
						case 6: $image->rotateimage("#FFF", 90); $thumb->rotateimage("#FFF", 90); break; // rotate 90 degrees CW
						case 8: $image->rotateimage("#FFF", -90); $thumb->rotateimage("#FFF", -90);break; // rotate 90 degrees CCW
					}
				}
			}
		} catch (ImagickException $e) {} // rotation not fixed. do nothing about it.
		
        $image->writeImage( $target_file ); // write the file...
        
        return true;
    } catch (ImagickException $e) {
		return false;
	} // the image was not uploaded/cropped correctly.
}
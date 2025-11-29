<?php
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
        UPLOAD_ERR_INI_SIZE     => 'The uploaded file exceeds the maximum file size.',
        UPLOAD_ERR_FORM_SIZE    => 'The uploaded file exceeds the maximum file size.',
        UPLOAD_ERR_PARTIAL      => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE      => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR   => 'Server Error, could not upload file',
        UPLOAD_ERR_CANT_WRITE   => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION    => 'File upload stopped by extension',
    );
    // return the error if we have one.
    if (array_key_exists($code, $errors))
        return $errors[$code];
    return 'Unknown file upload error. Please email bugs@tzivoshashem.org.';
}

/**
 * getProfileDestination
 *
 * Generates the destination location of the image ( id.timestamp.extension ) ( in /mobile/reg becuase reasons :-( )
 *
 * @param string/int $id
 * @param string $extension
 * @return string
 */
function getProfileDestination( $id, $extension ) {
    return "img/$id." . date('YmdHis') . $extension;
}

/**
 * getLogoDestination
 *
 * Generates the destination location of the image ( id.timestamp.extension ) ( in /schoolLogos/ becuase reasons :-( )
 *
 * @param string/int $id
 * @param string $extension
 * @return string
 */
function getLogoDestination( $id, $extension ) {
    return "$id." . date('YmdHis') . $extension;
}

/**
 * base64ToFileArray
 *
 * Convert base64 encoded image data to a file array that mimics $_FILES structure
 *
 * @param string $base64_data - Base64 encoded image (e.g., "data:image/png;base64,iVBORw0KG...")
 * @param string $field_name - The field name (e.g., 'profile')
 * @return array|string - Returns file array on success, error string on failure
 */
function base64ToFileArray( $base64_data, $field_name = 'profile' ) {
    // Check if it's actually base64 image data
    if ( !preg_match('/^data:image\/(\w+);base64,/', $base64_data, $matches) ) {
        return 'Invalid base64 image format.';
    }
    
    $image_type = $matches[1];
    
    // Map common image types to IMAGETYPE constants
    $type_map = [
        'png'  => IMAGETYPE_PNG,
        'jpg'  => IMAGETYPE_JPEG,
        'jpeg' => IMAGETYPE_JPEG,
        'gif'  => IMAGETYPE_GIF,
        'webp' => IMAGETYPE_WEBP
    ];
    
    if ( !isset($type_map[$image_type]) ) {
        return "Unsupported image type: $image_type";
    }
    
    // Remove the data:image/xxx;base64, part
    $base64_string = preg_replace('/^data:image\/\w+;base64,/', '', $base64_data);
    
    // Decode the base64 string
    $image_data = base64_decode($base64_string, true);
    
    if ( $image_data === false ) {
        return 'Failed to decode base64 image data.';
    }
    
    // Create a temporary file
    $tmp_file = tempnam(sys_get_temp_dir(), 'img_');
    if ( $tmp_file === false ) {
        return 'Failed to create temporary file.';
    }
    
    // Write the decoded data to the temp file
    if ( file_put_contents($tmp_file, $image_data) === false ) {
        @unlink($tmp_file);
        return 'Failed to write image data to temporary file.';
    }
    
    // Verify it's a valid image
    $img_info = @getimagesize($tmp_file);
    if ( $img_info === false ) {
        @unlink($tmp_file);
        return 'Invalid or corrupt image data.';
    }
    
    // Get the file extension
    $extension = image_type_to_extension($type_map[$image_type]);
    
    // Create a file array that mimics $_FILES structure
    return [
        'name'     => $field_name . $extension,
        'type'     => $img_info['mime'],
        'tmp_name' => $tmp_file,
        'error'    => UPLOAD_ERR_OK,
        'size'     => filesize($tmp_file)
    ];
}

/**
 * getBase64FromPost
 *
 * Check if POST contains base64 image data for a given field
 *
 * @param string $field_name - The field name to check
 * @return string|false - Returns base64 data if found, false otherwise
 */
function getBase64FromPost( $field_name = 'profile' ) {
    // Check if we have the field in POST
    if ( !isset($_POST[$field_name]) ) {
        return false;
    }
    
    $data = $_POST[$field_name];
    
    // Check if it's base64 image data
    if ( is_string($data) && preg_match('/^data:image\/\w+;base64,/', $data) ) {
        return $data;
    }
    
    return false;
}

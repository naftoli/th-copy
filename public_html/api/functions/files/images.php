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

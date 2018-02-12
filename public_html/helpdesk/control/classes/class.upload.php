<?php

class msUpload {

  public function isUploaded($tmp) {
    return (is_uploaded_file($tmp) ? true : false);
  }

  public function moveFile($tmp, $dest) {
    move_uploaded_file($tmp, $dest);
  }

  public function getMaxSize() {
    static $max_size = -1;
    if ($max_size < 0) {
      $max_size = msUpload::size(@ini_get('post_max_size'));
      $upload_max = msUpload::size(@ini_get('upload_max_filesize'));
      if ($upload_max > 0 && $upload_max < $max_size) {
        $max_size = $upload_max;
      }
    }
    return $max_size;
  }

  public function size($size) {
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
    $size = preg_replace('/[^0-9\.]/', '', $size);
    if ($unit) {
      return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
    } else {
      return round($size);
    }
  }

  public function chmodFile($file, $perms) {
    @chmod($file, $perms);
  }

  public function folderCreation($path, $chmod) {
    $omask = @umask(0);
    @mkdir($path, $chmod);
    @umask($omask);
  }

  public function error($code) {
    switch($code) {
       case UPLOAD_ERR_OK:
         $txt = 'There is no error, the file uploaded with success.';
         break;
       case UPLOAD_ERR_INI_SIZE:
         $txt = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
         break;
       case UPLOAD_ERR_FORM_SIZE;
         $txt = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
         break;
       case UPLOAD_ERR_PARTIAL;
         $txt = 'The uploaded file was only partially uploaded.';
         break;
       case UPLOAD_ERR_NO_FILE;
         $txt = 'No file was uploaded.';
         break;
       case UPLOAD_ERR_NO_TMP_DIR:
         $txt = 'Missing a temporary folder.';
         break;
       case UPLOAD_ERR_CANT_WRITE:
         $txt = 'Failed to write file to disk.';
         break;
       case UPLOAD_ERR_EXTENSION:
         $txt = 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help';
         break;
       default:
         $txt = 'Unknown Error';
         break;
    }
    return $txt;
  }

}

?>
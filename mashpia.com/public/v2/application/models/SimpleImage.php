<?php

/*
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details:
* http://www.gnu.org/licenses/gpl.html
*
*/

class SimpleImage {

	var $image;
	var $image_type;

	function load($filename) {

		$this->image_type = false;
		$this->image = false;

	  if ( file_exists( $filename ) ) {
			$image_info = getimagesize( $filename );

			$this->image_type = $image_info[2];
			if( $this->image_type == IMAGETYPE_JPEG ) {
				$this->image = imagecreatefromjpeg($filename);
			} elseif( $this->image_type == IMAGETYPE_GIF ) {

				$this->image = imagecreatefromgif($filename);
			} elseif( $this->image_type == IMAGETYPE_PNG ) {
				$this->image = imagecreatefrompng($filename);
			}
		}
	}

	function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {

	  if( $image_type == IMAGETYPE_JPEG ) {
		 imagejpeg($this->image,$filename,$compression);
	  } elseif( $image_type == IMAGETYPE_GIF ) {

		 imagegif($this->image,$filename);
	  } elseif( $image_type == IMAGETYPE_PNG ) {

		 imagepng($this->image,$filename);
	  }
	  if( $permissions != null) {

		 chmod($filename,$permissions);
	  }
	}
	function output($image_type=IMAGETYPE_JPEG) {
	   $image_type = $this->image_type;
	  if( $image_type == IMAGETYPE_JPEG ) {
		 imagejpeg($this->image);
	  } elseif( $image_type == IMAGETYPE_GIF ) {

		 imagegif($this->image);
	  } elseif( $image_type == IMAGETYPE_PNG ) {
		imagesavealpha($this->image, true);
		 imagepng($this->image);
		 imagedestroy($this->image);
	  }
	}
	function getWidth() {
		if ( $this->image )
			return imagesx($this->image);
		return 0;
	}
	function getHeight() {
		if ( $this->image )
			return imagesy($this->image);
		return 0;
	}
	function resizeToHeight($height) {

	  $ratio = $height / $this->getHeight();
	  $width = $this->getWidth() * $ratio;
	  $this->resize($width,$height);
	}

	function resizeToWidth($width) {
		$ratio = 1;

		if ( $this->getWidth() )
	  	$ratio = $width / $this->getWidth();
		$height = $this->getheight() * $ratio;
		
	  $this->resize( $width, $height );
	}

	function scale($scale) {
	  $width = $this->getWidth() * $scale/100;
	  $height = $this->getheight() * $scale/100;
	  $this->resize($width,$height);
	}

	function resize( $width, $height ) {
		if ( !function_exists( 'imagecreatetruecolor') || $width == 0 || $height == 0 )
			return false;

		$new_image = imagecreatetruecolor($width, $height);
		if($this->image_type == IMAGETYPE_GIF || $this->image_type == IMAGETYPE_PNG){
			//imagecolortransparent($new_image, imagecolorallocatealpha($new_image, 0, 0, 0, 127));
			//imagealphablending($new_image, true);

			$transparent = imagecolorallocatealpha( $new_image, 0, 0, 0, 127);
			imagefill( $new_image, 0, 0, $transparent );
			imagesavealpha($new_image, true);

			imagealphablending($new_image, false);

		}
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		$this->image = $new_image;
	}

	function thumbnail($thumb_width, $thumb_height)
	{
		$width = imagesx($this->image);
		$height = imagesy($this->image);

		$original_aspect = $width / $height;
		$thumb_aspect = $thumb_width / $thumb_height;
		if ( $original_aspect >= $thumb_aspect )
		{
		   // If image is wider than thumbnail (in aspect ratio sense)
		   $new_height = $thumb_height;
		   $new_width = $width / ($height / $thumb_height);
		}
		else
		{
		   // If the thumbnail is wider than the image
		   $new_width = $thumb_width;
		   $new_height = $height / ($width / $thumb_width);
		}
		$thumb = imagecreatetruecolor($thumb_width, $thumb_height);
		if ($this->image_type == IMAGETYPE_GIF || $this->image_type == IMAGETYPE_PNG) {
			imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
			$transparent = imagecolorallocatealpha( $thumb, 0, 0, 0, 127);
			imagefill( $thumb, 0, 0, $transparent );
			imagesavealpha($thumb, true);
			imagealphablending($thumb, true);

		}
		imagecopyresampled($thumb,
		   $this->image,
		   0 - ($new_width - $thumb_width) / 2, // Center the image horizontally
		   0 - ($new_height - $thumb_height) / 2, // Center the image vertically
		   0, 0,
		   $new_width, $new_height,
		   $width, $height);
		$this->image = $thumb;
	}

	function thumbnailph($thumb_width, $thumb_height)
	{
		$width = imagesx($this->image);
		$height = imagesy($this->image);

		$original_aspect = $width / $height;
		$thumb_aspect = $thumb_width / $thumb_height;
		if ( $original_aspect <= $thumb_aspect )
		{
		   // If image is wider than thumbnail (in aspect ratio sense)
		   $new_height = $thumb_height;
		   $new_width = $width / ($height / $thumb_height);
		}
		else
		{
		   // If the thumbnail is wider than the image
		   $new_width = $thumb_width;
		   $new_height = $height / ($width / $thumb_width);
		}
		$this->image_type = IMAGETYPE_PNG;
		$thumb = imagecreatetruecolor($thumb_width, $thumb_height);
		if ($this->image_type == IMAGETYPE_GIF || $this->image_type == IMAGETYPE_PNG) {
			imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
			$transparent = imagecolorallocatealpha( $thumb, 0, 0, 0, 127);
			imagefill( $thumb, 0, 0, $transparent );
			imagesavealpha($thumb, true);
			imagealphablending($thumb, true);

		}
		imagecopyresampled($thumb,
		   $this->image,
		   0 - ($new_width - $thumb_width) / 2, // Center the image horizontally
		   0 - ($new_height - $thumb_height) / 2, // Center the image vertically
		   0, 0,
		   $new_width, $new_height,
		   $width, $height);
		$this->image = $thumb;
	}

}
?>
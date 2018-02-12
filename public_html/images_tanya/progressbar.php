<?php
//phpinfo();
$h = 0 + $_REQUEST["h"];
$w = 0 + $_REQUEST["w"];
$perc = 0 + $_REQUEST["perc"];
$p = $w * ($perc/100);

header("Content-type: image/png");
$im = @imagecreate($w, $h)
    or die("Cannot Initialize new GD image stream");
$bg = imagecolorallocate($im, 255, 255, 255);
$pc = imagecolorallocate($im, 210, 210, 210);
$black = imagecolorallocate($im,0,0,0);
//imagestring($im, 1, 5, 5,  "A Simple Text String", $text_color);
imagefill($im,0,0,$bg);
imagefilledrectangle($im,0,0,$p,$h,$pc);
//imagesetthickness($im,2);
imagerectangle ( $im, 0, 0, $w-1, $h-1, $black );
imagepng($im);
imagedestroy($im);

?> 
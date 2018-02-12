<?php
// $DEBUG var sets output to debug mode
$DEBUG = false;
 
//Passed Vars
$page = 0 + $_REQUEST["page"];
$start_line = 0.0 + $_REQUEST["start_line"];
$start_pos = 0.0 + $_REQUEST["start_pos"];
$lines = 0.0 + $_REQUEST["lines"];

$top_zero = 60;	//top start line offset
$lineh = 65;	//line height
$marg = 5;	//side margin
$page_width = 1258;	
$number_marg = 40; // accomodate for the line numbers
$real_line = $page_width - ($marg*2) - $number_marg;  //Used for line percentage calculations

$img = @imagecreatefrompng("/home/mashpia/public_html/images_tanya/pages/pg_$page.png");
if(!$img) {
  $img = imagecreatetruecolor($page_width, 1805);
  imagefilledrectangle($img, 0, 0, $page_width, 1805, imagecolorallocate($img, 255, 255, 255));
  imagestring($img, 5, $marg, $top_zero, "Page $page", imagecolorallocate($img, 0, 0, 0));
}
//Create grey highlight color
$high = imagecolorallocatealpha($img, 210, 210, 210, 75);
$t = imagecolorallocatealpha($img, 100,100,0, 75);

//Calculate ends
$end_point = ( ($start_pos/100)+$start_line+$lines  );
$end_line = floor($end_point) ;
$end_pos = ($end_point - floor($end_point)) * 100;

//Do the stuff!
for($i=$start_line;$i<=$end_line;$i++){
	if($i == $start_line && $i == $end_line)
		highlight($img, $i, $start_pos, $end_pos);
	elseif($i == $start_line)
		highlight($img, $i, $start_pos, 100);
	elseif($i == $end_line){
		if($end_pos > 0)
			highlight($img, $i, 0, $end_pos);
	}
	else
		highlight($img, $i);
}

//Set up header
header("Content-Type: image/png");
//Send image!
imagepng($img);
//Kill the image from memory
imagedestroy($img);


	function highlight($img, $line, $start = 0, $end = 100){
		
		global $high, $lineh, $marg, $top_zero, $real_line, $page_width, $number_marg, $DEBUG;
		$roff = $real_line * ($start / 100) ; 
		$loff = $real_line * (1 - ($end / 100)) ;
		if($start != 0){
		imagefilledrectangle ( $img,  
			($marg + $loff),  
			$top_zero + (($line - 1) * $lineh) , 
			($page_width - ($marg + $number_marg + $roff) ), 			
			$top_zero + (($line - 1) * $lineh) + $lineh, 
			$high);	
			}
			
		if($start == 0){
			imagefilledrectangle ( $img, 
			($marg + $loff), 
			$top_zero + (($line-1) * $lineh) , 
			($page_width - ($marg) ), 
			$top_zero + (($line - 1) * $lineh) + $lineh, 
			$high);
		}
	}

?>

<?
//error_reporting(E_ALL);
$request = substr($_SERVER['PATH_INFO'], 1);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', getlastmod()) . ' GMT');

if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] === gmdate('D, d M Y H:i:s', getlastmod()) . ' GMT') {
  header("HTTP/1.0 304 not modified");
} else {
  header('Content-type: image/png');
  $img = barcode128c($request);
  imagepng($img);
  imagedestroy($img);
}

function &barcode128c($text) {

  $code = array();
  $code[0] = "212222";  // " "
  $code[1] = "222122";  // "!"
  $code[2] = "222221";  // "{QUOTE}"
  $code[3] = "121223";  // "#"
  $code[4] = "121322";  // "$"
  $code[5] = "131222";  // "%"
  $code[6] = "122213";  // "&"
  $code[7] = "122312";  // "'"
  $code[8] = "132212";  // "("
  $code[9] = "221213";  // ")"
  $code[10] = "221312"; // "*"
  $code[11] = "231212"; // "+"
  $code[12] = "112232"; // ","
  $code[13] = "122132"; // "-"
  $code[14] = "122231"; // "."
  $code[15] = "113222"; // "/"
  $code[16] = "123122"; // "0"
  $code[17] = "123221"; // "1"
  $code[18] = "223211"; // "2"
  $code[19] = "221132"; // "3"
  $code[20] = "221231"; // "4"
  $code[21] = "213212"; // "5"
  $code[22] = "223112"; // "6"
  $code[23] = "312131"; // "7"
  $code[24] = "311222"; // "8"
  $code[25] = "321122"; // "9"
  $code[26] = "321221"; // ":"
  $code[27] = "312212"; // ";"
  $code[28] = "322112"; // "<"
  $code[29] = "322211"; // "="
  $code[30] = "212123"; // ">"
  $code[31] = "212321"; // "?"
  $code[32] = "232121"; // "@"
  $code[33] = "111323"; // "A"
  $code[34] = "131123"; // "B"
  $code[35] = "131321"; // "C"
  $code[36] = "112313"; // "D"
  $code[37] = "132113"; // "E"
  $code[38] = "132311"; // "F"
  $code[39] = "211313"; // "G"
  $code[40] = "231113"; // "H"
  $code[41] = "231311"; // "I"
  $code[42] = "112133"; // "J"
  $code[43] = "112331"; // "K"
  $code[44] = "132131"; // "L"
  $code[45] = "113123"; // "M"
  $code[46] = "113321"; // "N"
  $code[47] = "133121"; // "O"
  $code[48] = "313121"; // "P"
  $code[49] = "211331"; // "Q"
  $code[50] = "231131"; // "R"
  $code[51] = "213113"; // "S"
  $code[52] = "213311"; // "T"
  $code[53] = "213131"; // "U"
  $code[54] = "311123"; // "V"
  $code[55] = "311321"; // "W"
  $code[56] = "331121"; // "X"
  $code[57] = "312113"; // "Y"
  $code[58] = "312311"; // "Z"
  $code[59] = "332111"; // "["
  $code[60] = "314111"; // "\"
  $code[61] = "221411"; // "]"
  $code[62] = "431111"; // "^"
  $code[63] = "111224"; // "_"
  $code[64] = "111422"; // "`"
  $code[65] = "121124"; // "a"
  $code[66] = "121421"; // "b"
  $code[67] = "141122"; // "c"
  $code[68] = "141221"; // "d"
  $code[69] = "112214"; // "e"
  $code[70] = "112412"; // "f"
  $code[71] = "122114"; // "g"
  $code[72] = "122411"; // "h"
  $code[73] = "142112"; // "i"
  $code[74] = "142211"; // "j"
  $code[75] = "241211"; // "k"
  $code[76] = "221114"; // "l"
  $code[77] = "413111"; // "m"
  $code[78] = "241112"; // "n"
  $code[79] = "134111"; // "o"
  $code[80] = "111242"; // "p"
  $code[81] = "121142"; // "q"
  $code[82] = "121241"; // "r"
  $code[83] = "114212"; // "s"
  $code[84] = "124112"; // "t"
  $code[85] = "124211"; // "u"
  $code[86] = "411212"; // "v"
  $code[87] = "421112"; // "w"
  $code[88] = "421211"; // "x"
  $code[89] = "212141"; // "y"
  $code[90] = "214121"; // "z"
  $code[91] = "412121"; // "{"
  $code[92] = "111143"; // "|"
  $code[93] = "111341"; // "}"
  $code[94] = "131141"; // "~"
  $code[95] = "114113"; // 95
  $code[96] = "114311"; // 96
  $code[97] = "411113"; // 97
  $code[98] = "411311"; // 98
  $code[99] = "113141"; // 99
  $code[100] = "114131"; // CODE_B
  $code[101] = "311141"; // CODE_A
  $code[102] = "411131"; // FUNC_1
  $code[103] = '211412'; // START_A
  $code[104] = '211214'; // START_B
  $code[105] = '211232'; // START_C
  $code[106] = '2331112'; // STOP

  if(strlen($text) % 2) $text = '0' . $text;

  $bar_pattern = $code[105];
  $checksum = 105;

  for($i = 0; $i < strlen($text); $i+=2) {
    $val = intval($text[$i] . $text[$i+1]);
    $checksum += $val * ($i/2 + 1);
    $bar_pattern .= $code[$val];
  }
  $bar_pattern .= $code[$checksum % 103];
  $bar_pattern .= $code[106];

  $bar_width = (strlen($bar_pattern)-1)/6*11+2; //each pattern is 6 chars long, and all patterns are 11 pixels wide, except the stop code

  $img = ImageCreate($bar_width*3, 50);
  $black = ImageColorAllocate($img, 0, 0, 0);
//  $white = ImageColorAllocate($img, 255, 255, 255);
  $transparency = imagecolorallocatealpha($img, 0, 0, 0, 127);
  imagefill($img, 0, 0, $transparency);

  $color = true;
  $xpos = 0;
  for($i = 0; $i < strlen($bar_pattern); $i++) {
    $width = intval($bar_pattern[$i])*3;
    if($color) imagefilledrectangle($img, $xpos, 0, $xpos + $width-1, 50, $black);
    $xpos += $width;
    $color = !$color;
  }
  
  return $img;
}
?>

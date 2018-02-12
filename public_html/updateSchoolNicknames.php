<?php
//$nicknames = array(
//    269	=> 'Anash Kinder',
//    176	=> 'BCM Postville',
//    162	=> 'BCM Los Angeles',
//    45	=> 'BCM Toronto',
//    30	=> 'BCM Crown Heights',
//    2	=> 'BR Montreal',
//    54	=> 'BR Crown Heights',
//    7	=> 'BM Crown Heights', 
//    112	=> 'CY Melbourne Boys',
//    66	=> 'CY Melbourne Girls',
//    105	=> 'Chassidus Club CT',
//    63	=> 'Cheder at the Ohel',
//    81	=> 'CC Baltimore',
//    49	=> 'CC Monsey',
//    89	=> 'CC Philadelphia',
//    55	=> 'Cheder Sydney',
//    106	=> 'Cheder Toronto',
//    5	=> 'CLHDS Chicago Boys',
//    50	=> 'CLHDS Chicago Girls',
//    21	=> 'Cheder Morristown Boys',
//    37	=> 'Cheder Morristown Girls',
//    4	=> 'CM Los Angeles',
//    263	=> 'MM Seattle Cheder',
//    60	=> 'CM New Jersey',
//    21	=> 'CM Wilkes Barre',
//    185	=> 'Hebrew Academy Margate',
//    80	=> 'Hillel Milwaukee',
//    110	=> 'KTC Sydney',
//    194	=> 'Lamplighters CH',
//    3	=> 'Lubavitch Boys London',
//    265	=> 'Lubavitch Girls London',
//    39	=> 'Lubavitch Cheder MN',
//    19	=> 'LEC Florida Boys',
//    42	=> 'LEC Florida Girls',
//    9	=> 'Lubavitcher Yeshiva CH',
//    61	=> 'MyShliach',
//    255	=> 'OT Crown Heights',
//    48	=> 'Ohr Temimim Buffalo',        
//    84	=> 'TDS Houston',
//    427	=> 'Tzivos Hashem Long Beach',
//    87	=> 'TH Vancouver',
//    264	=> 'Chok LeYisroel Lubavitch CH',
//    33	=> 'Darchai Menachem CH',
//    11	=> 'YSOP Boys',
//    40	=> 'YSOP Girls',
//    58	=> 'YTTL Montreal'
//);

$nicknames = array(
	112 => 'CY Melbourne',
	66	=> 'CY Melbourne',
	5	=> 'CLHDS Chicago',
	50	=> 'CLHDS Chicago',
	21	=> 'Cheder Morristown',
	37	=> 'Cheder Morristown',
	19	=> 'LEC Florida',
	42	=> 'LEC Florida',
	11	=> 'YSOP',
	40	=> 'YSOP'
);

require 'db.php';

foreach ($nicknames as $id => $name) {
    $sql = "update schools set nickname = '" . $name . "' where school_id = " . $id;
    mysql_query($sql);
}
echo "done.";
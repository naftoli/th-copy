<?
$boys = array(
	'Lipskier'		=>	'Chabad Youth Boys',
	'Margolis'		=>	'Cheder at the Ohel',
	'Lisbon'		=>	'Cheder Chabad Baltimore',
	'Wolowik'		=>	'Cheder Chabad Monsey Boys',
	'Wilansky'		=>	'Cheder Chabad Sydney',
	'Marmulsteyn'	=>	'Cheder Chabad Toronto',
	'Twersky'		=>	'Cheder Lubavitch Chicago Boys',
	'Wilshansky'	=>	'Cheder Lubavitch Morristown',
	'Heidingsfeld'	=>	'Cheder Menachem LA',
	'Green'			=>	'Cheder Menachem Wilkes Barre Boys',
	'Perlstein'		=>	'Darchai Menachem',
	'Straiton'		=>	'Kesser Torah College',
	'Hackner'		=>	'Lubavitch Boys Junior School',
	'Goldberg'		=>	'Lubavitch Cheder Day School',
	'Kahan'			=> 	'Lubavitcher Yeshiva',
	'Rivkin'		=>	'MyShliach Boys',
	'Yuzevitch'		=>	'Ohr Menachem',
	'machester'		=>	'OYY Lubavitch',
	'Lustig'		=>	'Oholei Torah',
	'Baras'			=>	'ULY Flatbush',
	'Vaisfiche'		=>	'Yeshiva Tomchei Temimim Lubavitch',
	'Greenwald'		=>	'Yeshiva Schools of Pittsburgh Boys'	
);

$girls = array(
	'Bukiet'		=>	'Bais Chaya Mushka LA',
	'Wagner'		=>	'Bais Chaya Mushka Toronto',
	'Posner'		=>	'Bais Chaya Mushka Crown Heights',
	'Wilhelm'		=>	'Bais Rivka Crown Heights',
	'Scheiner'		=>	'Bais Rivka Montreal',
	'Simpson'		=>	'Bnos Menachem',
	'Perlstein'		=>	'Cheder Lubavitch Chicago Girls',
	'Azimov'		=>	'Cheder Menachem NJ',
	'Rosenbluh'		=>	'Cheder Chabad Monsey Girls',
	'Green'			=>	'Cheder Menachem Wilkes Barre Girls',
	'Greene'		=>	'Lubavitch Cheder Day School',
	'Gordon'		=>	'Maimonides Hebrew Day School',
	'Rosensweig'	=>	'MyShliach Girls',
	'Deren'			=>	'Yeshiva Schools of Pittsburgh Girls'
);

$year = 5775;
include 'db.php';

foreach ($boys as $user => $school) {
	$sql = "insert into chidon_schools 
			set year = $year, 
			username = '" . strtolower($user) . "' , 
			school_name = '" . $school . "', 
			gender = 'boys'";
	mysql_query($sql);
}
foreach ($girls as $user => $school) {
	$sql = "insert into chidon_schools 
			set year = $year, 
			username = '" . strtolower($user) . "' , 
			school_name = '" . $school . "', 
			gender = 'girls'";
	mysql_query($sql);
}
?>
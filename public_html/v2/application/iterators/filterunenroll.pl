require LWP::UserAgent;
my $ua = LWP::UserAgent->new(
	env_proxy => 1,
	keep_alive => 1,
	timeout => 2,
);



my $intItr = 0;
while (++$intItr)
{
	$objResponse = $ua->get('http://mashpia2.icorpa.com/automation/filterunenroll/');
	open FILE, ">filterenrolllog.txt" or die $!; 
	print FILE $intItr . ": " . $objResponse->decoded_content;
	close FILE;
	print $intItr . ": " . $objResponse->decoded_content . "\n";
	sleep 5;
	#sleep 3600;
}
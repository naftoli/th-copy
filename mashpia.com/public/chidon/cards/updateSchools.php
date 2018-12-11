<?
require '../../db.php';

$schools = array(
74	=> 'Brooklyn, NY',
107	=> 'Postville, IA',
75	=> 'Los Angeles, CA',
76	=> 'Toronto, Ontario',
109	=> 'Los Angeles, CA',
77	=> 'Brooklyn, NY',
78	=> 'Montreal, Canada',
79	=> 'Brooklyn, NY',
136	=> 'Melbourne, AU',
110	=> 'New Haven, CT',
123	=> 'New Haven, CT',
81	=> 'Queens, NY',
82	=> 'Baltimore, MD',
83	=> 'Monsey, NY',
84	=> 'Monsey, NY',
111	=> 'Philadelphia, PA',
124	=> 'Philadelphia, PA',
85	=> 'Sydney, AUS',
135	=> 'Sydney, AUS',
86	=> 'Toronto, Ontario',
87	=> 'Chicago, IL',
89	=> 'Morristown, NJ',
90	=> 'Morristown, NJ',
91	=> 'Los Angeles, CA',
125	=> 'North Brunswick, NJ',
92	=> 'North Brunswick, NJ',
93	=> 'Wilkes Barre, PA',
94	=> 'Wilkes Barre, PA',
113	=> 'Margate, FL',
88	=> 'Chicago, IL',
96	=> 'London, England',
98	=> 'S Paul, MN',
114	=> 'Miami, FL',
115	=> 'Miami, FL',
116	=> 'Miami, FL',
117	=> 'London, England',
99	=> 'Brooklyn, NY',
101	=> 'Albany, NY',
118	=> 'Portland, OR',
128	=> 'Postville, IA',
104	=> 'Brooklyn, NY',
133	=> 'Buffalo, NY',
120	=> 'Houston, TX',
126	=> 'Houston, TX',
127	=> 'Vancouver, British Columbia',
105	=> 'Pittsburgh, PA',
131	=> 'Pittsburgh, PA',
106	=> 'Montreal, Canada'
);

foreach ($schools as $id => $info) {
	$sql = "update chidon_schools set city_state = '" . $info . "' where chidon_schools_id = " . $id;
	mysql_query($sql);
}
?>
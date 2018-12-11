	<title>Enrollment</title>
</head>
<body>
<form method="POST" action="__!Self!__?action=enroll&step=ladder">
	<h2>Soldier Enrollment</h2>
	__!Form Validation!__
	First name:
	<div><input size="40" type="text" name="first_name" value="__!first_name!__" /></div>
	Last name:
	<div><input size="40" type="text" name="last_name" value="__!last_name!__" /></div>
	Birth date:
	<div><input size="40" type="text" name="birth_date" value="__!birth_date!__" /> (dd/mm/yy)</div>
	&nbsp;<br>
	<input type="submit" value="Next step" /> &nbsp; <input type="button" value="Cancel" onclick="window.location.href='__!Self!__'" />
</form>
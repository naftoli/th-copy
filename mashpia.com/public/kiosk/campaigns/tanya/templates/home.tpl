	<title>Tanya Home</title>
</head>
<body>
	<h1>Tanya Home</h1>
	<form method="GET">
		<h3>Pick a user:</h3>
		<select name="user">
			__!User Options List!__
		</select>
		<input type="submit" value="Go" />
	</form>
	<div>
		or <input type="button" value="Enroll a user" onclick="window.location.href='__!Self!__?action=enroll'" />
		<input type="button" value="Admin" onclick="window.location.href='../admin/'" />
	</div>
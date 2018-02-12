	<title>Enrollment Ladder</title>
	<script>
		var arrLadderJSON = __!JSON Ladder!__;
		var intLinesBeforeEnrollment = __!intLinesBeforeEnrollment!__;
		var intLinesAfterEnrollment = __!intLinesAfterEnrollment!__;
		var intYearsRemaining = __!intYearsRemaining!__;
		
		function proc_ladder_details() {
			var intLadder = document.form01.ladder_list.value;
			var intYears = document.form01.years.value;
			var intLadderLines = arrLadderJSON[intLadder]["year"]; // Eight year campaign value
			document.form01.ladder_quota.value = Math.round(intLadderLines / intYearsRemaining) + " Lines";
			document.form01.year_quota.value = Math.round(intLadderLines / intYearsRemaining) + " New Lines";
			document.form01.weekly_quota.value = (Math.round(intLadderLines / (52 * intYearsRemaining) * 100) / 100) + " Lines";
			document.form01.total_quota.value = (Math.round(intLadderLines / intYearsRemaining) * intYears) + " Lines";
			document.getElementById("total_quota_text").innerHTML = intLadderLines + " lines - " + arrLadderJSON[intLadder]["chapter"];
		}
	</script>
</head>
<body>
<h2>Enrollment Ladder</h2>
<form name="form01" method="POST">
	<input type="hidden" name="form_history" value='__!Form History Place Holder!__' />
	<table border="1" cellspacing="3" cellpadding="3">
		<tr>
			<td>Ladder settings:</td>
			<td>Break down:</td>
		</tr>
		<tr>
			<td>
				<select name="ladder_list" onchange="proc_ladder_details()" onkeyup="proc_ladder_details()">
					__!User Ladder Option List!__
				</select>
			</td>
			<td>
				<select name="years" onchange="proc_ladder_details()" onkeyup="proc_ladder_details()">
					__!Remaining Years List!__
				</select>
			</td>
		</tr>
		<tr>
			<td>Your yearly quota will be:</td>
			<td>Your yearly quota will be:</td>
		</tr>
		<tr>
			<td><input type="text" name="ladder_quota" value="" size="12" onkeydown="return false" /></td>
			<td><input type="text" name="year_quota" value="" size="12" onkeydown="return false" /></td>
		</tr>
		<tr>
			<td>Your weekly quota will be:</td>
			<td>Your total will be:</td>
		</tr>
		<tr>
			<td><input type="text" name="weekly_quota" value="" size="12" onkeydown="return false" /></td>
			<td><input type="text" name="total_quota" value="" size="12" onkeydown="return false" /></td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				By Year __!intYearsRemaining!__ you will know: 
				<span id="total_quota_text"></span>
			</td>
		</tr>
	</table>
	&nbsp;<br>
	<input type="submit" value="Enroll soldier" /> &nbsp; <input type="button" value="Cancel" onclick="window.location.href='__!Self!__'" />
</form>
<script>
	// Apply the user ladder simulation
	proc_ladder_details();
</script>
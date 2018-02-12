function getHTTPObject() {
	var xmlhttp;

	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	}
	else if (window.ActiveXObject){ 
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
					
		if (!xmlhttp) {
			xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
		}
	}
				
	return xmlhttp; 
}

function get_levels(divs, form_name) {
	var url = "";

	try {
		url = "ajax_get_levels.php?admin_id=" + admin_id + "&user_type=" + user_type + "&camp_id=" + document.getElementById("camp_id").value;
	}
	catch(err) {
		url = "ajax_get_levels.php?admin_id=" + admin_id + "&user_type=" + user_type + "&camp_id=" + camp_id;			
	}
	
	if (form_name == "member_groups_form")
		url = url + "&user_id=" + user_id;
	
	switch (divs) {

		case "123":
		case "3":
		case "34":
			try {
				url = url + "&camp_group_id=" + document.getElementById("camp_group_id").value;
			}
			catch(err) {
				url = url + "&camp_group_id=" + camp_group_id;
			}
		break;
		
		case "4":
			try {
				url = url + "&camp_group_id=" + document.getElementById("camp_group_id").value;
			}
			catch(err) {
				url = url + "&camp_group_id=" + camp_group_id;
			}
			try {
				url = url + "&camp_division_id=" + document.getElementById("camp_division_id").value;
			}
			catch(err) {
				url = url + "&camp_division_id=" + camp_division_id;
			}
		break;
		
		case "1234":
			try {
				url = url + "&camp_group_id=" + document.getElementById("camp_group_id").value;
			}
			catch(err) {
				url = url + "&camp_group_id=" + camp_group_id;
			}
			try {
				url = url + "&camp_group_id=" + document.getElementById("camp_group_id").value;
			}
			catch(err) {
				url = url + "&camp_group_id=" + camp_group_id;
			}
			try {
				url = url + "&camp_division_id=" + document.getElementById("camp_division_id").value;
			}
			catch(err) {
				url = url + "&camp_division_id=" + camp_division_id;
			}		
		break;
		
	}
	
	url = url + "&divs=" + divs + "&form_name=" + form_name;

	var http = getHTTPObject();
	http.open("GET", url, true);
				
	http.onreadystatechange = function() {
		if (http.readyState == 4 && http.status == 200) {

			if (http.responseText.substr(0, 7) == "[SPLIT]") 
				var innerHTML = http.responseText.substr(7, http.responseText.length - 7);
			else 
				var innerHTML = http.responseText;
		
			var divisions = innerHTML.split("[SPLIT]");
						
			for (cntr = (divs.length - 1); cntr > -1; cntr--) {
				var div_name = "div_" + divs.substr(cntr, 1);
				document.getElementById(div_name).innerHTML = divisions[cntr];
			}
			
		}
	}
	http.send(null);
}

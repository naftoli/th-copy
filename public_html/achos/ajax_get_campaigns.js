function getHTTPObjct() {
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

function get_campaigns(divs, form_name) {
	var url = "";

	try {
		url = "ajax_get_campaigns.php?campaign_id=" + document.getElementById("campaign_id").value;
	}
	catch(err) {
		url = "ajax_get_campaigns.php?campaign_id=" + campaign_id;
	}
	
	switch (divs) {
		case "123":
		case "3":
			try {
				url = url + "&mission_id=" + document.getElementById("mission_id").value;
			}
			catch(err) {
				url = url + "&mission_id=" + mission_id;
			}		
		break;
	}
	
	url = url + "&divs=" + divs + "&form_name=" + form_name;
	
	var http = getHTTPObjct();
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

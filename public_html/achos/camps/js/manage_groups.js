var action = "get_all_groups";
var params = [""];
var url = "../../application/php/appInterface.php?action=" + action + "&params=" + params;	
var group_types = "";

$.getJSON(url, function(data) {	
	group_types = data;
	
	display_groups(0);
});



function display_groups(group_type_number) {
	group_type = group_types[group_type_number];
			
	var html = "\n";
	html = html+ "<h1>" + group_type.group_type_name + "</h1>\n";
	
	for (cntr2 = 0; cntr2 < group_type.divisions.length; cntr2++) {
		division = group_type.divisions[cntr2];
				
		html = html + "<div id='module-info' class='module'>\n";
		for (cntr3 = 0; cntr3 < division.groups.length; cntr3++) {
			group = division.groups[cntr3];
			
			html = html+ "<h1>" + group.group_name + " - " + division.division_name + "</h1>\n";
			
		}
		html = html + "</div>\n";
		
	}	
	
	document.getElementById("col_content").innerHTML = html;
}

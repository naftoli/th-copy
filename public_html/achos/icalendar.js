//Copyright Ariel Shkedi 2007-2010
var calendar;

function getDate(form, name, required) 
{
	if (calendar) 
	{
		calendar.parentNode.removeChild(calendar);
		calendar = null;
	}
	
	calendar = document.createElement('iframe');
	calendar.className = 'icalendar2';

	var text = form.elements[name+'_disp'];
	var value = form.elements[name];
	
	text.parentNode.style.position = 'relative';
	text.parentNode.insertBefore(calendar, text);
	
	calendar.callBack = function (value2, text2) 
	{
		if	(value2 != 'close' && (!required || (required && value2 != '' && text2 != ''))) 
		{
			value.value = value2;
			text.value = text2;
		}
		
		calendar.parentNode.removeChild(calendar);
		calendar = null;
	};
	
	calendar.src = 'icalendar.php?' + (value.value ? 'date=' + value.value : '') + (required ? '&required' : '');
}

function get_summary_date(form, name, required) 
{
	if (calendar) 
	{
		calendar.parentNode.removeChild(calendar);
		calendar = null;
	}
	
	calendar = document.createElement('iframe');
	calendar.className = 'icalendar2';

	var text = form.elements[name+'_disp'];
	var value = form.elements[name];
	
	text.parentNode.style.position = 'relative';
	text.parentNode.insertBefore(calendar, text);
	
	calendar.callBack = function (value2, text2) 
	{
		if	(value2 != 'close' && (!required || (required && value2 != '' && text2 != ''))) 
		{
			value.value = value2;
			text.value = text2;
		}
		
		calendar.parentNode.removeChild(calendar);
		calendar = null;
		
		form.submit();
	};
	
	calendar.src = 'icalendar.php?' + (value.value ? 'date=' + value.value : '') + (required ? '&required' : '');
}

function get_date(form, name, required) 
{
	if (calendar) 
	{
		calendar.parentNode.removeChild(calendar);
		calendar = null;
	}
	
	calendar = document.createElement('iframe');
	calendar.className = 'icalendar2';

	var element_id = name + "_disp";
	var text = document.getElementById(element_id);
	
	var value = document.getElementById(name).value;
	
	text.parentNode.style.position = 'relative';
	text.parentNode.insertBefore(calendar, text);
	
	calendar.callBack = function (value2, text2) {
	
		if (value2 != 'close' && (!required || (required && value2 != '' && text2 != ''))) 
		{
			value.value = value2;
			text.value = text2;
			document.getElementById(name).value = value2;
		}
		
		calendar.parentNode.removeChild(calendar);
		calendar = null;

		get_report();		
	};
	
	calendar.src = 'icalendar.php?' + (value.value ? 'date=' + value.value : '') + (required ? '&required' : '');
}

function get_board_date(form, name, required) 
{
	if (calendar) 
	{
		calendar.parentNode.removeChild(calendar);
		calendar = null;
	}
	
	calendar = document.createElement('iframe');
	calendar.className = 'icalendar2';

	var element_id = name + "_disp";
	var text = document.getElementById(element_id);
	
	var value = document.getElementById(name).value;
	
	text.parentNode.style.position = 'relative';
	text.parentNode.insertBefore(calendar, text);
	
	calendar.callBack = function (value2, text2) {
	
		if (value2 != 'close' && (!required || (required && value2 != '' && text2 != ''))) 
		{
			value.value = value2;
			text.value = text2;
			document.getElementById(name).value = value2;
		}
		
		calendar.parentNode.removeChild(calendar);
		calendar = null;

		get_board_report();		
	};
	
	calendar.src = 'icalendar.php?' + (value.value ? 'date=' + value.value : '') + (required ? '&required' : '');
}


function get_report()
{
	if ($("#class_id").val() == null)
		var class_id = 0;
	else
		var class_id = $("#class_id").val();

	var url = "user_possible_medals.php?school_id=" + $("#school_id").val() + "&class_id=" + class_id + "&start_date=" + $("#start_date").val() + "&end_date=" + $("#end_date").val();
	
	var http = getHTTPObject();
	http.open("GET", url, true);
				
	http.onreadystatechange = function() {
											
		if (http.readyState == 4 && http.status == 200) 
		{
			$("#report_div").html(http.responseText);
		} 
							
	}
							
	http.send(null);				
}

function get_board_report()
{
	if ($("#class_id").val() == null)
		var class_id = 0;
	else
		var class_id = $("#class_id").val();

	var url = "user_possible_boards.php?school_id=" + $("#school_id").val() + "&class_id=" + class_id + "&end_date=" + $("#end_date").val();
	
	var http = getHTTPObject();
	http.open("GET", url, true);
				
	http.onreadystatechange = function() {
											
		if (http.readyState == 4 && http.status == 200) 
		{
			$("#report_div").html(http.responseText);
		} 
							
	}
							
	http.send(null);				
}

function getHTTPObject() {
	var xmlhttp;

	if (window.XMLHttpRequest) 
	{
		xmlhttp = new XMLHttpRequest();
	}
	else if (window.ActiveXObject)
	{ 
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
					
		if (!xmlhttp) 
		{
			xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
		}
	}
				
	return xmlhttp; 
} 

function mashpia_ajax_get(div,start,url)
{
	$.ajax({
		type: 		"GET",
		cache: 		false,
		start: 		$(div).html(start),
		url: 		url,
		success: 	function(response) {
			$(div).html(response);
		}
	});
}

function mashpia_ajax_post(div,start,url,form)
{
	$.ajax({
		type: 		"POST",
		cache: 		false,
		start:		$(div).html('<div class="loading ui-corner-all">'+start+"</div>"),
		url: 		url,
		dataType: 	"text",
		data: 		$(form).serialize(),
		success: 	function(response)
		{
			var parsed_result = jQuery.parseJSON(response);
			if (!parsed_result)
			{
				return;
			}
			if (parsed_result.redirect == 1)
			{
				$(div).html('<div class="'+parsed_result.status+' ui-corner-all">'+parsed_result.reason+"</div>");
				window.location.replace(parsed_result.url);
			}
			else if (parsed_result.status == 'success' || parsed_result.status == 'failure')
			{
				$(div).html('<div class="'+parsed_result.status+' ui-corner-all">'+parsed_result.reason+"</div>");
			}
			else
			{
				$(div).html(parsed_result.data);
			}
		}
	});
}

function mashpia_validate(status_div,form_name,url)
{
	$(form_name).validate({
		submitHandler: function(form) {
			mashpia_ajax_post(status_div,'Authenticating...',url,form_name);
		}
	});
	$(form_name).submit();
}

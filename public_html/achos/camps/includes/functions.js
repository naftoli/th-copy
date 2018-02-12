function number_validation(e) {		
	var key;

	if (window.event)
		key = window.event.keyCode;
	else if (e)
		key = e.which;
			
	if (key == 8 || key == 0 || (key > 47 && key < 58))
		return true;
	else
		return false;			
}
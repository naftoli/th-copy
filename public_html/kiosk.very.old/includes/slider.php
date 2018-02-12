<script src="http://www.google.com/jsapi"></script>
<script>
	// Load jQuery
	  google.load("jquery", "1.3.2");
</script>
<script type="text/javascript" src="scripts/easySlider1.7.js"></script>
<script type="text/javascript">
	$(document).ready(function(){	
		$("#slider").easySlider({
			numeric: true, 
			controlsBefore:	'<div class="page_dots">',
			controlsAfter:	'</div>'
			});
	});	
</script>

<script type="text/javascript" src="scripts/easySlider1.7.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$("#slider").easySlider({
			numeric: true, 
			controlsBefore:	'<div class="page_dots">',
			controlsAfter:	'</div>'
			});
	});	
	
	$(window).load(function(){
		sliderPage();
	});	
	
	function sliderPage() {
		<?php
			if (isset($_GET["p"]) && $_GET["p"]!='') {
				echo "$('a',$('#controls" . $_GET["p"] . "')).click();";	
			}
		?>
		return false;
	}
</script>

<? 
if (isset($_POST["action"])) {
	echo 'Scanned';
} 
else {
?>

<script>
	$(function() {
		$('form').submit(function(e){
			e.preventDefault();
			$('#action').val('add');			
			$.post("includes/storescan.php", $("#scan_form").serialize(), function(data){
				if (data != ""){
					$('#scan').val('');
					$('#action').val('');
					$('.result').html(data).show().fadeOut(500);
				}
			});
		});
	});
	
	function return_key(e) {		
		var key;

		if (window.event)
			key = window.event.keyCode;
		else if (e)
			key = e.which;
		
		if (key == 13) {
			var message_list_item = document.getElementById("message");			
			var voucher_id = document.getElementById("voucher_id").value;
			
			var function_name = "scan_voucher";				
			var parameters = [voucher_id];
			var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
			$.getJSON(url, function(message) {
				if (message == 1) 
					alert("Scan could not locate voucher. Please try again.");
				else {
					$(message_list_item).html("<span class='title'>" + message + "</span>");
					document.getElementById("voucher_id").value = "";
				}
			});			
		
		}
	}
</script>
			
	<div class="slider">
	
		<div class="col_title">
			<span>Scan Vouchers</span>
		</div>
		
		<div class="col_content">
		
			<form id="scan_form">
			
				<div class="module lists forms" id="lists">
				
					<div class="module_content">
					
						<ul>
						
							<li>
							
								<span class="icon">
								</span>
								
								<span class="title">
									Scan Voucher
								</span>
								
								<span class="input">
									<input onkeypress="return_key(event);" type="text" id="voucher_id" name="voucher_id" />
									<input type="hidden" name="action" id="action" value="" />
								</span>
								
								<span class="title result"></span>
								
							</li>
								
							<li id="message">
								<span class="title"></span>
							</li>
							
						</ul>
						
					</div>
					
				</div>
				
			</form>
			
		</div>
		
	</div>
<? 
} 
?>

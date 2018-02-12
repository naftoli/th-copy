$(document).ready(function()
	{
		var prize_id = "";
		var prize_view = "";
		var prize_name = "";		
		var current_balance = 100000;
					
		// When the save button is clicked the cart session variable needs to be updated //
		$('#save_button').die().live('click',
			function(event) {
				// The quantity entered //
				var quantity = $("#form_" + prize_id).find("input[name=quantity]").val();
				
				// The points cost of the prize //
				var prize_miles = $("#form_" + prize_id).find("span[name=prize_miles]").html();
				
				// The cost of the purchase //
				var cost = quantity * prize_miles;
				
				// If the item is already in the cart then this is the quantity of the cart item //
				var old_quantity = $("#prizes_overlay").find("#form_" + prize_id).find("input[name=hidden_quantity]").val();
				
				// variable that will contain the difference between the   //
				// quantity of the cart item and the new quantity enetered //
				var difference = 0;
				
				if (old_quantity == 0 && quantity == 0)
				{
					$('prize_view_' + prize_id).css('display', 'none');
					$('#form_' + prize_id).css('display', 'none');
					$('#overlay_parent').css('display', 'none');
					$('#wrapper').fadeTo('normal', 1.0);
					
				}
				else
				{						
					// If the item is already in the cart then we have to figure out how //
					// many more can be purchased before the users balance is reached    //
					if (quantity > old_quantity && old_quantity > 0)
					{
						difference = quantity - old_quantity;
						var cost = difference * prize_miles;
					}
					else
					{
						var cost = quantity * prize_miles;	
					}
					
					// If the cost is greater than the balance then a message is alerted //
					if (cost > current_balance && quantity > old_quantity)
					{
						var max_items = Math.floor(current_balance / prize_miles);
						var total_quantity = parseInt(old_quantity) + parseInt(max_items);
						if (difference > 0 && max_items > 0)
						{
							$("#form_" + prize_id).find("input[name=quantity]").val(total_quantity);
							alert("You current balance only allows you to buy " + max_items + " more for a total of " + total_quantity);
						}
						else
						{
							$("#form_" + prize_id).find("input[name=quantity]").val(max_items);
							alert("You current balance only allows you to buy " + max_items);
						}
					}
					else
					{
						var url = "/kiosk/addcartitem/prize_id/" + prize_id + "/quantity/" + quantity;
						
						//************************************//
						// Ajax call to update the cart items //
						$.ajax({
							type : "GET",
							cache : false,
							url : url,
							dataType : "text",
							success : function(response) {
								var split_location = response.indexOf("[SPLIT2]");
								if (split_location > -1)
								{							
									current_balance = response.substr(split_location + 8, response.length - (split_location));							
									var divs_info = response.substr(0, split_location - 1);
									var split_location = divs_info.indexOf("[SPLIT1]");
									var cart_items_div = divs_info.substr(0, split_location - 1);
									var checkout_div = divs_info.substr(split_location + 8, response.length - (split_location));
									
									$("#prizes_overlay").find("span[name=user_balance]").html(current_balance);
									$("#prizes_overlay").find("#form_" + prize_id).find("input[name=quantity]").html(quantity); 
									$("#prizes_overlay").find("#form_" + prize_id).find("input[name=hidden_quantity]").val(quantity);
									
									update_badge_quantity(quantity);
									document.getElementById("cart_items_div").innerHTML = cart_items_div;
									document.getElementById("checkout_div").innerHTML = checkout_div;
									
									document.getElementById(prize_view).style.display = "none";
									document.getElementById("overlay_parent").style.display = "none";
									$('#wrapper').fadeTo('normal', 1.0);
								}
								else
								{
									alert("Update not performed.");	
								}
							}
						});
						// Ajax call to update the cart items //
						//************************************//
					}				

				}					
								
			}
		);
		
		// Make sure that only numeric values or a backspace is entered //
		$('input[name=quantity]').keypress(function(e) {
			var code = parseInt(e.keyCode ? e.keyCode : e.which);
			if (code != 8 && (code < 48 || code > 57))
			{
				return false;
			}
		});
					
		// Go to the store withdraw when the withdraw button is clicked //
		$('#withdraw_button').die().live('click',
			function(event) {
				$('#withdraw_form').submit();
			}
		);
		
		// Submit the form and call the storewithdrawAction //
		$('#cart_buynow').die().live('click',
			function(event) {
				$('#cart_buynow_form').submit();
			}
		);
		
		// Display the cart items in the checkout overlay //
		$('#checkout_button').die().live('click',
			function(event) {
				prize_view = "";
				$('#wrapper').fadeTo('normal', 0.3);
				$(".overlay").css('display', 'block');
				$(".checkout_overlay").css('display', 'block');
				$('#overlay_parent').css("top", Math.max(0, (($('body').height()-430)/2)) + 'px').fadeIn('normal');
			}
		);
				
		// Close the overlay //
		$('#close_overlay').die().live('click',
			function(event) {
				if (prize_view != "")
				{
					document.getElementById(prize_view).style.display = "none";
					document.getElementById("overlay_parent").style.display = "none";
					$('#wrapper').fadeTo('normal', 1.0);
				}
				else
				{
					$("#checkout_overlay").css('display', 'none');
					$(".overlay").css('display', 'none');
					$('#wrapper').fadeTo('normal', 1.0);
				}
			}
		);           
		
		// Open the overlay when the cart item is clicked //
		$('.cart_item').die().live('click',
			function(event) {
				var info = $(this).attr('id').split('_');
				prize_id = info[2];
				prize_view = "prize_view_" + prize_id;
				prize_name = $(this).attr('name');
				
				$("#overlay_parent").css('display', 'block');
				$(".overlay").css('display', 'block');
				
				$('#wrapper').fadeTo('normal', 0.3);
				$('#overlay_parent').css("top", Math.max(0, (($('body').height()-430)/2)) + 'px').fadeIn('normal');
				
				document.getElementById("overlay_parent").style.display = "block";
				document.getElementById(prize_view).style.display = "block";				
			}
		);           
		
		// Open the overlay when the prize is clicked //
		$('#show_overlay').die().live('click',
			function(event) {				
				prize_id = $(this).parents('div').attr('id');
				prize_view = "prize_view_" + prize_id;
				prize_name = $("#" + prize_view).find('div[name=prize_name]').html();
				
				if ($("div[name=" + prize_id + "]").find("div[name=badge]").size() > 0)
				{
					$("#form_" + prize_id).find("input[name=quantity]").val($("div[name=" + prize_id + "]").find("div[name=badge]").html());
				}
				
				$('#form_' + prize_id).css('display', 'block');
				$("#overlay_parent").css('display', 'block');
				$(".overlay").css('display', 'block');
				$('#wrapper').fadeTo('normal', 0.3);
				$('#overlay_parent').css("top", Math.max(0, (($('body').height()-430)/2)) + 'px').fadeIn('normal');
				document.getElementById(prize_view).style.display = "block";
			}
		);           
		
		$.extend($.fn.jScrollPane.defaults, {showArrows:true, scrollbarWidth: 42, arrowSize: 42});
		$('.scroll-pane').jScrollPane();
		$('.keypad').keypad({buttonImage: 'http://www.mashpia4.icorpa.com/images/front-end/kiosk/keypad_btn.jpg'});	
		
		function update_badge_quantity(quantity)
		{
			var prize_div = "div[name=" + prize_id + "]";
			
			if ($(prize_div).find("div[name=badge]").size() > 0)
			{
				if (quantity > 0)
				{
					//var badge_div = $(prize_div).find("div[name=badge]");
					$(prize_div).find("div[name=badge]").html(quantity);
					$(prize_div).find("div[name=badge]").css('display', 'block');
				}
				else
				{
					
					$(prize_div).find("div[name=badge]").html("");
					$(prize_div).find("div[name=badge]").css('display', 'none');
				}
			}
			else
			{
				var badge_div = "<div class='badge' name='badge' id='badge'>" + quantity + "</div>";
				var search_div = "div[name=pane_item_image_" + prize_id + "]";
				$(prize_div).find(search_div).before(badge_div);
			}
		}

	});
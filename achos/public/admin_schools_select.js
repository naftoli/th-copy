			var page_loaded = false;
			//var school_id = <?=$admin->school_id;?>;
			
			$(document).ready(function() 
			{
				var url = "ajax_get_classes.php?school_id=" + school_id;
				$.ajax({ url: url, success: function(data) { $(".class_list").find(".newListSelected").find("ul").html(data); } });
				
				var url = "ajax_get_students.php?school_id=" + $("#school_id").val() + "&class_id=" + $(this).val();
				$.ajax({ url: url, success: function(data) { $(".user_list").find(".newListSelected").find("ul").html(data); } });
				
				get_data();
				
				page_loaded = true;
			});
			
			$(function()
			{
				$('.marking_list div select').each(function() {
					//if (!$(this).find('option:selected').next().val()) $(this).siblings('a.next').addClass('disabled');
					//if (!$(this).find('option:selected').prev().val()) $(this).siblings('a.prev').addClass('disabled');
				});
						
				$('.marking_list div a.next').click(function() {
					var class_names = $(this).parent().attr("class").split(" ");
					
					if (class_names[0] == "school_list")
					{
						$(this).siblings('select').find('option:selected').next().attr('selected','selected').parent().change();
					}
					else
					{
						next_list_item(class_names[0]);
					}
				});
						
				$('.marking_list div a.prev').click(function() {
					var class_names = $(this).parent().attr("class").split(" ");
					
					if (class_names[0] == "school_list")
					{
						$(this).siblings('select').find('option:selected').prev().attr('selected','selected').parent().change();
					}
					else
					{
						previous_list_item(class_names[0]);
					}
				});
			
				$("#select_anchor_tag").live("mouseover mouseout", function(event) {
					if (event.type == 'mouseover') 
					{
						if ($(this).attr("class") != "hiLite")
							$(this).attr("style", "background-color:#E8EAEF");
					} 
					else 
					{
						if ($(this).attr("class") != "hiLite")
							$(this).attr("style", "background-color:#eee");
					}
				});	
			
				$("#school_id").sSelect().change(function () 
				{
					school_id = $(this).val();
					
					<? if (in_array("class_id", $selects)) : ?>
					var url = "ajax_get_classes.php?school_id=" + school_id;
					$.ajax({ url: url, success: function(data) 
						{ 
							$(".class_list").find(".newListSelected").find(".selectedTxt").html("All Platoons");
							$(".class_list").find(".newListSelected").find("ul").html(data);

							<? if (in_array("class_id", $selects)) : ?>
							var url = "ajax_get_students.php?school_id=" + school_id;
							$.ajax({ url: url, success: function(data) 
								{ 
									$(".user_list").find(".newListSelected").find(".selectedTxt").html("All Soldiers");
									$(".user_list").find(".newListSelected").find("ul").html(data);	

									get_data();									
								} 
							});
							<? else : ?>
							get_data();
							<? endif; ?>
						} 
					});
					<? else : ?>
					get_data();
					<? endif; ?>
					
					
				});	
				
				<? if (in_array("class_id", $selects)) : ?>
				$("#class_id").sSelect().change(function () 
				{
				});	
				<? endif; ?>
				
				<? if (in_array("user_id", $selects)) : ?>
				$("#user_id").sSelect().change(function () 
				{
				});	
				<? endif; ?>
				
			});

			function anchor_tag_click(anchor_tag)
			{
				var class_names = $(anchor_tag).parent().parent().parent().parent().attr("class").split(" ");
				
				$.each($($(anchor_tag).parent().parent()).find("a"), function() { 
					$(this).attr("class", "");
					$(this).attr("style", "background-color:#eee");
				});
				
				$(anchor_tag).parent().parent().parent().find(".selectedTxt").html($(anchor_tag).html()).click();												
				$(anchor_tag).css("background", "#D5D8DE").attr("class", "hiLite");
				
				<? if (in_array("user_id", $selects)) : ?>
				if (class_names[0] == "class_list")
					get_soldiers();
				<? endif; ?>
				
				get_data();
			}

			function get_soldiers()
			{
				var url = "ajax_get_students.php?school_id=" + $("#school_id").val();
				<? if (in_array("class_id", $selects)) : ?>
				url = url + "&class_id=" + $(".class_list").find("ul").find("a.hiLite").attr("data");
				<? endif; ?>
				$.ajax({ url: url, success: function(data) 
					{ 
						$(".user_list").find(".newListSelected").find(".selectedTxt").html("All Soldiers");					
						$(".user_list").find(".newListSelected").find("ul").html(data); 
						
						get_data();
					} 
				});				
			}
			
			function next_list_item(class_name)
			{
				var anchor_tags = $("." + class_name).find("ul").find("a");
				var no_of_anchor_tags = $("." + class_name).find("ul").find("a").size();				
				
				var hilited_anchor_tag_no = 0;
				var selected_text = "";
				var found = false;
				$.each($("." + class_name).find("ul").find("a"), function(index) { 
					if ($(this).attr("class") == "hiLite" && index < (no_of_anchor_tags - 1))
					{
						hilited_anchor_tag_no = index;
						$(this).attr("class", "");
						$(this).attr("style", "background-color:#eee");
						found = true;
					}
					
					if (found == true && index == (hilited_anchor_tag_no + 1))
					{
						selected_text = $(this).html();
						$(this).attr("class", "hiLite");
						$(this).attr("style", "background-color:#D5D8DE");
					}
				});
				
				if (selected_text != "")
					$("." + class_name).find(".newListSelected").find(".selectedTxt").html(selected_text);
					
				if (class_name == "class_list")
					get_soldiers();	
			}
			
			function previous_list_item(class_name)
			{
				var anchor_tags = $("." + class_name).find("ul").find("a");
				var no_of_anchor_tags = $("." + class_name).find("ul").find("a").size();
				
				var selected_text = "";
				
				for (atno = (no_of_anchor_tags - 1); atno > 0; atno--) 
				{
					var anchor_tag = anchor_tags[atno];
						
					if ( $(anchor_tag).attr("class") == "hiLite")
					{
						$(anchor_tag).attr("class", "");
						$(anchor_tag).attr("style", "background-color:#eee");
							
						atno--;
						anchor_tag = anchor_tags[atno];
						$(anchor_tag).attr("class", "hiLite");
						$(anchor_tag).attr("style", "background-color:#D5D8DE");
						selected_text = $(anchor_tag).html();
						break;
					}
				}
				
				if (selected_text != "")
					$("." + class_name).find(".newListSelected").find(".selectedTxt").html(selected_text);
					
				if (class_name == "class_list")
					get_soldiers();
			}	
			
			
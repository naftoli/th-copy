$(function() {
	$("#dates").hide();
	$("#reportType").hide(); 
	$("#campaign").hide();
	$("#tasks").hide();
    $("#school").sSelect();
    
    $("#school").change(function() {
    	removeAllLists(true);
    	var id = $(this).val();
    	if (id > 0) {
    		$.get('ajax/getClasses.php', {id : id}, function(data) {
    			data = $.parseJSON(data);
    			$("#class").append("<option value='0'>Choose class</option>");
    			for (p in data) {
    				$("#class").append("<option value='" + p + "'>" + data[p] + "</option>");
    			}
    			$("#class").sSelect();
    			if ($("#dates").css('display') == 'none') {
        			$("#start").sSelect();
        			$("#end").sSelect();
        			$("#dates").show();
        		}
    		});
    		getCampaigns();
    		$("#tasks").hide();
    	}
    });
    
    $("#class").change(function() {
    	removeAllLists();
    	var id = $(this).val();
    	if (id > 0) {
    		$.get('ajax/getUsers.php', {id : id}, function(data) {
    			data = $.parseJSON(data);
    			$("#user").append("<option value='-1'>All students</option>");
    			for (p in data) {
    				$("#user").append("<option value='" + p + "'>" + data[p] + "</option>");
    			}
    			$("#user").sSelect();
    		});
    		getTasks();
    	}
    });
    
    $("#user").change(function() {
    	getTasks();
    });
    
    $("#subject").change(function() {
    	getTasks();
    });
    
    $(".date").change(function() {
    	//make sure start date is less than end date
    	var start = $("#start").val();
    	var end = $("#end").val();
    	if (start > 0 && end > 0) {
    		if (start >= end) {
	    		alert("From parsha must be before end parsha!");
	    		this.stop(); //doesn't work but causes the dropdown to stay down until right value is chosen
    		} else {
	    		$("#reportType").show();
	    		$("#submit").show();
    		}
    	}
    });
    
    $(".reportType").click(function() {
    	if ($(this).val() == 1) {
    		getCampaigns();
    		$("#submit").hide();
    	} else {
    		$("#campaign").hide();
    		$("#tasks").hide();
    		$("#task").empty();
    		$("#submit").show();
    	}
    });
    
    function removeAllLists(addGrade) {
    	var e = ['user', 'task'];
    	if (addGrade) {
    		e.push('class');
    	}
    	var num = e.length;
    	for (i = 0; i < num; i++) {
    		var elem = e[i];
    		$("#" + elem).next("div").remove(".newListSelected");
    		$("#" + elem).empty();
    	}
    }
    
    function getCampaigns() {
    	//make sure start and end date have real values and that report type is to select tasks
    	var start = $("#start").val();
    	var end = $("#end").val();
    	if (start > 0 && end > 0) {
    		var e = $(".reportType");
	    	var type;
	    	for (i = 0; i < e.length; i++) {
	    		if ($(e[i]).is(":checked")) {
	    			type = $(e[i]).val();
	    		}
	    	}
	    	if (type == 1) {
	        	//get campaigns
	        	$("#subject").next("div").remove(".newListSelected");
	        	$("#subject").empty();
	        	var school = $("#school").val();
				$.get('ajax/getCampaigns.php', {id : school}, function(data) {
					data = $.parseJSON(data);
					$("#subject").append("<option value='0'>Choose campaign</option>");
					for (p in data) {
						$("#subject").append("<option value='" + p + "'>" + data[p] + "</option>");
					}
					$("#subject").sSelect();
					$("#campaign").show();
				});
			} else {
				$("#submit").show();
			}
		}
    }
    
    function getTasks() {
    	if (($("#campaign").css('display') == 'block') && ($(".reportType:checked").val() == 1)) {
    		$("#tasks").find("p").remove();
        	$("#task").next("div").remove(".newListSelected");
        	$("#task").empty();
        	
        	$("#loading").empty();
    		$("#loading").append('<img src="../images/loadingbar.gif" />');
    	
        	var id = $("#subject").val();
        	var user, grade;
        	if ($("#user").val() > 0) {
        		user = $("#user").val();
        		grade = 0;
        	} else {
        		user = 0;
        		grade = $("#class").val();
        	}
        	//alert(id);
        	if (id > 0) {
        		$.get('ajax/getTasks.php', {
        			school : $("#school").val(), 
        			subject : id, 
        			start : $("#start").val(), 
        			end : $("#end").val(), 
        			grade : grade, 
        			user : user, 
        			debug : false 
        		}, function(data) {
        			var total = 0;
        			data = $.parseJSON(data);
        			$.each(data, function(cat, info) { 
        				$.each(info, function(enrolled, names) { 
        					$.each(names, function(name, types) {
        						$("#task").append("<option value=\"" + encodeURIComponent(name) + "\">" + name + "</option>");
        						total++;
        					});
        				});
        			});
        			$("#loading").hide();
        			if (total == 0) {
        				$("#task").hide();
        				$("#tasks").append("<p>There are no tasks that fit your selection, please modify your selection.</p>");
        				$("#tasks").show();
        			} else {
	        			$("#task").attr('size', total);
	        			$("#tasks").show();
	        			$("#task").show();
	        			$("#submit").show();
	        		}
        		});
        	}
        }
    }
});
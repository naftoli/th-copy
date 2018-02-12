<?
require_once '../db.php';
$user_id = mysql_real_escape_string( $_GET['id'] );

$sql = "select user_photo_id from users where user_id = " . $user_id;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$photo = $row['user_photo_id'];

//get today's day
//$jd = unixtojd();
//$today = intval(date('w'));
//$start = $jd - ($today + 2);
//$end = $start + 6;

$start = 2457277;
$end = 2457661;
			
require '../class.tasksCustomizationNew.php';
$tc = new TasksCustomizationNew;
$both = $tc->getCampaignsForChild( $user_id );
$campaigns = $both['campaigns'];
//$enrolled = $both['enrolled'];

$campaignLogos = array(
	1	=>	'Tehillim.gif',
	4	=>	'Tefilla.gif',
	12	=>	'Mivtzoim.gif',
	13	=>	'Niggunim.gif',
	16	=>	'hiskashrus.gif',
	21	=>	'sefer-hamitzvos.gif',
	27	=>	'tanya.gif',
	40	=>	'Yom-Dipagra.gif',
	41	=>	'Father-Son.gif',
	42	=>	'Footsteps.gif',
	45	=>	'Cheshbon-Hanefesh.gif',
	90	=>	'Chitas.gif',
	100	=>	'Brias-Haguf.gif'
);
?>
<!doctype html>
<html class="no-js" lang="">
    <head>
    	<? include 'inc/head.php' ?>
        <title></title>
        <style>
        	.save {
        		float: right;
        		margin-bottom: 15px;
        	}
        	.alert {
        		margin-bottom: 0;
        	}
        </style>
    </head>
		
    <body class="page-goals">
        <header class="navbar" id="top" role="banner">
            <div class="container">
                <div class="navbar-header">
                	<h1>My Goals</h1>
                </div>
            </div>
        </header>
        
        <div class="personalImg">
        	<? if (!empty($photo)) : ?>
        	<img id="userImg" src="../../../file_view.php?id=<?=$photo?>">
        	<? endif; ?>
        </div>
        
        <div class="container">
            <div class="content">
            	<!--
            	<div class="text-left" style="margin-bottom: 20px;">
					<input type="button" id="expandAll" class="btn btn-danger btn-sm" value="Expand All" style="background-color: #5e1c77;border-color:#834999;" />
				</div>
            	-->
            	<? foreach ( $campaigns as $id => $campaign ) : ?>
            
	                <div class="panel panel-default">
	                	<div id="spinner"></div>
	                	<div class="panel-heading"><i class="glyphicon glyphicon-chevron-right"></i><?=$campaign?>
	                		<!--<div class="pull-right small points"><?=$points?> Points Needed</div>-->
	                	</div>
	                
	                	<div class="collapse">
	                		<div class='alert alert-warning' role='alert'>
		    					<div class='media'>
		    						<div class='media-left'>
			    						<!--<img class='media-object' width="50px;"
			    							src="../mission_report/campaignLogos/<?=$campaignLogos[$id]?>" alt='Camapign Logo'>-->
								  	</div>
								  	<div class="media-body">
								    	<?
								    	$sql = "select * from subjects where subject_id = $id";
										$result = mysql_query($sql);
										$row = mysql_fetch_assoc($result);
										echo $row['subject_slogan'];
								    	?>
								  	</div>
								</div>
							</div>
	                        <div class="panel-body" id="<?=$id?>">
	                        	
	                        	<ul class="list-unstyled tasks"></ul>		                        		
	                        	
	                        </div>
	                    </div>
	                </div>
	                
	        	<? endforeach; ?>
                            
            </div>
        </div>
         
    	<? include 'inc/footer.php' ?>

    	<? include 'inc/foot.php' ?>
    	
    	<script type="text/javascript" src="reg/js/js.cookie.js"></script>
    	<script type="text/javascript" src="js/spin.js"></script>
    	<script>
    		$( function() {
    			//$(".form-group").hide();
    			var url = location.toString();
				var pos = url.indexOf('='); 
				var id = url.substring( pos+1 );
				
    			$.post('reg/ajax/checkAuth.php', { user_id : id, admin_id : Cookies.get('admin') }, function( success ) {
					if (success == 0) {
						window.location = "/mobile";
					}
				});
    			
    			$("#missionsLink").attr('href', '/mobile/missions.php?id=' + id);
				$("#goalsLink").attr('href', '/mobile/goals.php?id=' + id);
				$("#rankLink").attr('href', '/mobile/reg/rank.html?id=' + id);
				
    			$("#expandAll").click( function() {
    				$('.panel').trigger('click');
        			//$(this).parent().parent().parent().find('.panel-heading').trigger('click');
        		});
        		
        		$(".panel").click( function() {
        			var container = this;
        			var campaign = $(this).find('.panel-body').attr('id');
    				//var container = $(this).parent().parent().parent(); 
    				//var campaign = $(this).parent().attr('id');
    				var opts = {
						  lines: 13 // The number of lines to draw
						, length: 28 // The length of each line
						, width: 14 // The line thickness
						, radius: 42 // The radius of the inner circle
						, scale: 1 // Scales overall size of the spinner
						, corners: 1 // Corner roundness (0..1)
						, color: 'grey' // #rgb or #rrggbb or array of colors
						, opacity: 0.25 // Opacity of the lines
						, rotate: 0 // The rotation offset
						, direction: 1 // 1: clockwise, -1: counterclockwise
						, speed: 1 // Rounds per second
						, trail: 60 // Afterglow percentage
						, fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
						, zIndex: 2e9 // The z-index (defaults to 2000000000)
						, className: 'spinner' // The CSS class to assign to the spinner
						, top: '50%' // Top position relative to parent
						, left: '50%' // Left position relative to parent
						, shadow: true // Whether to render a shadow
						, hwaccel: true // Whether to use hardware acceleration
						, position: 'absolute' // Element positioning
					}
    				var target = document.getElementById('spinner');
					var spinner = new Spinner(opts).spin(target);
			    
    				$.get('../ajax/getTasks.php', {
    					subject : campaign,
    					user : <?=$user_id?>, 
    					start : <?=$start?>,
    					end : <?=$end?>
    				}, function( data ) {
    					var data = $.parseJSON( data );
    					if ( data.length == 0 ) {
							$(container).empty();
							return;
						}
    					var str = "";
    					for ( var cat in data ) {
    						for ( var enrolled in data[cat] ) {
    							str += "<li><input name='tasks[]' type='checkbox' class='category' value=\"" + campaign + "|" + cat + "\"";
                            	if ( enrolled == '1' ) {
                                    str += ' checked ';
                                }
                                str += "/> <b>" + cat + " ";
                                
	    						$.ajax({
	    							type : "GET", 
	    							url : '../ajax/getMissions.php',
	    							async : false,
	    							data : {
	    								subject : campaign,
	    								task : cat, 
				    					user : <?=$user_id?>, 
				    					start : <?=$start?>,
				    					end : <?=$end?>
	    							}, 
	    							success : function( info ) {
	    								var mission = $.parseJSON( info );
	    								for ( var week in mission ) {
	    									var mandatory = mission[week]['mandatory'];
	    									if (mandatory == '1') {
	    										str += "<span style='color:red'>*</span>";
	    										break;
	    									}
	    								}
	    							}
	    						});
	    						
	    						str += "</b></li><div class='task'>";
    							for ( var task in data[cat][enrolled] ) {
    								 str += task + "<br />";
    							}
    							str += "</div>";
    						}
    					}
    					str += "<p><button class='btn btn-danger btn-sm save' style='background-color : #5e1c77;border-color:#834999;'>Save</button></p>";
                        $("#" + campaign).find("ul").append(str);
                        $(".spinner").empty();
                        
                        $(".category").click( function(e) { 
	                        //e.preventDefault();
	                        var val = $(this).val();
	                        var checked = $(this).is(":checked");
	                        updateArray( tasks, checked, val );
	                        updateArray( tasksAdded, !checked, val );
	                    });
    				});
    			});
    			
	            var tasks = [];
	            var tasksAdded = [];
	
	            //function to use for updating any of the above arrays
	            function updateArray(name, checked, val) {  
	            	var found = false;
	                for (i = 0; i < name.length; i++) {
	                    if (name[i] == val) {
	                        found = true;
	                        break;
	                    }
	                } 
	                if (checked) { 
						if (found)  
	                        name.splice( i, 1 );
	                } else { 
	                    if (!found)
	                        name.push(val);
	                }
	            }
	            
	            $(document).on("click", ".save", function() {
	            	$.post('../ajax/customize.php', { 
	                    tasks : tasks, 
	                    tasksAdded : tasksAdded, 
	                    user : <?=$user_id?>, 
	                    start : <?=$start?>,  
	                    end : <?=$end?> 
	                }, function( data ) { 
	                    //alert( data );
	                    alert( "Saved." );
	                    //history.go(0);
	                    //window.location = 'goals.php';
	                });
	            });
    			/*
    			$(".addNew").click( function() {
    				var newInput = $(this).parent().find(".newGroup");
    				newInput.show();
    				newInput.find(".newTask").select();
    			});
    			
    			$("#create").click( function() {
    				//var user = <?=$_SESSION['user_id']?>;
    				var label = $(this).parent().parent().attr('id');
    				var subtask = $(this).parent().find('.newSubtask').val().trim();
    				var task = $(this).parent().find('.newTask').val().trim();    				
    				var add = confirm("Are you sure you want to add \"" + subtask + "\" to " + task + " ?");
    				if (add) {
	    				$.post('../ajax/createTask.php', {
                			 label : label, 
                			 task : task, 
                			 subtask : subtask, 
                			 user : user
                		}, function( data ) {
                			if (data == 1) {
                				alert("Task Created");
                				window.location.href = 'goals.php';
                			} else {
            					alert(data);
                			}
                		});
	    			} else {
	    				//$(this).val('');
	    				$(this).parent().hide();
	    			}
    				return false;
    			});
    			
    			$(".add").click( function() {
    				var input = $(this).parent().parent().parent().find(".form-group");
    				input.show();
    				input.find(".sub").focus();
    			});
    			
    			$(".sub").blur( function() {
    				//var user = <?=$_SESSION['user_id']?>;
    				var label = $(this).parent().parent().parent().attr('id');
    				var subtask = $(this).val().trim();
    				var task = $(this).parent().parent().find("h4.task-header").text().trim();    				
    				var add = confirm("Are you sure you want to add \"" + subtask + "\" to " + task + " ?");
    				if (add) {
	    				$.post('../ajax/createTask.php', {
                			 label : label, 
                			 task : task, 
                			 subtask : subtask, 
                			 user : user
                		}, function( data ) {
                			if (data == 1) {
                				alert("Task Created");
                				window.location.href = 'goals.php';
                			} else {
            					alert(data);
                			}
                		});
	    			} else {
	    				//$(this).val('');
	    				$(this).parent().hide();
	    			}
    			});
    			
    			$(".delete").click( function() {
    				//var user_id = <?=$_SESSION['user_id']?>;
    				var task = $(this).parent().parent().parent().find('h4.task-header').text().trim();
    				var label = $(this).parent().parent().parent().parent().attr('id');
    				var del = confirm("Are you sure you want to delete " + task + " ?");
    				if (del) {
	    				$.post('../ajax/deleteTask.php', {
	    					task : task, 
	    					label : label, 
	    					is_task : 1, 
	    					user_id : user_id
	    				}, function( data ) {
	    					if (data == 1) {
	    						alert("Deleted.");
	    						window.location.href = "goals.php";
	    					} else {
	    						alert( data );
	    					}
	    				});
	    			}
    			});
    			
    			$(".deleteSub").click( function() {
    				//var user_id = <?=$_SESSION['user_id']?>;
    				var task = $(this).parent().parent().parent().text().trim();
    				var label = $(this).parent().parent().parent().parent().parent().parent().attr('id');
    				var del = confirm("Are you sure you want to delete " + task + " ?");
    				if (del) {
	    				$.post('../ajax/deleteTask.php', {
	    					task : task, 
	    					label : label, 
	    					is_task : 0, 
	    					user_id : user_id
	    				}, function( data ) {
	    					if (data == 1) {
	    						alert("Deleted.");
	    						window.location.href = "goals.php";
	    					} else {
	    						alert( data );
	    					}
	    				});
	    			}
    			});
    			
    			$(".edit").click( function() {
    				var h4 = $(this).parent().parent().parent().find('h4.task-header');
    				var task = h4.text().trim();
    				h4.html("<input type='text' size='" + task.length + "' class='editTask' value='" + task + "' />");
    				h4.after("<input class='oldTask' type='hidden' value='" + task + "' />");
    				h4.find("input").select();
    				
    				$(".editTask").blur( function() {
	    				//var user = <?=$_SESSION['user_id']?>;
	    				var label = $(this).parent().parent().parent().parent().attr('id');
	    				var oldTask = $(this).parent().parent().find('.oldTask').val();
	    				var newTask = $(this).val().trim();
	    				$.post('../ajax/editTask.php', {
	    					user : user, 
	    					label : label, 
	    					oldTask : oldTask, 
	    					newTask : newTask
	    				}, function( data ) {
	    					if (data == 1) {
	    						alert("Task has been changed.");
	    						window.location.href = "goals.php";
	    					} else {
	    						alert( data );
	    					}
	    				});
	    			});
    			});
    			
    			$(".editSub").click( function() {
    				var sub = $(this).parent().parent().parent().find('.subName');
    				var subtask = sub.text().trim();
    				sub.html("<input type='text' size='" + subtask.length + "' class='editSubTask' value='" + subtask + "' />");
    				sub.after("<input class='oldSub' type='hidden' value='" + subtask + "' />");
    				sub.find("input").select();
    				
    				$(".editSubTask").blur( function() {
    					//var user = <?=$_SESSION['user_id']?>;
    					var label = $(this).parent().parent().parent().parent().parent().attr('id');
    					var oldTask = $(this).parent().parent().find('.oldSub').val();
    					var newTask = $(this).val().trim();
    					$.post('../ajax/editTask.php', {
	    					user : user, 
	    					label : label, 
	    					oldTask : oldTask, 
	    					newTask : newTask
	    				}, function( data ) {
	    					if (data == 1) {
	    						alert("Task has been changed.");
	    						window.location.href = "goals.php";
	    					} else {
	    						alert( data );
	    					}
	    				});
    				});
    			});
    			*/
    		})
    	</script>
        
    </body>
</html>		
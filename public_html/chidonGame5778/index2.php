        <!DOCTYPE html>
        <html lang="en">
          <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Chidon Gameshow</title>

            <!-- Bootstrap -->
            <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
            
            <!-- Main Style -->
            <link rel="stylesheet" type="text/css" href="assets/css/main.css">

            <!--Icon Fonts-->
            <link rel="stylesheet" media="screen" href="assets/fonts/font-awesome/font-awesome.min.css" />
            
            <style>
            	body {
            		background: #13ff02;
            	}
            </style>
                        
          </head>

        <body>
     	
     	<br />
     	<br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <div id="lower">
        	<table>
        		<tr>
        			<td><button class="btn first">ספר המצוות</button></td>
        			<td><button class="btn second">יד החזקה</button></td>
        			<td><button class="btn third">פירוש המשניות</button></td>
        		</tr>
        		<tr>
        			<td><input type="checkbox" class="chk" value='1' /></td>
        			<td><input type="checkbox" class="chk" value='2' /></td>
        			<td><input type="checkbox" class="chk" value='3' /></td>
        		</tr>
        	</table>
        	
        	<table>
        		<tr>
        			<td><button class="chkAll">Check All</button></td>
        			<td><button class="unChkAll">Uncheck All</button></td>
        			<td>Points to award <input type="text" id="points" /></td>
        			<td><button class="go">Go</button></td>
        		</tr>
        	</table>
        	
        	<table>
        		<tr>
        			<td><button class="btn val">10</button></td>
        			<td><button class="btn val">50</button></td>
        			<td><button class="btn val">100</button></td>
        		</tr>
        	</table>
        </div>
        	
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <!-- Pricing Table Section -->
        <section id="pricing-table">
            <div class="container">
                <div class="row">
                    <div class="pricing">
                        <div class="col-md-4 col-sm-12 col-xs-12">
                            <div class="pricing-table">
                            	<div class="pricing-list">
                                    <ul>
                                        <li><span>ספר המצוות</span></li>
                                    </ul>
                                </div>
                                
                                <div class="pricing-header">
                                    <p class="pricing-rate team1">0</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 col-sm-12 col-xs-12">
                            <div class="pricing-table">
                            	<div class="pricing-list">
                                    <ul>
                                        <li><span>יד החזקה</span></li>
                                    </ul>
                                </div>
                                
                                <div class="pricing-header">
                                    <p class="pricing-rate team2">0</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 col-sm-12 col-xs-12">
                            <div class="pricing-table">
                            	<div class="pricing-list">
                                    <ul>
                                        <li><span>פירוש המשניות</span></li>
                                    </ul>
                                </div>
                                
                                <div class="pricing-header">
                                    <p class="pricing-rate team3">0</p>
                                </div>
                            </div>
                        </div>
                        
						<!--
                        <div class="col-md-4 col-sm-12 col-xs-12">
                            <div class="pricing-table">
                                <div class="pricing-header">
                                    <p class="pricing-title">Business Plan</p>
                                    <p class="pricing-rate"><sup>$</sup> 20 <span>/Mo.</span></p>
                                    <a href="#" class="btn btn-custom">And Get Free Month</a>
                                </div>

                                <div class="pricing-list">
                                    <ul>
                                        <li><i class="fa fa-envelope"></i>10,000 messages</li>
                                        <li><i class="fa fa-signal"></i><span>unlimited</span> data</li>
                                        <li><i class="fa fa-user"></i><span>unlimited</span> users</li>
                                        <li><i class="fa fa-smile-o"></i>first 10 day free</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
						-->
                	</div>
                </div>
            </div>
        </section>
		<!-- Pricing Table Section End -->
		
        </body>
        <script src="../jquery-1.8.1.min.js"></script>
        <script>
        	$(function() {
        		$(".chkAll").click( function() {
        			$(".chk").attr('checked', true);
        		});
        		
        		$(".unChkAll").click( function() {
        			$(".chk").attr('checked', false);
        		});
        		
        		$(".val").click( function() {
        			var val = $(this).text();
        			$("#points").val(val);
        		});
        		
        		$(".first").click( function() {
        			$("input[value='1']").trigger('click');
        		});
        		
        		$(".second").click( function() {
        			$("input[value='2']").trigger('click');
        		});
        		
        		$(".third").click( function() {
        			$("input[value='3']").trigger('click');
        		});
        		
        		$(".go").click( function() {
        			var points = parseInt($("#points").val());
        			if (!isNaN(points) && points != 0) {
	        			var teams = [];
	        			$("#lower input").each( function(i, v) {
	        				if ($(this).is(":checked")) {
	        					teams.push($(this).val());
	        				}
	        			});
	        			for (var i in teams) {
	        				var team = teams[i];
	        				var current = $(".team" + team).text();
	        				var total = points + parseInt(current);
	        				$(".team" + team).fadeOut(300).delay().fadeIn().fadeOut(300).
	        				delay().fadeIn().fadeOut(300).delay().fadeIn().text(total);
	        			}
	        		}
        		});
        	});
        </script>
        </html>
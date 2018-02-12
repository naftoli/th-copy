<!DOCTYPE html>
<html>
<head>
<title>Chidon</title>
<meta charset="utf8">
<link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="js/jquery.min.js"></script>
<!-- Custom Theme files -->
<!--theme-style-->
<link href="css/style.css" rel="stylesheet" type="text/css" media="all" />	
<!--//theme-style-->
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1255" />
<meta name="keywords" content="" />
<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
<!--fonts-->

<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Sanchez" />
<!--//fonts-->
<script type="text/javascript" src="js/move-top.js"></script>
<script type="text/javascript" src="js/easing.js"></script>
				<script type="text/javascript">
					jQuery(document).ready(function($) {
						$(".scroll").click(function(event){		
							event.preventDefault();
							$('html,body').animate({scrollTop:$(this.hash).offset().top},1000);
						});
					});
				</script>
<link href="css/nav.css" rel="stylesheet" type="text/css" media="all"/>
<style type="text/css">
body,td,th {
	font-family: Sanchez, "Sanchez Slab";
}
</style>
<script src="js/easyResponsiveTabs.js" type="text/javascript"></script>
		    <script type="text/javascript">
			    $(document).ready(function () {
			        $('#horizontalTab,#horizontalTab1,#horizontalTab2').easyResponsiveTabs({
			            type: 'default', //Types: default, vertical, accordion           
			            width: 'auto', //auto or any width like 600px
			            fit: true   // 100% fit in a container
			        });
			    });
			   </script>


<script src="js/main.js"></script> <!-- Resource jQuery -->
	   
</head>
<body>
<!--header-->

<div class="container">
<div class="main-top">
	<div class="main">
		<div class="header">
			<div class="header-top">
				<div class="header-in">
					<div class="logo"> <img src="images/topheader.png" alt="" >
					</div>
					
				  <div class="clearfix"> </div>
				</div>
				
				<div class="clearfix"> </div>
			</div>
			<!---->
			
	</div>
		
	<?php require 'menu.php' ?>
	
<div class="content">
	<div class="col-md-9 content-top">
		<div class="number">
				
    		
    	  <div class="row_8">
			
			<div class="banner"><img src="images/books.png"  alt=""/></div>
			<style>
				.indent {
					margin-left: 20px;
				}
			</style>
			<div class="col-md-10 ">
				<div class="caption">
					<h4><strong>Books that are available for purchase:</strong></h4>
					<p class="indent page-top">
						Book 1 — Grade 4<br />
						Book 2 — Grade 5<br />
						Book 3 — Grade 6<br />
						Book 4 — Grade 7<br />
						Book 5 — Grade 8<br />
					</p>
					<p class="page-top">
						<span style="text-decoration: line-through;">List price $55</span> $45 Chidon price
					</p>
				</div>
			</div>
		
			<div class="clearfix"></div>
			<p class="page-top">
				Books shipped within the U.S. will arrive within 5-7 business days. Books shipped internationally will arrive in approximately 14 business days.
				<br /><br />
				To order books please contact your school’s Chidon Coordinator.
			</p>
		  
		</div>
			
		</div>
			<!---->
			
			<!---->
			
			<!---->
	</div>
	<!---->
		<!---->
	
		<div class="clearfix"></div>
		</div>
	</div>
	
		<div class="archives-top">
				
				<div class="col-md-4 top-archives">
				  <h3>The Yahadus curriculum was created in memory of Mrs. Sara (Charlotte) Rohr
</h3>
				</div>
				<div class="col-md-4 top-archives">
				  <h3>A project of: <img src="images/sponsors.png" width="102" height="47" alt=""/></h3>
				</div>
				<div class="col-md-4 top-archives">
               
				  
				  
				  <h3>Chidon Sponsor:
					הרוצה בעילום שמו להצלחה מופלגה בגשמיות וברוחניות
					<? 
					//$str = " הרוצה בעילום שמו להצלחה מופלגה בגשמיות וברוחניות ";
					//$he = iconv('utf8', 'windows-1255', $str);
					//echo $he;
					?>
				  </h3>
					
				</div>
                
		<div class="col-md-12 top-archives" align="center" style="background: #fff">
			לע"נ הרב אליעזר בן הרב מרדכי ע"ה וונגר 
			לע"נ הרב יצחק בן הרב אליעזר צבי זאב ע"ה צירקינד
		</div>
                
      <div class="col-md-12 top-archives">
					<h3>Chidon Partners:
					<div id="sponsors">
							<div>
							<?
							$partners = array();
							if ($dh = opendir(getcwd() . '/sponsors')) {
							    while (($file = readdir($dh)) !== false) {
							    	if ($file != '.' && $file != '..') {
							      		$partners[] = $file;
									}
							    }
							    closedir($dh);
							}
							sort($partners);
							foreach ($partners as $key => $file) {
								if ($key && ($key % 5 == 0)) {
									echo "</div><div style='clear: both'></div><div>";
								}
								echo "<img src='sponsors/" . $file . "' />";
							}
							?>
							</div>
						</div>
					</h3>
					
				</div>
				<div class="clearfix"></div>
                
  </div>
	
	
	</div>
	
<script type="text/javascript">
						$(document).ready(function() {
							/*
							var defaults = {
					  			containerID: 'toTop', // fading element id
								containerHoverID: 'toTopHover', // fading element hover id
								scrollSpeed: 1200,
								easingType: 'linear' 
					 		};
							*/
							
							$().UItoTop({ easingType: 'easeOutQuart' });
							
						});
					</script>
				<a href="#" id="toTop" style="display: block;"> <span id="toTopHover" style="opacity: 1;"> </span></a>


</div>
</body>
</html>
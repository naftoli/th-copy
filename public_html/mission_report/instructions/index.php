<!DOCTYPE html>
<html>
	<head>
		<link href="/admin_styles.css" rel="stylesheet" type="text/css">
		<style>
			.container{max-width: 950px; margin: 50px auto;}
			.infobox p {margin-bottom: 0px; margin-top: 10px;}
			.list_expand li {display: none;}
			.module-header{padding: 20px; font-size: 1.5em;}
			div.module .list_expand li p{font-size: 1.3em;}
			div.module .list_expand li p.step{font-size: 1.5em; margin-top: 25px;}
			strong {font-style: italic}
			.images{width: 50%; float: right;}
			.steps{width: 50%; float: left;}
			.list_expand img{max-width: 90%;}
		</style>
	</head>
	
	<body>
        <div class="container">
            <div class="infobox">
				<p>IMPORTANT: Printing Missions will <strong>only work properly</strong> with the settings that are shown <strong>below.</strong></p>
				<p>Please note that this page requires JavaScript to be enabled.</p>
				<p>Currently supported browsers are: Chrome, Firefox, IE and Edge (Safari and Opera Coming Soon)</p>
			</div>
			
			<div class="module clearfix" style="clear:both;">
				<h3 class="module-header"><span class="icon"></span>Printing Instructions For <span id="browser-name"></span></h3>
				<div class="list_expand">
					<ul>
						<li id="Chrome">
							<div class="images">
								<img src="img/chrome.png" alt="chrome setup example"/>
							</div>
							<div class="steps">
								<p class="step" style="margin-top: 10px;">Step 1: Press the <strong>"+ More settings"</strong> button on the left hand side.</p>
								<p class="step">Step 2: Set the <strong>Layout</strong> to <strong>Portrait</strong></p>
								<p class="step">Step 3: Set <strong>Scale</strong> to <strong>90%</strong>.</p>
								<p>Please try decreasing this to <strong>85%</strong> if you are encountering issues as adjusting the margins is currently broken in Chrome 63+.</p>
								<!--<p class="step">Step 3: Set <strong>Margins</strong> to <strong>Custom</strong>.</p>-->
<!--								<p class="step">Step 4: Please enter the following values into the black boxes which will now be surrounding the page<br />
									<div style="margin-left: 30px; font-size: 1.4em;">
										Top Box: 0.3"<br />
										Left Box: 0.3"<br />
										Right Box: 0.0"<br />
										Bottom Box: 0.0"<br />
									</div>
								</p>-->
								<p class="step">Step 4: uncheck <strong>Headers and footers</strong></p>
								<p>Note: As of version 63 the browser will no longer save these preferences for later use.</p>
							</div>
						</li>
						<li id="Firefox">
							<div class="images">
								<span>Image #1 - Open Print Preview</span>
								<img src="img/firefox.png" alt="firefox setup example"/>
								<span>Image #2 - Orientation and Scale</span>
								<img src="img/firefox-1.png" alt="firefox setup example"/>
								<span>Image #3 - Margins and Header/Footer</span>
								<img src="img/firefox-2.png" alt="firefox setup example"/>
							</div>
							<div class="steps">
								<p class="step" style="margin-top: 10px;">
									Step 1: Please select <strong>"Print"</strong> from the Firefox menu (See image #1).
								</p>
								
								<p class="step">Step 2: Press the <strong>"Page Setup..."</strong> button in the upper right corner</p>
								
								<p class="step">Step 3: Set the <strong>Orientation</strong> to <strong>Portrait</strong></p>
								
								<p class="step">Step 4: Set <strong>Scale</strong> to <strong>90%</strong>.</p>
								<p>Please try decreasing this to <strong>85%</strong> if you are encountering issues.</p>
								
								<p class="step">Step 5: Move to the <strong>Margins & Header/Footer</strong> Tab</p>
								
								<p class="step">Step 6: Enter the following values for <strong>Margins (inches)</strong><br />
									<div style="margin-left: 30px; font-size: 1.4em;">
										Top: 0.3"<br />
										Left: 0.3"<br />
										Right: 0.0"<br />
										Bottom: 0.0"<br />
									</div>
								</p>

								<p class="step">Step 7: Set every box under <strong>Headers & Footers</strong> to <strong>--blank--</strong></p>
								
								<p>Note: The browser will save these preferences for later use.</p>
							</div>
						</li>
						<li id="IE">
							<div class="images">
								<img src="img/ie-1.png" alt="ie setup example"/>
								<img src="img/ie-2.png" alt="ie setup example"/>
							</div>
							<div class="steps">
								<p class="step" style="margin-top: 0px;">Please right click and select <strong>"Print preview"</strong> from the menu and <strong>"do not press the print button"</strong> on the page.</p>
								
								<p class="step">Step 1: Press the <strong>Gear icon</strong> in the upper right corner</p>
								
								<p class="step">Step 2: Set the <strong>Orientation</strong> to <strong>Portrait</strong> (Below Page size)</p>
								
								<p class="step">Step 3: Enter the following values for <strong>Margins (inches)</strong><br />
									<div style="margin-left: 30px; font-size: 1.4em;">
										Top: 0.3"<br />
										Left: 0.3"<br />
										Right: 0.0"<br />
										Bottom: 0.0"<br />
									</div>
								</p>

								<p class="step">Step 4: Set every box under <strong>Headers and Footers</strong> to <strong>-Blank-</strong></p>
								
								<p class="step">Step 5: Set <strong>Scale</strong> to <strong>90%</strong>. (Defaults to "Shrink To Fit")</p>
								<p>Please try decreasing this to <strong>85%</strong> if you are encountering issues.</p>
								
								<p>Note: The browser will save these preferences for later use.</p>
							</div>
						</li>
						<li id="Edge">
							<div class="images">
								<img src="img/edge.png" alt="edge setup example"/>
							</div>
							<div class="steps">
								<p class="step">Step 1: Press the <strong>Print Button</strong> on the top of the printing page</p>
								
								<p class="step">Step 2: Set the <strong>Orientation</strong> to <strong>Portrait</strong></p>
								
								<p class="step">Step 3: Set the <strong>Scale</strong> to <strong>75%</strong> (Below Page size)</p>
								<p>Please note that the system is designed for firefox where <strong>85-90%</strong> is an option. As such you may experiance large margins on the page</p>
								
								<!--<p class="step">Step 3: Enter the following values for <strong>Margins (inches)</strong><br />
									<div style="margin-left: 30px; font-size: 1.4em;">
										Top: 0.3"<br />
										Left: 0.3"<br />
										Right: 0.0"<br />
										Bottom: 0.0"<br />
									</div>
								</p>-->
								
								<p class="step">Step 4: Set <strong>Margins</strong> to <strong>Normal/Moderate</strong></p>
								
								<p class="step">Step 5: Set <strong>Headers and Footers</strong> to <strong>Off</strong></p>

							</div>
						</li>
						<li id="Opera">
							<div class="images">
								<img src="img/opera.png" alt="opera setup example"/>
							</div>
							<div class="steps">
								<p class="step">Opera Instructions Coming Soon</p>
							</div>
						</li>
						<li id="Safari">
							<div class="images">
								<img src="img/safari.png" alt="safari setup example"/>
							</div>
							<div class="steps">
								<p class="step">Safari Instructions Coming Soon</p>
							</div>
						</li>
						<li id="default">
							<p class="step">Please install a supported browser.</p>
						</li>
					</ul>
				</div>
			</div>
        </div>
		
	</body>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="/js/utils/browser_detect.js"></script>
	<script>
		$("#browser-name").text(browser_detect());
		if (browser_detect() === "N/A" || browser_detect() == "Blink") {
            $("li#default").css({"display": "inline-block"});
        } else {
			$("li#"+browser_detect()).css({"display": "inline-block"});
		}
	</script>
</html>
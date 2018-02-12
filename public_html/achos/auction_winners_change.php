<?
if (isset($_POST['changeValue'])) {
	$changeValue = $_POST['changeValue'];
	
	
?>

	<SCRIPT type="text/javascript">
		$(function () {
			$('#wrapper').css("opacity", 0.3);
			$('#select_overlay').css("top", Math.max(0, (($('body').height()-430)/2)) + 'px');
				
			$(document).bind("keyup.select_overlay", function (e) {
				if (e.keyCode==27) {
					$('#close_pop a').click();
					$(document).unbind("keyup.select_overlay");
				}
			});			
		});
		
		var aFilter = new Array()
		aFilter = {'auction':'11 Shvat','prize':'All','name':'All','grade':'All','base':'All'};
		var sLink;
	
		function updateFilter(filter,val) {
			// ********** CLOSE auction_winners_change.php ********** //
			document.getElementById('select_overlay').style.display = 'none';
			if (document.getElementById('focus')) 
				document.getElementById('focus').focus(); 
			// ********** CLOSE auction_winners_change.php ********** //			
		
			getWinners("");
			
			//var toLoad = "auction_winners.php?" + filter + "=" + val;
			
			//alert("1) val:" + val);
			
			//aFilter[filter]=val;
			//alert("2) updateFilter");
			
			//var key;
			//alert("3) updateFilter");
			
			//sLink = '';			
			//alert("4) updateFilter");
			
			//for (key in aFilter) {
			//	sLink += '&' + escape(key) + '=' + escape(aFilter[key]);
			//}
			
			//alert("5) sLink:" + sLink);
			
			//event.preventDefault();
			//var num = $(this).eq();
			//var toLoad = "auction_winners.php?auction_id=15" + sLink;
			
			//alert("6) updateFilter");
			
			//alert("toLoad:" + toLoad);
			
			//$('.winner_results').fadeOut('fast',loadContent);
			//$('.loader').remove();
			//$('.two_column').append('<div class="loader">LOADING...</div>');
			//$('.loader').fadeIn('normal');

			// ********** RESET THE OPACITY T NORMAL FOR auction_winners.php ********** //
			//$('#wrapper').fadeTo('normal', 1); 			
			
			//return false;			
			
			////////////window.location.hash = $(this).attr('href').substr(0,$(this).attr('href').length-5);
			//function loadContent() {
			//	alert("1) loadContent");
				
			//	$.get(toLoad,'',
			//			function(data){
			//				$('.winner_results').html(data);
			//				showNewWinnerContent()
			//			});
			//}
			
			//alert("2) updateFilter");
			
			//function showNewWinnerContent() {
			//	alert("1) showNewWinnerContent");
				
				////////////$('.winner_display').jScrollPane();
				
			//	$("table").tablesorter({
			//			headers: { 3: {sorter:'name'}}}).tablesorterPager({container: $("#pager"),size:8,positionFixed:false,seperator:' of ',textPage:'Page ',textShowing:'Showing '
			//	}); 
				
			//	alert("2) showNewWinnerContent");
				
			//	$("table").bind("sortStart",function() { 
			//		$("table").hide();
			//	}).bind("sortEnd",function() { 
			//			$("table").fadeIn('normal'); 
			//		}); 

			//		$(".sort_links a").click(function() { 
			//			var sorting = [[$(".sort_links a").index(this),0]];
			//			$("table").trigger("sorton",[sorting]); 
			//			return false; 
			//		}); 
			//		$('.winner_results').fadeIn('normal',hideLoader());
			//		for (key in aFilter) {
			//			$('.select .option#filter_' + escape(key)).text(aFilter[key]);
			//		}
			//		
			//}
			
			//function hideLoader() {
			//	$('.loader').fadeOut('fast');
			//}			
		}
		
		//function closeOverlay() {
		//	alert("closeOverlay");
			
		//	$(".select a").each(function() {
		//		if ($(this).select_overlay()) {
		//			$(this).select_overlay().close();
		//		}
		//	});
		//}
		
	</SCRIPT>
	
<?php
	switch ($changeValue) {
	
		case "auction": 
?>		
			<div id="select_overlay" style="position: fixed; z-index: 100; width: 100%;">
				<A HREF="#" onClick="$('#wrapper').fadeTo('normal', 1); document.getElementById('select_overlay').style.display = 'none'; if(document.getElementById('focus')) document.getElementById('focus').focus(); return false;">Close</A>
				<div class="pane_title">
					Choose an Auction:
				</div>
				<div class="button_small">
					<div><a onClick="$('#wrapper').fadeTo('normal', 1); updateFilter('auction_id','15');">15  ז אדר</a></div>
					<div><a onClick="$('#wrapper').fadeTo('normal', 1); updateFilter('auction_id','16');">10 Shvat</a></div>
					<div><a onClick="$('#wrapper').fadeTo('normal', 1); updateFilter('auction_id','17');">12 Shvat</a></div>
					<div><a onClick="$('#wrapper').fadeTo('normal', 1); updateFilter('auction_id','18');">21 Shvat</a></div>
				</div> <!-- button_small -->		
			</div> <!-- select_overlay -->
<?	
	} // switch ($output) {
	
?>

<?
} // if (isset($_POST['change'])) {
?>


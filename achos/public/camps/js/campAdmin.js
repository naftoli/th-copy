$(function() {

  	$(".list_first").tabs(".list_first > ul", {tabs: '.list_parent', effect: 'slide', history: true});
  	
  	$("#nav .list_second a").click(function(event) {
  			event.preventDefault();
  			slideForward(this);
	  });
	
	  function showLoader() {
		  $('#content .col_title_bg').append('<span class="loader">LOADING...</span>');
	  }
	
	  function hideLoader() {
		  $('.loader').fadeOut('fast',function() {$(this).remove()});
	  }
	
  	function slideForward(id) {
  		currentTitle = $('.slider:last .col_title span').html();
  
  		var toLoad = $(id).attr('href');
  		showLoader();
  		$('.loader').fadeIn('normal',loadContent());
  		
      function loadContent() {
  			$.get(toLoad,'',
  				function(data){
  					$('.slider_container').append(data);
  					$('.slider_container .slider:last .col_title a').html(currentTitle);
  					showNewSlide();
                        });
  		}
  		
  		function showNewSlide() {
  			hideLoader();
  			initialize();
  			slide_width = 773;
  			$(".slider_container").animate({'margin-left':parseInt($(".slider_container").css('margin-left')) - slide_width + 'px'},500, hideLoader());
  		}
  	}
  	
  	
	function initPageContent() {
      
      /*
		var action = "edit_group";
		var params = ["85","Girls 2"];
      
      
		$.getJSON("../../application/php/appInterface.php?action=" + action + "&params=" + params, function(data) {      

		});
		*/
	  
    }
  	
  	function initialize() {
  	
  		$(".slider:last a.dismiss").click(function(event) {
  			event.preventDefault();
  			$(this).parent().css({backgroundColor: "#ff0000"}).fadeOut("slow");
  		});
  		
  		$(".slider:last .lists a, .slider:last .wizard_nav a").click(function(event) {
  			event.preventDefault();
  			slideForward(this);
  		});
  		
  		$(".slider:last .slider_back").click(function(event) {
  			
  			slide_width = 773;
  			$(".slider_container").animate({'margin-left':parseInt($(".slider_container").css('margin-left')) + slide_width + 'px'},500);
  			$(this).parent().parent().fadeOut(function() {$(this).remove()});
  		
      });
  		
      $(".list_edit a").overlay({top: '20%', target: '#overlay', api:true, closeOnClick: false, close:'.close', mask: { color: '#fff', loadSpeed: 200, opacity: 0.5 },
  			onBeforeLoad: function() {
  				var wrap = this.getOverlay().find(".content");
  				var self = this;
  				showLoader();
  				wrap.load(this.getTrigger().attr("href"),function() {
  					hideLoader();
  					$('.close', this).click(function(){self.close()});
  				});
  			}
  		});
  		
      $('input[type=checkbox]').checkbox();
  	}
  	
  	initPageContent();
  	
  	initialize();
});
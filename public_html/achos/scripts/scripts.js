	
	function showLoader() {
		$('#content .col_title_bg').append('<span class="loader">LOADING...</span>');
	}
	function hideLoader() {
		$('.loader').fadeOut('fast',function() {$(this).remove()});
	}
	
	function slideReplace(id) {
		var toLoad = $(id).attr('href');
		showLoader();
		$('.loader').fadeIn('normal',loadContent());
		function loadContent() {
			$.get(toLoad,'',
				function(data){
					$('.slider_container').fadeOut('fast',function(){
						$(this).empty().css({'margin-left':0}).append(data).fadeIn('fast',function(){
							$('.slider_container').data('url',toLoad); //Stores the url to the latest page to use in slideRefresh()
							initialize();
							hideLoader();
							correctHeight();						
						}).parents('#content').addClass('camp_school');
					});
				});
		}
	}
	function slideReplaceLast(id) {
		var toLoad = $(id).attr('href');
		var currentBack = $('.slider:last .col_title a.slider_back').html();
		showLoader();
		$('.loader').fadeIn('normal',loadContent());
		function loadContent() {
			$.get(toLoad,'',
				function(data){
					$('.slider_container').fadeOut('fast',function(){
						$('.slider:last').remove();
						$(this).append(data);
						$('.slider:last .col_title').append('<a class="slider_back"></a>');
						$('.slider:last .col_title a').html(currentBack);
						$(this).fadeIn('fast',function(){
							$('.slider_container').data('url',toLoad); //Stores the url to the latest page to use in slideRefresh()
							initialize();
							hideLoader();
						});
					});						
				});
		}
	}
	function slideRefresh() {
		var toLoad = $('.slider_container').data('url');
		var currentBack = $('.slider:last .col_title a.slider_back').html();
		showLoader();
		$('.loader').fadeIn('normal',loadContent());
		function loadContent() {
			$.get(toLoad,'',
				function(data){
					$('.slider_container').fadeOut('fast',function(){
						$('.slider:last').remove();
						$(this).append(data)
						$('.slider:last .col_title').append('<a class="slider_back"></a>');
						$('.slider:last .col_title a').html(currentBack);
						$(this).fadeIn('fast',function(){
							$('.slider_container').data('url',toLoad);
							initialize();
							hideLoader();
						});
					});
				});
		}
	}
	function slideForward(id) {
		currentTitle = $('.slider:last .col_title span').html();
		//currentTitle = $(this).parents('.slider').children('.col_title').html();

		var toLoad = $(id).attr('href');
		showLoader();
		$('.loader').fadeIn('normal',loadContent());
		//addressBar = $(this).attr('href');
		function loadContent() {
			$.get(toLoad,'',
				function(data){
					//var filtered_data = $(data).find('.slider');
					data = data.replace(/\"includes\//g,'"camps/includes/');
					//alert(data);
					$('.slider_container').append(data);
					$('.slider_container .slider:last .col_title').append('<a class="slider_back"></a>');
					$('.slider_container .slider:last .col_title a').html(currentTitle);
					$('.slider_container').data('url',toLoad); //Stores the url to the latest page to use in slideRefresh()
					showNewSlide();
				});
		}
		function showNewSlide() {
			hideLoader();
			initialize();
			slide_width = 773;
			$(".slider_container").animate({'margin-left':parseInt($(".slider_container").css('margin-left')) - slide_width + 'px'},500, hideLoader());
			//window.location.hash = $(this).attr('href').substr(0,$(this).attr('href').length-5);
			//window.location.hash = addressBar;
		}
	}
	function initialize() {
		$('.slider:last a[href^="content"]').each(function(){
			$(this).attr('href', 'camps/' + $(this).attr('href')); //to add camps to the href links of all camp pages
		});
		$('.slider:last .stats').parents('.module').hide();
		/*$('#content').html().replace('"includes/','"camps/includes/');*/
		$(".slider:last a.dismiss").click(function(event) {
			event.preventDefault();
			$(this).parent().css({backgroundColor: "#ff0000"}).fadeOut("slow");
		});
		$(".slider:last .slider_back").click(function(event) {
			//event.preventDefault();
			slide_width = 773;
			$(".slider_container").animate({'margin-left':parseInt($(".slider_container").css('margin-left')) + slide_width + 'px'},500);
			$(this).parent().parent().fadeOut(function() {$(this).remove()});
		});
		$(".slider:last .lists a, .slider:last .wizard_nav a").live('click',function(event) {
			event.preventDefault();
			slideForward(this);
		});
		$(".list_edit a, a.overlay").overlay({top: '10%', target: '#overlay', api:true, mask: { color: '#000', loadSpeed: 200, opacity: 0.4 },
			onBeforeLoad: function() {
				var wrap = this.getOverlay().find(".content");
				var self = this;
				showLoader();
				wrap.load(this.getTrigger().attr("href"),function() {
						hideLoader();
						//$('.close', this).click(function(){self.close()});
					});
			},
			onBeforeClose: function() {
				$('.form_error').remove();
			},
			onClose: function() {
				this.getOverlay().find(".content").empty();
			}
		});
		$('.slider:last select.select').sSelect();

/*		$('.slider:last .editable').editable('http://www.example.com/save.php',{
			 indicator : '<img src="images/ajax-loader-sm.gif"/>',
			 submit:'<img src="images/bullet_disk.png"/>',
			 onblur:'ignore',
			 width:'120',
			 height:'14'
		});
		$('.editableText .content').editableText();
		$('.editableText .content').change(function(){
			var newValue = $(this).html();
		});*/
		$('.makeTextEditable').toggle(function(e){
			e.preventDefault();
			$(this).parents('.editableText').toggleClass('editableActive');
			var data = $(this).text();
			$(this).attr('name',data);
			$(this).children().text('Done Editing');
		},function(e){
			e.preventDefault();
			$(this).parents('.editableText').toggleClass('editableActive');
			var text = $(this).attr('name');
			$(this).children().text(text);
		});
 
	}
	
	
	$(function() {
		/*$('.side_main > ul > li h1').click(function() {
			$(this).next().toggle('fast');
			return false;
		}).next().hide().first().show();*/

		/*$(".list_first").tabs(".list_first > ul", {tabs: '.list_parent', effect: 'slide'});*/
		
		$("#nav .list_first .slide a:not([href*='#'])").click(function(event) {
				event.preventDefault();
				slideReplace(this);
		});
		
		$(".slider a.link, .slider .wizard_nav a").live('click',function(event) {
			event.preventDefault();
			slideForward(this);
		});

/*		var overlayObject = $('#overlayElementNotTrigger').overlay({api:true, top: '20%', target: '#overlay', closeOnClick: false, mask: { color: '#000', loadSpeed: 200, opacity: 0.4 },
			onBeforeLoad: function() {
				var wrap = this.getOverlay().find(".content");
				var self = this;
				showLoader();
				//wrap.load(this.getTrigger().attr("href"),function() {
				wrap.load(overlayUrl,function() {
					hideLoader();
					//$('.close', this).click(function(){self.close()});
				});
			}
		}); 
		$('.list_edit a, a.overlay').live('click', function(e){ 
			overlayUrl = $(this).attr("href");
			e.preventDefault(); 
			overlayObject.load(); 
		});*/
		/*$('.edit a, a.edit').live('click',function(){$(this).parents('li').find('.editable').click()});*/
		
		initialize();

	});

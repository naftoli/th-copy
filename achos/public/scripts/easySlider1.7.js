/*
 * 	Easy Slider 1.8 - jQuery plugin
 *	written by Alen Grakalic (modified by Kyle Florence - kyle.florence@gmail.com)
 *	http://cssglobe.com/post/4004/easy-slider-15-the-easiest-jquery-plugin-for-sliding
 *
 *	Copyright (c) 2009 Alen Grakalic (http://cssglobe.com)
 *	Dual licensed under the MIT (MIT-LICENSE.txt)
 *	and GPL (GPL-LICENSE.txt) licenses.
 *
 *	Built for jQuery library
 *	http://jquery.com
 *
 */
 
/*
 *	markup example for $("#slider").easySlider();
 *	
 * 	<div id="slider">
 *		<ul>
 *			<li><img src="images/01.jpg" alt="" /></li>
 *			<li><img src="images/02.jpg" alt="" /></li>
 *			<li><img src="images/03.jpg" alt="" /></li>
 *			<li><img src="images/04.jpg" alt="" /></li>
 *			<li><img src="images/05.jpg" alt="" /></li>
 *		</ul>
 *	</div>
 *
 
 
                                 	<div class="slider_buttons">
                                    	<div class="slider_button"><a class="link-1" href="javascript:void(0);" rel="1">Week 5</a></div>
                                    	<div class="slider_button"><a class="link-2" href="javascript:void(0);" rel="2">Week 6</a></div>
                                    	<div class="slider_button"><a class="link-3" href="javascript:void(0);" rel="3">Week 7</a></div>
                                    	<div class="slider_button active"><a class="link-4" href="javascript:void(0);" rel="4">Week 8</a></div>
                                    </div>

 
 
 */

(function($)
{
    $.fn.easySlider = function(options)
    {
		// default configuration properties
		var defaults = {			
			prevId: 		'prevBtn',
			prevText: 		'Previous',
			nextId: 		'nextBtn',	
			nextText: 		'Next',
			controlsShow:	true,
			controlsBefore:	'',
			controlsAfter:	'',	
			controlsFade:	true,
            insertAfter:    true,
			firstId: 		'firstBtn',
			firstText: 		'First',
			firstShow:		false,
			lastId: 		'lastBtn',	
			lastText: 		'Last',
			lastShow:		false,				
			vertical:		false,
			speed: 			500,
            ease:           'swing',
			auto:			false,
			pause:			2000,
			continuous:		false, 
            prevNext:       true,
			numeric: 		false,
			numericId: 		'controls',
			animate:		function(number,animclick) {animate(number,animclick);}
		}; 
		
		var options = $.extend(defaults, options);  
				
        this.each(function()
        {
			var obj = $(this); 				

            // Fix for nested list items
            var ul = obj.children("ul");
            var li = ul.children("li");

            var s = li.length;
            var w = obj.width();
            var h = obj.height();

            var t = 0;
            var ts = s-1;
			var clickable = true;

            // Set obj overflow to hidden
			obj.css("overflow","hidden");
			
            // Set width/height of list items based on width/height of obj
            li.each(function() {
               if(options.vertical) $(this).height(h);
                else $(this).width(w);
            });

            // Float items to the left
            if(!options.vertical) li.css('float', 'left');

            // Set width/height of ul
            if(options.vertical) ul.height(s*h);
            else ul.width(s*w);

            // Clone elements for continuous scrolling
            if(options.continuous)
            {
                if(options.vertical)
                {
                    ul.prepend(li.filter(":last-child").clone().css("margin-top","-"+ h +"px"));
                    ul.append(li.filter(":nth-child(2)").clone());
                    ul.height((s+1)*h);
                } else {
                    ul.prepend(li.filter(":last-child").clone().css("margin-left","-"+ w +"px"));
                    ul.append(li.filter(":nth-child(2)").clone());
                    ul.width((s+1)*w);
                }
			};				
			
            if(ts<1) options.controlsShow = false;
			if(options.controlsShow)
            {
				var html = options.controlsBefore;				
				if(options.numeric){
					html += '<ol id="'+ options.numericId +'"></ol>';
                }
                if(options.firstShow) {
                    html += '<span id="'+ options.firstId +'"><a href="javascript:void(0);">'+ options.firstText +'</a></span>';
                }
                if(options.prevNext){
                    html += '<span id="'+ options.prevId +'"><a href="javascript:void(0);">'+ options.prevText +'</a></span>';
                    html += '<span id="'+ options.nextId +'"><a href="javascript:void(0);">'+ options.nextText +'</a></span>';
                }
                if(options.lastShow) {
                    html += '<span id="'+ options.lastId +'"><a href="javascript:void(0);">'+ options.lastText +'</a></span>';
                }
                html += options.controlsAfter;
				
                if (options.insertAfter) $(obj).after(html);
                else $(obj).before(html);
			};
			
            if(options.numeric)
            {
                for(var i=0;i<s;i++)
                {
					$(document.createElement("li"))
						.attr('id',options.numericId + (i+1))
                        .html('<a rel="'+ i +'" href="javascript:void(0);"><span>'+ (i+1) +'</span></a>')
						.appendTo($("#"+ options.numericId))
						.click(function(){							
							animate($("a",$(this)).attr('rel'),true);
                            return false;
						}); 
					$('.link-'+(i+1))
						.click(function(){	
							animate(($(this).attr('rel')-1),true);
                            return false;
						}); 
/*					$('#link-'+i)
						.load(function(){	
							animate($(this).attr('rel'),true);
                            return false;
						}); 
*/						
				};							
            }

            if(options.prevNext)
            {
				$("a","#"+options.nextId).click(function(){		
                    animate("next",true); return false;
				});
				$("a","#"+options.prevId).click(function(){		
                    animate("prev",true); return false;
				});	
				$("a","#"+options.firstId).click(function(){		
                    animate("first",true); return false;
				});				
				$("a","#"+options.lastId).click(function(){		
                    animate("last",true); return false;
				});				
			};
			
            function setCurrent(i)
            {
				i = parseInt(i)+1;
				$("li", "#" + options.numericId).removeClass("current");
				$("li#" + options.numericId + i).addClass("current");
			};
			
            function adjust()
            {
				if(t>ts) t=0;		
				if(t<0) t=ts;	
/*				if(!options.vertical) {
                    ul.css("margin-left",(t*w*-1));
				} else {
                    ul.css("margin-top",(t*h*-1));
				}
*/				clickable = true;
				if(options.numeric) setCurrent(t);
			};
			
            function animate(dir,clicked)
            {
                if (clickable)
                {
					clickable = false;
					var ot = t;				
                    switch(dir)
                    {
						case "next":
							t = (ot>=ts) ? (options.continuous ? t+1 : ts) : t+1;						
							break; 
						case "prev":
							t = (t<=0) ? (options.continuous ? t-1 : 0) : t-1;
							break; 
						case "first":
							t = 0;
							break; 
						case "last":
							t = ts;
							break; 
						default:
                            t = parseInt(dir);
							break; 
					};	

					var diff = Math.abs(ot-t);
					var speed = options.speed;
					if (diff>1) speed = diff*options.speed/3;
					if(!options.vertical) {
						p = (t*w*-1);
                        ul.animate(
							{ marginLeft: p }, 
                            {
                                queue:false,
                                duration:speed,
                                easing:options.ease
                            }
						);				
						adjust();
					} else {
						p = (t*h*-1);
                        ul.animate(
							{ marginTop: p }, 
                            {
                                queue:false,
                                duration:speed,
                                easing:options.ease
                            }
						);					
						adjust();
					};
					
                    if(!options.continuous && options.controlsFade)
                    {
						if(t==0){
                            $("a","#"+options.prevId).fadeOut('slow');
                            $("a","#"+options.firstId).fadeOut('slow');
                            $("a","#"+options.nextId).fadeIn('slow');
                            $("a","#"+options.lastId).fadeIn('slow');
                        } else if(t==ts){
                            $("a","#"+options.nextId).fadeOut('slow');
                            $("a","#"+options.lastId).fadeOut('slow');
                            $("a","#"+options.prevId).fadeIn('slow');
                            $("a","#"+options.firstId).fadeIn('slow');
						} else {
                            $("a","#"+options.prevId).fadeIn('slow');
                            $("a","#"+options.firstId).fadeIn('slow');
                            $("a","#"+options.nextId).fadeIn('slow');
                            $("a","#"+options.lastId).fadeIn('slow');
						};					
					};				
					
					if(clicked) clearTimeout(timeout);
					if(options.auto && dir=="next" && !clicked){;
						timeout = setTimeout(function(){
							animate("next",false);

						},diff*options.speed+options.pause);
					};
			
				};
			};
			// init
			var timeout;
			if(options.auto){;
				timeout = setTimeout(function(){
					animate("next",false);
				},options.pause);
			};		
			
			if(options.numeric) setCurrent(0);
		
			if(!options.continuous && options.controlsFade){					
				$("a","#"+options.prevId).hide();
				$("a","#"+options.firstId).hide();				
			};				
			
		});
	  
	};
})(jQuery);
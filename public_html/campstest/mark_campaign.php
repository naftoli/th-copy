<?php

$admin_auth = array('camp');
require('../header.php'); 

$camp_id = $_GET['camp_id'];
$campaign_id = $_GET['campaign_id'];

?>
        <script src="scripts/jquery.jeditable.min.js"></script>
        <script>
        
        $(function() {

                // We override the animation for all of these color styles
                jQuery.each(['backgroundColor', 'borderBottomColor', 'borderLeftColor', 'borderRightColor', 'borderTopColor', 'color', 'outlineColor'], function(i,attr){
                    jQuery.fx.step[attr] = function(fx){
                    if (fx.state == 0 || fx.start.constructor != Array || fx.end.constructor != Array) {
    				    fx.start = getColor( fx.elem, attr );
    				    fx.end = getRGB( fx.end );
    				    if (fx.end == "transparent" || fx.end == "") fx.end = getColor(fx.elem.parentNode, "backgroundColor");
                    }
    
                    fx.elem.style[attr] = "rgb(" + [
    				
                    Math.max(Math.min( parseInt((fx.pos * (fx.end[0] - fx.start[0])) + fx.start[0]), 255), 0),
    				Math.max(Math.min( parseInt((fx.pos * (fx.end[1] - fx.start[1])) + fx.start[1]), 255), 0),
    				Math.max(Math.min( parseInt((fx.pos * (fx.end[2] - fx.start[2])) + fx.start[2]), 255), 0)
    
                    ].join(",") + ")";
                    
                    }
                });
                
                // Color Conversion functions from highlightFade
                // By Blair Mitchelmore
                // http://jquery.offput.ca/highlightFade/

                // Parse strings looking for color tuples [255,255,255]
                function getRGB(color) {
                    var result;

                    // Check if we're already dealing with an array of colors
                    if ( color && color.constructor == Array && color.length == 3 )
                        return color;
    
                    // Look for rgb(num,num,num)
                    if (result = /rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(color))
                        return [parseInt(result[1]), parseInt(result[2]), parseInt(result[3])];
    
                    // Look for rgb(num%,num%,num%)
                    if (result = /rgb\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*\)/.exec(color))
                        return [parseFloat(result[1])*2.55, parseFloat(result[2])*2.55, parseFloat(result[3])*2.55];
    
                    // Look for #a0b1c2
                    if (result = /#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(color))
                        return [parseInt(result[1],16), parseInt(result[2],16), parseInt(result[3],16)];
    
                    // Look for #fff
                    if (result = /#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(color))
                        return [parseInt(result[1]+result[1],16), parseInt(result[2]+result[2],16), parseInt(result[3]+result[3],16)];
    
                    // Otherwise, we're most likely dealing with a named color
                    return colors[jQuery.trim(color).toLowerCase()];
                }
	
                function getColor(elem, attr) {
                    var color;

                    do {
                        color = jQuery.curCSS(elem, attr);

                        // Keep going until we find an element that has color, or we hit the body
                        if ( color != '' && color != 'transparent' || jQuery.nodeName(elem, "body") )
                            break; 

                        attr = "backgroundColor";
                    } while ( elem = elem.parentNode );

                    return getRGB(color);
                };
	
                // Some named colors to work with
                // From Interface by Stefan Petre
                // http://interface.eyecon.ro/
                var colors = {
            		aqua:[0,255,255],
            		azure:[240,255,255],
            		beige:[245,245,220],
            		black:[0,0,0],
            		blue:[0,0,255],
            		brown:[165,42,42],
            		cyan:[0,255,255],
            		darkblue:[0,0,139],
            		darkcyan:[0,139,139],
            		darkgrey:[169,169,169],
            		darkgreen:[0,100,0],
            		darkkhaki:[189,183,107],
            		darkmagenta:[139,0,139],
            		darkolivegreen:[85,107,47],
            		darkorange:[255,140,0],
            		darkorchid:[153,50,204],
            		darkred:[139,0,0],
            		darksalmon:[233,150,122],
            		darkviolet:[148,0,211],
            		fuchsia:[255,0,255],
            		gold:[255,215,0],
            		green:[0,128,0],
            		indigo:[75,0,130],
            		khaki:[240,230,140],
            		lightblue:[173,216,230],
            		lightcyan:[224,255,255],
            		lightgreen:[144,238,144],
            		lightgrey:[211,211,211],
            		lightpink:[255,182,193],
            		lightyellow:[255,255,224],
            		lime:[0,255,0],
            		magenta:[255,0,255],
            		maroon:[128,0,0],
            		navy:[0,0,128],
            		olive:[128,128,0],
            		orange:[255,165,0],
            		pink:[255,192,203],
            		purple:[128,0,128],
            		violet:[128,0,128],
            		red:[255,0,0],
            		silver:[192,192,192],
            		white:[255,255,255],
            		yellow:[255,255,0]
            	};
                
        }
        </script>

        <div class="slider">
				<div class="col_title"><span>Marking</span> - Sunday, Ches Tammuz<a class="slider_back">Dashboard</a></div>
				<div class="col_content">
                    <div class="module" id="module-info">
                        <div class="module_content">
                        	<div class="marking">
                            	<div class="col names">
                                    <div class="mission_name">
                                        <div class="cell">Bunk Alef</div>
                                    </div>
                                    <div class="task_names">
                                        <div class="cell"></div>
                                    </div>
                                	<div class="cell">Shmuli Alevsky</div>
                                	<div class="cell">Yanky Stock</div>
                                	<div class="cell">Moshe Boruch</div>
                                	<div class="cell">Boruch Shmalberg</div>
                                	<div class="cell">Yitzi Yitzyovitcz</div>
                                	<div class="cell">Mark Wahlberg</div>
                                	<div class="cell">Eli Kestelbaum</div>
                                	<div class="cell">Avi Hurowitz</div>
                                	<div class="cell">Menachem Mendel Rosenblatt</div>
                                	<div class="cell">Dovy Scheinberger</div>
                                	<div class="cell">Huda Stein</div>
                                	<div class="cell">Zalman Mutnitzovitchz</div>
                                </div>
                                <div class="mission_window">
                                	<div style="margin-left: 0px;" class="mission_container">
                                        <div class="missions">
                                            <div class="col mission">
                                                <div class="row mission_name">
                                                    <div class="cell">Mission Name</div>
                                                </div>
                                                <div class="row task_names">
                                                    <div class="cell"><span>Come on time and standing in a straight line</span></div>
                                                    <div class="cell"><span>Look inside</span></div>
                                                    <div class="cell"><span>Come on time</span></div>
                                                    <div class="cell"><span>Look inside</span></div>
                                                    <div class="cell"><span>Come on time</span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                            </div>
                                            <div class="col mission">
                                                <div class="row mission_name">
                                                    <div class="cell">Mission Name</div>
                                                </div>
                                                <div class="row task_names">
                                                    <div class="cell"><span>Come on time and standing in a straight line</span></div>
                                                    <div class="cell"><span>Look inside</span></div>
                                                    <div class="cell"><span>Come on time</span></div>
                                                    <div class="cell"><span>Look inside</span></div>
                                                    <div class="cell"><span>Come on time</span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="missions">
                                            <div class="col mission">
                                                <div class="row mission_name">
                                                    <div class="cell">Mission Name</div>
                                                </div>
                                                <div class="row task_names">
                                                    <div class="cell"><span>Come on time and standing in a straight line</span></div>
                                                    <div class="cell"><span>Look inside</span></div>
                                                    <div class="cell"><span>Come on time</span></div>
                                                    <div class="cell"><span>Look inside</span></div>
                                                    <div class="cell"><span>Come on time</span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                            </div>
                                            <div class="col mission">
                                                <div class="row mission_name">
                                                    <div class="cell">Mission Name</div>
                                                </div>
                                                <div class="row task_names">
                                                    <div class="cell"><span>Come on time and standing in a straight line</span></div>
                                                    <div class="cell"><span>Look inside</span></div>
                                                    <div class="cell"><span>Come on time</span></div>
                                                    <div class="cell"><span>Look inside</span></div>
                                                    <div class="cell"><span>Come on time</span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="bot_nav">
                    	<span class="bunk_buttons">
                            <a href="#" class="button prev"><span class="icon"></span>Previous Bunk</a>
                            <a href="#" class="button next">Next Bunk<span class="icon"></span></a>
                        </span>
                        <span class="mission_buttons">
                            <a href="#" class="button prev"><span class="icon"></span>Previous Missions</a>
                            <a href="#" class="button next">Next Missions<span class="icon"></span></a>
                        </span>
                    </div>
				</div>
			</div>

			 
			<div class="slider">
				<div class="col_title"><span>Marking</span> - Sunday, Ches Tammuz<a class="slider_back">Marking</a></div>
				<div class="col_content">
                    <div class="module" id="module-info">
                        <div class="module_content">
                        	<div class="marking">
                            	<div class="col names">
                                    <div class="mission_name">
                                        <div class="cell">Bunk Alef</div>
                                    </div>
                                    <div class="task_names">
                                        <div class="cell"></div>
                                    </div>
                                	<div class="cell">Shmuli Alevsky</div>
                                	<div class="cell">Yanky Stock</div>
                                	<div class="cell">Moshe Boruch</div>
                                	<div class="cell">Boruch Shmalberg</div>
                                	<div class="cell">Yitzi Yitzyovitcz</div>
                                	<div class="cell">Mark Wahlberg</div>
                                	<div class="cell">Eli Kestelbaum</div>
                                	<div class="cell">Avi Hurowitz</div>
                                	<div class="cell">Menachem Mendel Rosenblatt</div>
                                	<div class="cell">Dovy Scheinberger</div>
                                	<div class="cell">Huda Stein</div>
                                	<div class="cell">Zalman Mutnitzovitchz</div>
                                </div>
                                <div class="mission_window">
                                	<div class="mission_container">
                                        <div class="missions">
                                            <div class="col mission">
                                                <div class="row mission_name">
                                                    <div class="cell">Mission Name</div>
                                                </div>
                                                <div class="row task_names">
                                                    <div class="cell"><span>Come on time and standing in a straight line</span></div>
                                                    <div class="cell"><span>Look inside</span></div>
                                                    <div class="cell"><span>Come on time</span></div>
                                                    <div class="cell"><span>Look inside</span></div>
                                                    <div class="cell"><span>Come on time</span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                            </div>
                                            <div class="col mission">
                                                <div class="row mission_name">
                                                    <div class="cell">Mission Name</div>
                                                </div>
                                                <div class="row task_names">
                                                    <div class="cell"><span>Come on time and standing in a straight line</span></div>
                                                    <div class="cell"><span>Look inside</span></div>
                                                    <div class="cell"><span>Come on time</span></div>
                                                    <div class="cell"><span>Look inside</span></div>
                                                    <div class="cell"><span>Come on time</span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="missions">
                                            <div class="col mission">
                                                <div class="row mission_name">
                                                    <div class="cell">Mission Name</div>
                                                </div>
                                                <div class="row task_names">
                                                    <div class="cell"><span>Come on time and standing in a straight line</span></div>
                                                    <div class="cell"><span>Look inside</span></div>
                                                    <div class="cell"><span>Come on time</span></div>
                                                    <div class="cell"><span>Look inside</span></div>
                                                    <div class="cell"><span>Come on time</span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                            </div>
                                            <div class="col mission">
                                                <div class="row mission_name">
                                                    <div class="cell">Mission Name</div>
                                                </div>
                                                <div class="row task_names">
                                                    <div class="cell"><span>Come on time and standing in a straight line</span></div>
                                                    <div class="cell"><span>Look inside</span></div>
                                                    <div class="cell"><span>Come on time</span></div>
                                                    <div class="cell"><span>Look inside</span></div>
                                                    <div class="cell"><span>Come on time</span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                                <div class="row">
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                    <div class="cell checkbox checked"><span><input id="1-1" checked="checked" type="checkbox"></span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="bot_nav">
                    	<span class="bunk_buttons">
                            <a href="#" class="button prev"><span class="icon"></span>Previous Bunk</a>
                            <a href="#" class="button next">Next Bunk<span class="icon"></span></a>
                        </span>
                        <span class="mission_buttons">
                            <a href="#" class="button prev"><span class="icon"></span>Previous Missions</a>
                            <a href="#" class="button next">Next Missions<span class="icon"></span></a>
                        </span>
                    </div>
				</div>
			</div>
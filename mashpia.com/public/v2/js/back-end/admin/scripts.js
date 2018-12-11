$(document).ready(function () {
	$(this).keydown(function () {
		$('.slider:last .keydown_auto_focus:first').focus();
	});
});
if (!Object.keys) {
    Object.keys = function (obj) {
        var keys = [],
            k;
        for (k in obj) {
            if (Object.prototype.hasOwnProperty.call(obj, k)) {
                keys.push(k);
            }
        }
        return keys;
    };
}

/*
 * reprot({
 *	'code' : 'CR-AE101-lfkmef',
 *	'location' : '/ereport/ajaxerror',
 *	'message' : '...',
 *	'other' : '...'
 * });
 */
function report(params) {
	$.ajax({
		type : "POST",
		cache : false,
		url : '/ereport/ajaxerror',
		dataType : "text",
		data: {
			'code' : params.code ? params.code : '',
			'location' : params.location ? params.location : '',
			'message' : params.message ? params.message : '',
			'other' : params.other ? params.other : '',
			'sequence' : new Date().getTime()
		},
		success : function(strResponse) {

		}
	});
}

function date (format, timestamp) {
    var that = this,
      jsdate,
      f,
      formatChr = /\\?([a-z])/gi,
      formatChrCb,
      // Keep this here (works, but for code commented-out
      // below for file size reasons)
      //, tal= [],
      _pad = function (n, c) {
        n = n.toString();
        return n.length < c ? _pad('0' + n, c, '0') : n;
      },
      txt_words = ["Sun", "Mon", "Tues", "Wednes", "Thurs", "Fri", "Satur", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
  formatChrCb = function (t, s) {
    return f[t] ? f[t]() : s;
  };
  f = {
    // Day
    d: function () { // Day of month w/leading 0; 01..31
      return _pad(f.j(), 2);
    },
    D: function () { // Shorthand day name; Mon...Sun
      return f.l().slice(0, 3);
    },
    j: function () { // Day of month; 1..31
      return jsdate.getDate();
    },
    l: function () { // Full day name; Monday...Sunday
      return txt_words[f.w()] + 'day';
    },
    N: function () { // ISO-8601 day of week; 1[Mon]..7[Sun]
      return f.w() || 7;
    },
    S: function(){ // Ordinal suffix for day of month; st, nd, rd, th
      var j = f.j()
      i = j%10;
      if (i <= 3 && parseInt((j%100)/10) == 1) i = 0;
      return ['st', 'nd', 'rd'][i - 1] || 'th';
    },
    w: function () { // Day of week; 0[Sun]..6[Sat]
      return jsdate.getDay();
    },
    z: function () { // Day of year; 0..365
      var a = new Date(f.Y(), f.n() - 1, f.j()),
        b = new Date(f.Y(), 0, 1);
      return Math.round((a - b) / 864e5);
    },

    // Week
    W: function () { // ISO-8601 week number
      var a = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3),
        b = new Date(a.getFullYear(), 0, 4);
      return _pad(1 + Math.round((a - b) / 864e5 / 7), 2);
    },

    // Month
    F: function () { // Full month name; January...December
      return txt_words[6 + f.n()];
    },
    m: function () { // Month w/leading 0; 01...12
      return _pad(f.n(), 2);
    },
    M: function () { // Shorthand month name; Jan...Dec
      return f.F().slice(0, 3);
    },
    n: function () { // Month; 1...12
      return jsdate.getMonth() + 1;
    },
    t: function () { // Days in month; 28...31
      return (new Date(f.Y(), f.n(), 0)).getDate();
    },

    // Year
    L: function () { // Is leap year?; 0 or 1
      var j = f.Y();
      return j % 4 === 0 & j % 100 !== 0 | j % 400 === 0;
    },
    o: function () { // ISO-8601 year
      var n = f.n(),
        W = f.W(),
        Y = f.Y();
      return Y + (n === 12 && W < 9 ? 1 : n === 1 && W > 9 ? -1 : 0);
    },
    Y: function () { // Full year; e.g. 1980...2010
      return jsdate.getFullYear();
    },
    y: function () { // Last two digits of year; 00...99
      return f.Y().toString().slice(-2);
    },

    // Time
    a: function () { // am or pm
      return jsdate.getHours() > 11 ? "pm" : "am";
    },
    A: function () { // AM or PM
      return f.a().toUpperCase();
    },
    B: function () { // Swatch Internet time; 000..999
      var H = jsdate.getUTCHours() * 36e2,
        // Hours
        i = jsdate.getUTCMinutes() * 60,
        // Minutes
        s = jsdate.getUTCSeconds(); // Seconds
      return _pad(Math.floor((H + i + s + 36e2) / 86.4) % 1e3, 3);
    },
    g: function () { // 12-Hours; 1..12
      return f.G() % 12 || 12;
    },
    G: function () { // 24-Hours; 0..23
      return jsdate.getHours();
    },
    h: function () { // 12-Hours w/leading 0; 01..12
      return _pad(f.g(), 2);
    },
    H: function () { // 24-Hours w/leading 0; 00..23
      return _pad(f.G(), 2);
    },
    i: function () { // Minutes w/leading 0; 00..59
      return _pad(jsdate.getMinutes(), 2);
    },
    s: function () { // Seconds w/leading 0; 00..59
      return _pad(jsdate.getSeconds(), 2);
    },
    u: function () { // Microseconds; 000000-999000
      return _pad(jsdate.getMilliseconds() * 1000, 6);
    },

    // Timezone
    e: function () { // Timezone identifier; e.g. Atlantic/Azores, ...
      // The following works, but requires inclusion of the very large
      // timezone_abbreviations_list() function.
/*              return that.date_default_timezone_get();
*/
      throw 'Not supported (see source code of date() for timezone on how to add support)';
    },
    I: function () { // DST observed?; 0 or 1
      // Compares Jan 1 minus Jan 1 UTC to Jul 1 minus Jul 1 UTC.
      // If they are not equal, then DST is observed.
      var a = new Date(f.Y(), 0),
        // Jan 1
        c = Date.UTC(f.Y(), 0),
        // Jan 1 UTC
        b = new Date(f.Y(), 6),
        // Jul 1
        d = Date.UTC(f.Y(), 6); // Jul 1 UTC
      return ((a - c) !== (b - d)) ? 1 : 0;
    },
    O: function () { // Difference to GMT in hour format; e.g. +0200
      var tzo = jsdate.getTimezoneOffset(),
        a = Math.abs(tzo);
      return (tzo > 0 ? "-" : "+") + _pad(Math.floor(a / 60) * 100 + a % 60, 4);
    },
    P: function () { // Difference to GMT w/colon; e.g. +02:00
      var O = f.O();
      return (O.substr(0, 3) + ":" + O.substr(3, 2));
    },
    T: function () { // Timezone abbreviation; e.g. EST, MDT, ...
      // The following works, but requires inclusion of the very
      // large timezone_abbreviations_list() function.
/*              var abbr = '', i = 0, os = 0, default = 0;
      if (!tal.length) {
        tal = that.timezone_abbreviations_list();
      }
      if (that.php_js && that.php_js.default_timezone) {
        default = that.php_js.default_timezone;
        for (abbr in tal) {
          for (i=0; i < tal[abbr].length; i++) {
            if (tal[abbr][i].timezone_id === default) {
              return abbr.toUpperCase();
            }
          }
        }
      }
      for (abbr in tal) {
        for (i = 0; i < tal[abbr].length; i++) {
          os = -jsdate.getTimezoneOffset() * 60;
          if (tal[abbr][i].offset === os) {
            return abbr.toUpperCase();
          }
        }
      }
*/
      return 'UTC';
    },
    Z: function () { // Timezone offset in seconds (-43200...50400)
      return -jsdate.getTimezoneOffset() * 60;
    },

    // Full Date/Time
    c: function () { // ISO-8601 date.
      return 'Y-m-d\\TH:i:sP'.replace(formatChr, formatChrCb);
    },
    r: function () { // RFC 2822
      return 'D, d M Y H:i:s O'.replace(formatChr, formatChrCb);
    },
    U: function () { // Seconds since UNIX epoch
      return jsdate / 1000 | 0;
    }
  };
  this.date = function (format, timestamp) {
    that = this;
    jsdate = (timestamp === undefined ? new Date() : // Not provided
      (timestamp instanceof Date) ? new Date(timestamp) : // JS Date()
      new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)
    );
    return format.replace(formatChr, formatChrCb);
  };
  return this.date(format, timestamp);
}


function json_response(strResponse, params) {
	var strCustomError = params.error ? params.error : 'An unexpected server error occurred: JS-BAS-G3S1AB';
	try {
		var arrResponse = jQuery.parseJSON(strResponse);
	} catch(e) {
		if (typeof params.fail == 'function')
			params.fail(strCustomError + '-101');
		$.alert(strResponse);
		return;
	}
	if (typeof(arrResponse) != "object") {
		if (typeof params.fail == 'function')
			params.fail(strCustomError + '-102');
		$.alert(strResponse);
		return;
	} else if (arrResponse["success"] == "true") {
		if (typeof params.success == 'function')
			params.success(arrResponse);
	} else {
		if (typeof params.fail == 'function')
			params.fail(strCustomError + '-102');
	}
}

function urlencode (str) {
	str = (str + '').toString();
	return encodeURIComponent(str)
		.replace(/!/g, '%21')
		.replace(/'/g, '%27')
		.replace(/\(/g, '%28')
		.replace(/\)/g, '%29')
		.replace(/\*/g, '%2A')
		.replace(/%20/g, '+');
}

function json_encode(data) {
	return JSON.stringify(data);
}

$.extend($.fn.disableTextSelect = function() {
	return this.each(function(){
		if($.browser.mozilla){//Firefox
			$(this).css('MozUserSelect','none');
		}else if($.browser.msie){//IE
			$(this).bind('selectstart',function(){return false;});
		}else{//Opera, etc.
			$(this).mousedown(function(){return false;});
		}
	});
});
$.fn.serializeObject = function()
{
   var o = {};
   var a = this.serializeArray();
   $.each(a, function() {
       if (o[this.name]) {
           if (!o[this.name].push) {
               o[this.name] = [o[this.name]];
           }
           o[this.name].push(this.value || '');
       } else {
           o[this.name] = this.value || '';
       }
   });
   return o;
};

// js add ons
function sort_by(field_names) {
	if (typeof(field_names) == "string") {
		field_names = [field_names];
	}
	return function (a, b) {
		for( var i=0 ; i<field_names.length ; i++ ) {
			var field_name = field_names[i];
			if(a[field_name] < b[field_name] )
				return 1;
		}
		return 0;
	}
}

// php functions from phpjs.org
function utf8_encode (argString) {
    if (argString === null || typeof argString === "undefined") {
        return "";
    }
     var string = (argString + ''); // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");
    var utftext = "",
        start, end, stringl = 0;

    start = end = 0;    stringl = string.length;
    for (var n = 0; n < stringl; n++) {
        var c1 = string.charCodeAt(n);
        var enc = null;
         if (c1 < 128) {
            end++;
        } else if (c1 > 127 && c1 < 2048) {
            enc = String.fromCharCode((c1 >> 6) | 192) + String.fromCharCode((c1 & 63) | 128);
        } else {
            enc = String.fromCharCode((c1 >> 12) | 224) + String.fromCharCode(((c1 >> 6) & 63) | 128) + String.fromCharCode((c1 & 63) | 128);
        }
        if (enc !== null) {
            if (end > start) {
                utftext += string.slice(start, end);            }
            utftext += enc;
            start = end = n + 1;
        }
    }
    if (end > start) {
        utftext += string.slice(start, stringl);
    }
     return utftext;
}

// php functions from phpjs.org
// jquery's merge function was not working properly
function array_merge () {
    var args = Array.prototype.slice.call(arguments),
        argl = args.length,
        arg,        retObj = {},
        k = '',
        argil = 0,
        j = 0,
        i = 0,        ct = 0,
        toStr = Object.prototype.toString,
        retArr = true;

    for (i = 0; i < argl; i++) {
        if (toStr.call(args[i]) !== '[object Array]') {
            retArr = false;
            break;
        }
    }
    if (retArr) {
        retArr = [];
        for (i = 0; i < argl; i++) {
            retArr = retArr.concat(args[i]);        }
        return retArr;
    }

    for (i = 0, ct = 0; i < argl; i++) {
        arg = args[i];
        if (toStr.call(arg) === '[object Array]') {
            for (j = 0, argil = arg.length; j < argil; j++) {
                retObj[ct++] = arg[j];
            }
        }
        else {
            for (k in arg) {
                if (arg.hasOwnProperty(k)) {
                    if (parseInt(k, 10) + '' === k) {
                        retObj[ct++] = arg[k];
                    }
                    else {
                        retObj[k] = arg[k];
                    }
                }
            }
        }
    }
    return retObj;
}

function serialize (mixed_value) {
    var _utf8Size = function (str) {
        var size = 0,
            i = 0,
            l = str.length,
            code = '';
        for (i = 0; i < l; i++) {            code = str.charCodeAt(i);
            if (code < 0x0080) {
                size += 1;
            } else if (code < 0x0800) {
                size += 2;            } else {
                size += 3;
            }
        }
        return size;    };
    var _getType = function (inp) {
        var type = typeof inp,
            match;
        var key;
        if (type === 'object' && !inp) {
            return 'null';
        }
        if (type === "object") {            if (!inp.constructor) {
                return 'object';
            }
            var cons = inp.constructor.toString();
            match = cons.match(/(\w+)\(/);            if (match) {
                cons = match[1].toLowerCase();
            }
            var types = ["boolean", "number", "string", "array"];
            for (key in types) {                if (cons == types[key]) {
                    type = types[key];
                    break;
                }
            }        }
        return type;
    };
    var type = _getType(mixed_value);
    var val, ktype = '';
    switch (type) {
    case "function":
        val = "";
        break;    case "boolean":
        val = "b:" + (mixed_value ? "1" : "0");
        break;
    case "number":
        val = (Math.round(mixed_value) == mixed_value ? "i" : "d") + ":" + mixed_value;        break;
    case "string":
        val = "s:" + _utf8Size(mixed_value) + ":\"" + mixed_value + "\"";
        break;
    case "array":    case "object":
        val = "a";
/*
            if (type == "object") {
                var objname = mixed_value.constructor.toString().match(/(\w+)\(\)/);                if (objname == undefined) {
                    return;
                }
                objname[1] = this.serialize(objname[1]);
                val = "O" + objname[1].substring(1, objname[1].length - 1);            }
            */
        var count = 0;
        var vals = "";
        var okey;        var key;
        for (key in mixed_value) {
            if (mixed_value.hasOwnProperty(key)) {
                ktype = _getType(mixed_value[key]);
                if (ktype === "function") {                    continue;
                }

                okey = (key.match(/^[0-9]+$/) ? parseInt(key, 10) : key);
                vals += this.serialize(okey) + this.serialize(mixed_value[key]);                count++;
            }
        }
        val += ":" + count + ":{" + vals + "}";
        break;    case "undefined":
        // Fall-through
    default:
        // if the JS object has a property which contains a null value, the string cannot be unserialized by PHP
        val = "N";        break;
    }
    if (type !== "object" && type !== "array") {
        val += ";";
    }    return val;
}


//slider ui

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
				$('.slider_container').fadeOut('fast',
					function() {
						$(this).empty().css({'margin-left':0}).append(data).fadeIn('fast',
							function(){
								//Stores the url to the latest page to use in slideRefresh()
								$('.slider_container').data('url',toLoad);
								$('.slider:last').data('url',toLoad);
								$('.slider:last').attr('url',toLoad);
								initialize();
								hideLoader();
							}
						);
					}
				);
			}
		);
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
				$('.slider_container').fadeOut('fast',
					function(){
						$('.slider:last').remove();
						$(this).append(data);
						$('.slider:last .col_title').append('<a class="slider_back"></a>');
						$('.slider:last .col_title a').html(currentBack);
						$(this).fadeIn('fast',
							function(){
								$('.slider_container').data('url',toLoad); //Stores the url to the latest page to use in slideRefresh()
								$('.slider:last').data('url',toLoad);
								$('.slider:last').attr('url',toLoad);
								initialize();
								hideLoader();
							}
						);
					}
				);
			}
		);
	}
}
function slideRefresh(strUrl) {
	var toLoad = $('.slider_container').data('url');
	if (strUrl)
		toLoad = strUrl;
	var currentBack = $('.slider:last .col_title a.slider_back').html();
	showLoader();
	$('.loader').fadeIn('normal',loadContent());
	function loadContent() {
		$.get(toLoad,'',
			function(data){
				$('.slider_container').fadeOut('fast',function(){
					$('.slider:last').remove();
					$(this).append(data);
					if (currentBack)
					{
						$('.slider:last .col_title').append('<a class="slider_back"></a>');
						$('.slider:last .col_title a').html(currentBack);
					}
					$(this).fadeIn('fast',function(){
						$('.slider_container').data('url',toLoad);
						$('.slider:last').data('url',toLoad);
						$('.slider:last').attr('url',toLoad);
						hideLoader();
						initialize();
					});
				});
			});
	}
}
function slideForward(id) {
	currentTitle = $('.slider:last .col_title span').html();
	var intMaxLen = 17;
	if (currentTitle.length > intMaxLen)
		currentTitle = jQuery.trim(currentTitle.substr(0, intMaxLen-3)) + "...";

	//currentTitle = $(this).parents('.slider').children('.col_title').html();

	var toLoad = $(id).attr('href');
	showLoader();
	$('.loader').fadeIn('normal',loadContent());
	//addressBar = $(this).attr('href');
	function loadContent() {
		$.get(toLoad,'',
			function(data){
				$('.slider_container').append(data);
				$('.slider_container .slider:last .col_title').append('<a class="slider_back"></a>');
				$('.slider_container .slider:last .col_title a').html(currentTitle);
				// Stores the url to the latest page to use in slideRefresh()
				$('.slider_container').data('url',toLoad);
				$('.slider:last').data('url',toLoad);
				$('.slider:last').attr('url',toLoad);
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

function slideURL(href) {
	currentTitle = $('.slider:last .col_title span').html();
	var intMaxLen = 17;
	if (currentTitle.length > intMaxLen)
		currentTitle = jQuery.trim(currentTitle.substr(0, intMaxLen-3)) + "...";
	var toLoad = href;
	showLoader();
	$('.loader').fadeIn('normal',loadContent());
	//addressBar = $(this).attr('href');
	function loadContent() {
		$.get(toLoad,'',
			function(data){
				$('.slider_container').append(data);
				$('.slider_container .slider:last .col_title').append('<a class="slider_back"></a>');
				$('.slider_container .slider:last .col_title a').html(currentTitle);
				// Stores the url to the latest page to use in slideRefresh()
				$('.slider_container').data('url',toLoad);
				$('.slider:last').data('url',toLoad);
				$('.slider:last').attr('url',toLoad);
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

function naviageBack() {
	slide_width = 773;
	$(".slider_container").animate(
		{'margin-left':parseInt($(".slider_container").css('margin-left')) + slide_width + 'px'},
		500
	);
	$(".slider:last").fadeOut().queue(function() {
		$(this).dequeue().remove();
		$('.slider_container').data('url', $(".slider:last").data("url"));
		$(".slider:last").trigger("slide_focused");
		$(".slider:last").trigger("slide_back");
	});
}

function naviageHome() {
	slide_width = 773;
	$(".slider_container").animate(
		{'margin-left':parseInt($(".slider_container").css('margin-left')) + (slide_width * ($(".slider").length-1)) + 'px'},
		500,
		function () {
			while ($(".slider").length > 1)
			{
				$(".slider:last").remove();
			}
			$(".slider:last").trigger("slide_focused");
		}
	);
}

var refHelpTimeout;
function initialize() {
	$(".slider:last a.dismiss").click(function(event) {
		event.preventDefault();
		$(this).parent().css({backgroundColor: "#ff0000"}).fadeOut("slow");
	});
	$(".slider:last").trigger("slide_focused");
	$(".slider:last .slider_back").click(function () {naviageBack()});
	$(".slider:last .info").click(function () {
		$(this).trigger('hover');
	});
	$(".slider:last .info").hover(function (e) {
		var parent = this;
		var strTitle = $(parent).attr('title');
		$(parent).attr('title', '');
		$(parent).attr('temp_title', strTitle);
		refHelpTimeout = window.setTimeout(function () {
			var objPosition = $(parent).offset();
			var intButtonLeft = objPosition.left;
			var intContentLeft =  $("#content").position().left;
			if (strTitle) {
				var objDiv = $('<div></div>').html(strTitle);
				objDiv.css({
					display: 'none',
					position: 'absolute',
					background: '#fff',
					border: '1px solid #555',
					top: objPosition.top+45,
					left: intContentLeft,
					'font-family' : '"Myriad Pro",Arial,Helvetica,sans-serif',
					'font-size' : '17px',
					'font-weight' : 'normal',
					'line-height' : '20px',
					'color' : '#000',
					'max-width' : "700px",
					padding: '12px 10px',
					'text-align': 'left',
					'box-shadow': '0 0 8px rgba(0,0,0,0.3)',
					'z-index' : 10000,
					'text-overflow' : 'string',
					'overflow' : 'visible',
					'white-space' : 'normal',
					'width' : 'auto',
					'max-width' : "700px"
				}).addClass('title_box').addClass('rounded');
				objDiv.appendTo("body");
				var intDivWidth = objDiv.width();
				//alert($("#content").position().left);
				objDiv.css({
					"left" : intButtonLeft - (intDivWidth / 2)
				});
				
				objDiv.fadeIn(0);
				var fModuleContentRight = intContentLeft + $("#content").width();
				var fBoxLeft = objDiv.position().left;
				var fBoxRight = fBoxLeft + intDivWidth;
				if (fBoxLeft < intContentLeft+25) {
					objDiv.css({
						left : intContentLeft+25
					});
				} else if (fBoxRight > fModuleContentRight+50) {
					console.log(fBoxRight + " > " + fModuleContentRight);
					//alert(123);
					objDiv.css({
						left : fModuleContentRight-intDivWidth-50
					});
				}
			}
		}, 1000);
		$(parent).data('refHelpTimeout', refHelpTimeout);
	}, function () {
		window.clearTimeout(refHelpTimeout);
		$('.title_box').stop().fadeOut().queue(function () {
			$(this).dequeue().remove();
		});
		$(this).attr('title', $(this).attr('temp_title'));
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

	$('.slider:last .editable').editable('http://www.example.com/save.php',{
		 indicator : '<img src="/images/back-end/admin/ajax-loader-sm.gif"/>',
		 submit:'<img src="/images/back-end/admin/bullet_disk.png"/>',
		 onblur:'ignore',
		 width:'120',
		 height:'14'
	});
	$('.editableText .content').editableText();
	$('.editableText .content').change(function(){
		var newValue = $(this).html();
	});
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
var boolCancelClicks = false;
$(function() {
	$(".list_first").tabs(".list_first > ul", {tabs: '.list_parent', effect: 'slide'});

	$("#nav .list_first a:not([href*='#'])").click(function(event) {
		event.preventDefault();
		slideReplace(this);
	})

	$(".slider a.link, .slider .wizard_nav a")
	.live('click',function(event) {
		event.preventDefault();
		if (boolCancelClicks)
			return;
		slideForward(this);
		boolCancelClicks = true;
		window.setTimeout(function () {
			boolCancelClicks = false;
		}, 700);
	});
	$('.edit a, a.edit').live('click',function(){$(this).parents('li').find('.editable').click()});

	initialize();

});
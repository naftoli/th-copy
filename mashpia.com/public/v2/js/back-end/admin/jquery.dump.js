/**
 * jquery.dump.js
 * @author Torkild Dyvik Olsen
 * @modfied by augmentlogic.com
 *
 * A simple debug function to gather information about an object.
 * Returns a nested tree with information.
 *
 */
(function($) {

$.fn.dumper = function() {
   return $.dumper(this);
}
$.dumper = function(object) {
	myWindow=window.open('','dumper','width=' + screen.width + ',height=' + screen.height + ',top=0,left=0,scrollbars=no');
	myWindow.document.body.innerHTML ="<textarea style='width:100%;height:100%'>" + $.dump(object) + "</textarea>";
	myWindow.focus();
}

$.alert = function(object) {
	alert(
		"-------------------------------------------------------------------------\n"
		+ $.dump(object)
	);
}

$.fn.dump = function() {
   return $.dump(this);
}
$.dump = function(object) {
	var recursion = function(obj, level) {
		var strDel = "\t";
		if(!level) level = 0;
		var dump = '', p = '';
		for(i = 0; i < level; i++) p += strDel;

		t = type(obj);
		switch(t) {
			 case "string":
				return '"' + obj + '"';
				break;
			 case "number":
				return obj.toString();
				break;
			 case "boolean":
				return obj ? 'true' : 'false';
			 case "date":
				return "Date: " + obj.toLocaleString();
			 case "array":
				dump += 'Array ( \n';
				$.each(obj, function(k,v) {
				   dump += p + strDel + k + ' => ' + recursion(v, level + 1) + '\n';
				});
				dump += p + ')';
				break;
			 case "object":
				dump += 'Object { \n';
				$.each(obj, function(k,v) {
				   dump += p + strDel + k + ': ' + recursion(v, level + 1) + '\n';
				});
				dump += p + '}';
				break;
			 case "jquery":
				dump += 'jQuery Object { \n';
				$.each(obj, function(k,v) {
				   dump += p + strDel + k + ' = ' + recursion(v, level + 1) + '\n';
				});
				dump += p + '}';
				break;
			 case "regexp":
				return "RegExp: " + obj.toString();
			 case "error":
				return obj.toString();
			 case "document":
			 case "domelement":
				dump += 'DOMElement [ \n';
				var arrSimpleItems = ['id','src','nodeName','type','className','name','value','checked','action','method','target'];
				for (intKey in arrSimpleItems)
				{
					if (obj[arrSimpleItems[intKey]])
						dump += p + strDel + arrSimpleItems[intKey] + ': ' + obj[arrSimpleItems[intKey]] + '\n';
				}
				var strCSS = css(obj);
				if (strCSS.length)
					dump += p + strDel + 'style: ' + css(obj) + '\n';
				if (obj.childNodes.length)
				{
					dump += p + strDel + 'innerHTML: [ \n';
					var offset = 0;
					for (i3 in obj.childNodes)
					{
						var v = obj.childNodes[i3];
						if(type(v) == "string") {
							if(v.textContent.match(/[^\s]/))
								dump += p + strDel + strDel + (i3-offset) + ' = String: ' + trim(v.textContent) + '\n';
							else
								offset++;
						} else {
							dump += p + strDel + strDel + (i3-offset) + ' = ' + recursion(v, level + 2) + '\n';
						}
					}
					dump += p + strDel + ']\n';
				}
				dump += p + ']';
				break;
			 case "function":
				var match = obj.toString().match(/^(.*)\(([^\)]*)\)/im);
				match[1] = trim(match[1].replace(new RegExp("[\\s]+", "g"), " "));
				match[2] = trim(match[2].replace(new RegExp("[\\s]+", "g"), " "));
				return match[1] + "(" + match[2] + ")";
			 case "window":
			 default:
				dump += 'N/A: ' + t;
				break;
		}

		return dump;
   }

   var type = function(obj) {
      var type = typeof(obj);

      if(type != "object") {
         return type;
      }

      switch(obj) {
         case null:
            return 'null';
         case window:
            return 'window';
         case document:
            return 'document';
         case window.event:
            return 'event';
         default:
            break;
      }

      if(obj.jquery) {
         return 'jquery';
      }

      switch(obj.constructor) {
         case Array:
            return 'array';
         case Boolean:
            return 'boolean';
         case Date:
            return 'date';
         case Object:
            return 'object';
         case RegExp:
            return 'regexp';
         case ReferenceError:
         case Error:
            return 'error';
         case null:
         default:
            break;
      }

      switch(obj.nodeType) {
         case 1:
            return 'domelement';
         case 3:
            return 'string';
         case null:
         default:
            break;
      }

      return 'Unknown';
   }

   return recursion(object);
}

function trim(str) {
   return ltrim(rtrim(str));
}

function ltrim(str) {
   return str.replace(new RegExp("^[\\s]+", "g"), "");
}

function rtrim(str) {
   return str.replace(new RegExp("[\\s]+$", "g"), "");
}
function css(obj){
	var res = '';
	for(var i2 in obj.style) {
		if (!!obj[i2])
			res += i2 + ":" + obj[i2] + ";";
	}
	return res;
}


})(jQuery);
/**
 * jquery.nosearch.js
 * @author Andrew Dear
 * @version 1.0
 * 
 * Search in a direction through the dom tree
 * keyword: 
 * direction: up, down [default: down]
 */
(function($) {
	$.fn.nodesearch = function(strKey) {
		var objSearchCursor = this;
		alert($.dump(objSearchCursor));
		/*var fnSearchNode = function (objNode, strKey) {
			
		}
		var objCurrent = $(this).next();
		
		fnSearchNode(strKey);
		return $.nodesearch(this);
		*/
	}
	
})(jQuery);
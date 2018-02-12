$(function () {
	var self = {
		start : function () {
			self.resize();
		},
		resize : function () {
			var resize = function () {
				$('body,#bg_tiles').css({
					height : 0
				});
				$('body,#bg_tiles').css({
					height : $(document).height()
				});

			};
			resize();
			window.setTimeout(function () {
				resize();
			},500);
			window.setTimeout(function () {
				resize();
			},1000);
			window.setTimeout(function () {
				resize();
			},1500);
			$(window).bind("resize", function () {
				resize();
			});
		}
	};
	self.start();
});
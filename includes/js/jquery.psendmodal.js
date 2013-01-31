// VERY Simple Modal

$.fn.psendmodal = function() {
	$('body').append('<div class="modal_overlay"></div><div class="modal"><div class="modal_title"><a href="#" class="modal_close">X</a></div><div class="modal_content"></div></div>');

	$(".modal_close").click(function() {
		$('.modal_overlay').stop(true, true).fadeOut();
		$('.modal').stop(true, true).fadeOut();
		return false;
	});

	$(".modal_overlay").click(function() {
		$('.modal_overlay').stop(true, true).fadeOut();
		$('.modal').stop(true, true).fadeOut();
		return false;
	});
	
	$(document).keyup(function(e) {
		if (e.keyCode == 27) { // Esc
			$('.modal_overlay').stop(true, true).fadeOut();
			$('.modal').stop(true, true).fadeOut();
			return false;
		}
	});
};
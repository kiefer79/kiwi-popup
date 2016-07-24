if("undefined"==typeof jQuery) {
	alert('Kiwi Popup needs jQuery! Include it on your own or activate jQuery in Plugin Settings');
}

$(document).ready(function () {
	var layer = $('#kiwi_popup_layer');
	var content = $('#kiwi_popup_content');
	var inner = $('#kiwi_popup_inner');
	var close = $('#kiwi_popup_close');

	layer.css('visibility', 'hidden');

	// calculate top position
	windowHeight = $(window).height();
	contentHeight = content.height();
	topPos = (windowHeight - contentHeight) / 2;
	content.css('top', topPos);

	// calculate left position
	windowWidth = $(window).width();
	contentWidth = content.width();
	leftPos = (windowWidth - contentWidth) / 2;
	content.css('left', leftPos);

	layer.css('visibility', 'visible');
	layer.hide();
	layer.fadeIn(1000);
	$("html, body").animate({ scrollTop: 0 }, "slow");

	if (hideclosebutton) {
		close.on('click', function () {
			layer.fadeOut(1000);
			content.remove();
		});
	}

	if (link) {
		inner.css('cursor', 'pointer');
		inner.on('click', function () {
			window.location.href = link;
		});
	} else if (!hideclosebutton) {
		inner.css('cursor', 'pointer');
		inner.on('click', function () {
			layer.fadeOut(1000);
			content.remove();
		});
	}

	if (autoclose > 0) {
		var timerUpdate = setInterval(
			function () {
				if (autoclose > 0) {
					$('#kiwi_popup_timevalue').text(autoclose);
					autoclose--;
				} else {
					layer.fadeOut(1000);
					content.remove();
					clearInterval(timerUpdate);
				}
			}, 1000);
	}
});
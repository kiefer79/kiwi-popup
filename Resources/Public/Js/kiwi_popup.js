if("undefined"==typeof jQuery) {
	alert('Kiwi Popup needs jQuery! Include it on your own or activate jQuery in Plugin Settings');
}

$(document).ready(function () {
	var layer = $('#kiwi_popup_layer');
	var content = $('#kiwi_popup_content');
	var inner = $('#kiwi_popup_inner');
	var close = $('#kiwi_popup_close');

	console.log('link: ' + link);
	console.log('autoclose: ' + autoclose);
	console.log('hideclosebutton: ' + hideclosebutton);

	layer.css('visibility', 'hidden');

	// calculate top position
	windowHeight = $(window).height();
	console.log(windowHeight);
	contentHeight = content.height();
	console.log(contentHeight);
	topPos = (windowHeight - contentHeight) / 2;
	console.log(topPos);
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
		console.log('hideclosebutton not set');
		close.on('click', function () {
			console.log('click closebutton');
			layer.fadeOut(1000);
			content.remove();
		});
	}

	if (link) {
		console.log('link: ' + link);
		inner.css('cursor', 'pointer');
		inner.on('click', function () {
			console.log('click link');
			window.location.href = link;
		});
	} else if (!hideclosebutton) {
		console.log('hideclosebutton not set');
		inner.css('cursor', 'pointer');
		inner.on('click', function () {
			console.log('click inner');
			layer.fadeOut(1000);
			content.remove();
		});
	}

	if (autoclose > 0) {
		console.log('autoclose');
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
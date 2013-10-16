RigBagAdvert = function() {

	this.searchParams = {
		query: '',
		lastCall: false
	};
};

RigBagAdvert.prototype.sendToFriend = function() {

	if($('#formToFriend').valid()) {

		var toMail = $('#tf-emails').val();
		if (toMail == '') { //TODO: refactor this piece, do proper validation
			return;
		}
		showLoader();
		var data = {
			advertId: $('#tf-advert-id').val(),
			message: $('#tf-message').val(),
			emails: toMail
		};

		$.ajax({
			type: 'POST',
			url: APP_PATH + 'advert/suggest-to-friend/',
			data: data,
			cache: false,
			dataType: 'json',
			success: function(data) {
				$('#tf-message').val('');
				$('.to-friend-form').slideUp();
				hideLoader();
			}
		});
	}

};

RigBagAdvert.prototype.freeAccept = function(freeId) {

	showLoader();

	var dataIn = {
		freeId: freeId
	};

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'free/accept/',
		data: dataIn,
		cache: false,
		dataType: 'json',
		success: function(data) {
			hideLoader();
			$('#messages-list').html(data.content);
			sendMail('freeAccepted', dataIn);
		}
	});

};

RigBagAdvert.prototype.swapAccept = function(swapId) {

	showLoader();

	var dataIn = {
		swapId: swapId
	};

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'swap/accept/',
		data: dataIn,
		cache: false,
		dataType: 'json',
		success: function(data) {
			hideLoader();
			$('#messages-list').html(data.content);
			sendMail('swapAccepted', dataIn);
		}
	});

};

RigBagAdvert.prototype.freeReject = function(freeId) {

	showLoader();

	var dataIn = {
		freeId: freeId
	};

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'free/reject/',
		data: dataIn,
		cache: false,
		dataType: 'json',
		success: function(data) {
			hideLoader();
			$('#messages-list').html(data.content);
			sendMail('freeRejected', dataIn);
		}
	});

};

RigBagAdvert.prototype.swapReject = function(swapId) {

	showLoader();

	var dataIn = {
		swapId: swapId
	};

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'swap/reject/',
		data: dataIn,
		cache: false,
		dataType: 'json',
		success: function(data) {
			hideLoader();
			$('#messages-list').html(data.content);
			sendMail('swapRejected', dataIn);
		}
	});

};

RigBagAdvert.prototype.renderForm = function() {

	switch($('.f-mode').val()) {
	case 'sale':
		$('.f-name').addClass('required');
		$('.f-price').addClass('required');
		$('.f-price').addClass('number');
		$('.f-swap-for').removeClass('required');
		$('.only-swap, .only-freebie').hide();
		$('.only-sale').show();
		break;
	case 'swap':
		$('.f-name').removeClass('required');
		$('.f-price').removeClass('required');
		$('.f-price').removeClass('number');
		$('.f-swap-for').addClass('required');
		$('.only-sale, .only-freebie').hide();
		$('.only-swap').show();
		break;
	case 'freebie':
		$('.f-name').removeClass('required');
		$('.f-swap-for').removeClass('required');
		$('.f-price').removeClass('required');
		$('.f-price').removeClass('number');
		$('.only-swap, .only-sale').hide();
		$('.only-freebie').show();
		break;
	}

	this.renderPreview();

};

RigBagAdvert.prototype.renderPreview = function() {

	var actionLabel;
	var name = $('.f-name').val();
	var extLangth = 28;
	var price = $('.f-price').val();
	var currency = $('#dropdown-currency .value').html();
	var location2 = $('.f-location-formated').val().split(', ');
	var location = '';
	var swapFor = '';
	var forLabel = ' for ';
	var length = 0;
	var lengthStr = '';

	if(location2.length) {
		c = location2.length - 1;
		for(var b = c; b > c - 2; b--) {
			if(b >= 0) {
				if(location) {
					location = ', ' + location;
				}
				location = location2[b] + location;
			}
		}
	}


	switch($('.f-mode').val()) {
	case 'sale':
		actionLabel = 'FOR SALE';
		swapFor = '';
		break;
	case 'swap':
		actionLabel = 'SWAP';
		swapFor = $('.f-swap-for').val();
		break;
	case 'freebie':
		actionLabel = 'FREEBIE';
		swapFor = '';
		break;
	}

	var html = '';

	lengthStr = lengthStr + actionLabel;
	lengthStr = lengthStr + name;

	html = html + '<span class="label-action">';
	html = html + actionLabel;
	html = html + '</span>';
	html = html + ' <span class="label-important">' + name + '</span>';

	if($('.f-mode').val() == 'swap' && swapFor.length) {
		lengthStr = lengthStr + forLabel + swapFor;
		html = html + forLabel + '<span class="label-important">' + swapFor + '</span>';
	}

	html = html + '<br/>';
	html = html + $('#dropdown-condition .value').html();

	lengthStr = lengthStr + $('#dropdown-condition .value').html();

	if($('.f-mode').val() == 'sale') {
		if(parseInt(price, 10)) {
			lengthStr = lengthStr + price + currency;
			html = html + ', ';
			html = html + price + ' ' + currency;
		}
	}

	if(location.length) {
		lengthStr = lengthStr + location;
		html = html + ', ';
		html = html + location;
	}

	html = html + '<br/><a href="#" onclick="return false;">rigbag.com/a/skdj8930933</a>';

	var leftChars = parseInt($('#maxLength').val(), 10) - parseInt(lengthStr.length, 10);
	if(leftChars >= 0) {
		$('.chars-left').html(leftChars + ' characters left');
		$('.chars-left').removeClass('error');
		$('#tooLong').val('0');
	} else {
		$('.chars-left').html(leftChars + ' characters left');
		$('.chars-left').addClass('error');
		$('#tooLong').val('1');
	}

	$('.preview').html(html);

};

RigBagAdvert.prototype.search = function() {

	$(document).unbind('scroll');

	var type, data;

	if(!$('body').hasClass('circles')) {
		type = 'main';
		data = {
			query: $('#searchQuery').val(),
			category: $('#searchCategory').val(),
			mode: $('#headerBottom ul .active').attr('data-type'),
			type: 'main'
		};
	} else {
		type = 'circle';
		data = {
			query: $('#searchQuery2').val(),
			category: $('.circles-sidebar li.active').attr('data-id'),
			type: 'circle'
		};
	}

	rbApp.actionStamp = new Date().getTime();
	data.actionStamp = rbApp.actionStamp;
	var call = false;

	if(data.query.length > 2) {
		call = true;
	} else if(this.searchParams.query.length > data.query.length && this.searchParams.lastCall) {
		call = true;
	}

	if(call) {
		showLoader();
		this.searchParams.lastCall = call;
		this.searchParams.query = data.query;

		$.ajax({
			type: 'POST',
			url: APP_PATH + 'advert/search/',
			data: data,
			cache: false,
			dataType: 'json',
			success: function(data) {

				if(data.actionStamp == rbApp.actionStamp) {
					rbApp.fillData(data);
				}
			}
		});
		hideLoader();
	}

};

RigBagAdvert.prototype.remove = function() {

	var data = {
		advertId: $('.adv-id').val()
	};

	showLoader();

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'advert/delete/',
		data: data,
		cache: false,
		dataType: 'json',
		success: function(data) {
			if(data.success) {
				setUrl('/adverts/list/' + data.mode + '/');
			}
		}
	});
};

RigBagAdvert.prototype.close = function() {
	var data = {
		advertId: $('.adv-id').val()
	};

	showLoader();

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'advert/close/',
		data: data,
		cache: false,
		dataType: 'json',
		success: function(data) {
			if(data.success) {
				setUrl('/adverts/view/' + data.hash + '/');
			}
		}
	});
};

RigBagAdvert.prototype.socialPublish = function(advertId, social) {

	var data = {
		advertId: advertId,
		social: social
	};

	showLoader();

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'advert/social/publish/',
		data: data,
		cache: false,
		dataType: 'json',
		success: function(data) {
			rbApp.showFlash(data.flashMessage.title, data.flashMessage.content, data.flashMessage.type, 0);
			hideLoader();
		}
	});

};

RigBagAdvert.prototype.post = function() {

	var circlesData = [];
	var socialData = [];

	$('.user-circles .selected').each(function() {
		circlesData.push($(this).attr('data-id'));
	});

	$('.social-connect li.connected').each(function() {
		socialData.push($(this).attr('data-type'));
	});

	if($('#formAdvert').valid() && $('#tooLong').val() == '0' && circlesData.length) {

		var data = {
			id: $('.f-id').val(),
			title: $('.f-name').val(),
			description: $('.f-description').val(),
			condition: $('.f-condition').val(),
			currency: $('.f-currency').val(),
			mode: $('.f-mode').val(),
			location: $('.f-location').val(),
			locationFormated: $('.f-location-formated').val(),
			locationLng: $('.f-location-lng').val(),
			locationLat: $('.f-location-lat').val(),
			price: $('.f-price').val(),
			swapFor: $('.f-swap-for').val(),
			payPal: $('.f-paypal').val(),
			circles: circlesData,
			social: socialData
		};
		showLoader();
		$.ajax({
			type: 'POST',
			url: APP_PATH + 'advert/save/',
			data: data,
			cache: false,
			dataType: 'json',
			success: function(data) {
				//if(data.redirectToPay !== undefined) {
				//	window.location = APP_PATH + 'payment/advert/paypal/' + data.advertId + '/';
				//} else {
					setUrl('/adverts/list/' + $('.f-mode').val() + '/');
				//}
				hideLoader();
			}
		});
	} else {
		var msg = '';

		if($('#tooLong').val() == '1') {
			msg = msg + '&bull; Advert is too long<br/>';
		}
		if(!circlesData.length) {
			msg = msg + '&bull; Please select circle';
		}

		if(msg.length) {
			$('#msg-view').html('<div class="alert alert-error">' + msg + '</div>');
			$('#msg-view').show();
		} else {
			$('#msg-view').hide();
		}
	}


};
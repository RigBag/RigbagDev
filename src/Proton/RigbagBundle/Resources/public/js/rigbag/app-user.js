RigBagUser = function() {
	this.id = null;
};


RigBagUser.prototype.setId = function(id) {
	this.id = id;
};

RigBagUser.prototype.search = function() {

	var data = {
		query: $('#searchQuery').val(),
		circle: $('.circles-sidebar li.active').attr('data-id')
	};

	rbApp.actionStamp = new Date().getTime();
	data.actionStamp = rbApp.actionStamp;


	$.ajax({
		type: 'POST',
		url: APP_PATH + 'users/search/',
		data: data,
		cache: false,
		dataType: 'json',
		success: function(data) {
			hideLoader();
			if(data.actionStamp == rbApp.actionStamp) {
				$('#circle-content').html(data.content);
			}
		}
	});

};

RigBagUser.prototype.refreshAccount = function() {
	this.panelRefresh();
};

RigBagUser.prototype.refreshMessages = function() {

	var data = {};

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'user/panel/messages/',
		data: data,
		cache: false,
		dataType: 'json',
		success: function(data) {
			$('#user-panel .popover-content').html(data.content);
		}
	});
};

RigBagUser.prototype.panelRefresh = function() {

	var data = {};

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'user/panel/refresh/',
		data: data,
		cache: false,
		dataType: 'json',
		success: function(data) {
			$('#user-panel .message .info-alert').html(data.messages.newCount);
			if(data.messages.newCount) {
				$('#user-panel .message').addClass('active');
			} else {
				$('#user-panel .message').removeClass('active');
			}
		},
		error: function(data) {
			console.log(data);
		}

	});

};

RigBagUser.prototype.profileUpdate = function(isSignUp) {

	if(isSignUp === undefined) {
		isSignUp = false;
	}

	if($('#formProfile').valid()) {
		showLoader();

		var data = {
			name: $('.f-name').val(),
			email: $('.f-email').val(),
			location: $('.f-location').val(),
			location_cc: $('.f-location-cc').val(),
			location_formated: $('.f-location-formated').val(),
			location_lat: $('.f-location-lat').val(),
			location_lng: $('.f-location-lng').val(),
			postCode: '',
			paypalId: $('.f-paypal').val(),
			phone: $('.f-phone').val(),
			bio: $('.f-bio').val()
		};

		$.ajax({
			type: 'POST',
			url: APP_PATH + 'settings/my-profile-update/',
			data: data,
			cache: false,
			dataType: 'json',
			success: function(data) {
				hideLoader();
				if(data.success) {
					if(isSignUp) {
						setUrl('/signup/mysports/');
					} else {
						rbApp.showFlash('', 'Your profile has been updated.', 'success', 6000);
					}
					if(data.updateImage) {
						$('.user-short-info .ico').css('background-image', 'url(' + data.updateImage + ')');
					}
					$('.user-short-info .user').html($('.f-name').val());
				} else {
					for(var a in data.errorFields) {
						$('.' + data.errorFields[a]).addClass('error');
					}
				}
			},
			error: function() {
				hideLoader();
				rbApp.showFlash('Error', 'Upsss', 'error', 10000);
			}
		});
	}

};

RigBagUser.prototype.facebookConnect = function() {

	var data = {
		bckData: {
			name: $('.f-name').val(),
			email: $('.f-email').val(),
			location: $('.f-location').val(),
			location_cc: $('.f-location-cc').val(),
			postCode: $('.f-post-code').val(),
			phone: $('.f-phone').val(),
			bio: $('.f-bio').val()
		}
	};

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'settings/connect/facebook/',
		data: data,
		cache: false,
		dataType: 'json',
		success: function(data) {
			if(data.canConnect) {
				window.location = APP_PATH + 'login/facebook/';
			}
		},
		error: function() {

		}
	});
};

RigBagUser.prototype.facebookDisconnect = function() {
	$.ajax({
		type: 'POST',
		url: APP_PATH + 'settings/disconnect/facebook/',
		data: data,
		cache: false,
		dataType: 'json',
		success: function(data) {

		},
		error: function() {

		}
	});
};

RigBagUser.prototype.twitterConnect = function() {

	var data = {
		bckData: {
			name: $('.f-name').val(),
			email: $('.f-email').val(),
			location: $('.f-location').val(),
			location_cc: $('.f-location-cc').val(),
			postCode: $('.f-post-code').val(),
			phone: $('.f-phone').val(),
			bio: $('.f-bio').val()
		}
	};

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'settings/connect/twitter/',
		data: data,
		cache: false,
		dataType: 'json',
		success: function(data) {
			if(data.canConnect) {
				window.location = APP_PATH + 'login/twitter/';
			}
		},
		error: function() {

		}
	});
};

RigBagUser.prototype.twitterDisconnect = function() {
	$.ajax({
		type: 'POST',
		url: APP_PATH + 'settings/disconnect/twitter/',
		data: data,
		cache: false,
		dataType: 'json',
		success: function(data) {

		},
		error: function() {

		}
	});
};

RigBagUser.prototype.googleConnect = function() {

	var data = {
		bckData: {
			name: $('.f-name').val(),
			email: $('.f-email').val(),
			location: $('.f-location').val(),
			location_cc: $('.f-location-cc').val(),
			postCode: $('.f-post-code').val(),
			phone: $('.f-phone').val(),
			bio: $('.f-bio').val()
		}
	};

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'settings/connect/google/',
		data: data,
		cache: false,
		dataType: 'json',
		success: function(data) {
			if(data.canConnect) {
				window.location = APP_PATH + 'login/google/';
			}
		},
		error: function() {

		}
	});
};

RigBagUser.prototype.googleDisconnect = function() {
	$.ajax({
		type: 'POST',
		url: APP_PATH + 'settings/disconnect/google/',
		data: data,
		cache: false,
		dataType: 'json',
		success: function(data) {

		},
		error: function() {

		}
	});
};
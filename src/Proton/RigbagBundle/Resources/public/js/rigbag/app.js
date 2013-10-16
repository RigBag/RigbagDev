function RigBagApp() {

	this.hash = '';
	this.actionData = {};
	this.action = '';
	this.actionInProgess = false;
	this.actionStamp = null;
	this.advert = new RigBagAdvert();
	this.qa = new RigBagQa();
	this.user = new RigBagUser();
	this.circle = new RigBagCircle();
	this.mySport = new RigBagMySport();
	this.queryParams = {};
}


RigBagApp.prototype.init = function() {
	this.getHash();
	this.parseHash();
};

RigBagApp.prototype.setActionData = function(data) {
	this.actionData = data;
};

RigBagApp.prototype.getActionData = function() {
	return this.actionData;
};

RigBagApp.prototype.getHash = function() {
	this.hash = window.location.hash.substr(1);
	return this.hash;
};

RigBagApp.prototype.setAction = function(action) {
	this.action = action;
};

RigBagApp.prototype.getAction = function() {
	return this.action;
};

RigBagApp.prototype.setHash = function(hash) {
	window.location.hash = '#' + hash;
	this.hash = hash;
};

RigBagApp.prototype.mustLoginInit = function() {
	$('.must-login').attr('onclick', '');
	$('.must-login').click(function() {
		$('#modalLogin').modal('show');
		return false;
	});
};

RigBagApp.prototype.reload = function() {

	window.location.reload();
};

RigBagApp.prototype.callAction = function(action, callback) {


	$('.question-new-form').slideUp();

	if($('#flash-message').length) {
		$('#flash-message').fadeOut(function() {
			$(this).remove();
		});
	}


	this.actionStamp = new Date().getTime();
	data = this.getActionData();
	data.actionStamp = this.actionStamp;



	showLoader();

	$.ajax({
		type: 'POST',
		url: APP_PATH + action,
		data: data,
		cache: false,
		dataType: 'json',
		success: function(data) {
			hideLoader();
			if(data.actionStamp == rbApp.actionStamp) {
				rbApp.postAction(data);
			}
			if(callback !== undefined) {
				eval(callback);
			}
		},
		error: function(data) {
			hideLoader();
			var msg = '';
			msg = data.statusText;
			if( msg.length == 0 ) {
				msg = 'We have noticed some problems. Please refresh the site.';
			}
			rbApp.showFlash('', msg, 'error', 0);
		}
	});
};

RigBagApp.prototype.loadMoreQa = function(source) {

	var actionUrl;
	this.actionStamp = new Date().getTime();
	data = this.getActionData();
	data.actionStamp = this.actionStamp;

	showLoader();

	if(source === 'mode') {
		actionUrl = 'advert/more/';
	} else {
		actionUrl = 'q-and-a/more/';
	}


	$.ajax({
		type: 'POST',
		url: APP_PATH + actionUrl,
		data: data,
		cache: false,
		dataType: 'json',
		success: function(data) {
			if(data.actionStamp == rbApp.actionStamp) {
				hideLoader();
				if(data.full === 0) {
					$('.load-more').addClass('hide');
				}

				$('#qa-list ul').append(data.content);
			}


		},
		error: function(data) {
			hideLoader();
			rbApp.showFlash('', data.status + ' ' + data.statusText, 'error', 3000);
		}
	});

};

RigBagApp.prototype.loadMoreAdverts = function(source) {

	var actionUrl;
	this.actionStamp = new Date().getTime();
	data = this.getActionData();
	data.actionStamp = this.actionStamp;

	showLoader();

	switch(source) {
	case 'mode':
		actionUrl = 'advert/more/';
		break;
	case 'circle':
		actionUrl = 'circles/more/adverts/';
		break;
	}


	$.ajax({
		type: 'POST',
		url: APP_PATH + actionUrl,
		data: data,
		cache: false,
		dataType: 'json',
		success: function(data) {
			if(data.actionStamp == rbApp.actionStamp) {
				hideLoader();
				if(data.full === 0) {
					$('.load-more').addClass('hide');
				}

				$('#adverts-list').append(data.content);
			}


		},
		error: function(data) {
			hideLoader();
			rbApp.showFlash('', data.status + ' ' + data.statusText, 'error', 3000);
		}
	});
};

RigBagApp.prototype.postAction = function(data) {
	this.fillData(data);
	this.updateInterface();
};

RigBagApp.prototype.updateInterface = function() {

};

RigBagApp.prototype.fillData = function(data) {

	$(document).unbind('scroll');

	for(var a in data.toUpdate) {

		switch(data.toUpdate[a]) {
		case 'headerExtras':
			$('#headerExtras').html(data.header.extras);
			break;
		case 'headerExtras-2':
			$('#headerExtras-2').html(data.header.extras2);
			break;
		case 'headerTop':
			$('#headerTop').html(data.header.top);
			break;
		case 'headerBottom':
			$('#headerBottom').html(data.header.bottom);
			break;
		case 'content':
			$('#content').html(data.content);
			break;
		case 'bodyClass':
			$('body').attr('class', data.bodyClass);
			break;
		case 'subContent':
			$('#sub-content').html(data.subContent);
			break;
		case 'flashMessage':
			if(data.flashMessage) {
				this.showFlash(data.flashMessage.title, data.flashMessage.content, data.flashMessage.type, 0);
			}
			break;
		case 'circleContent':
			$('#circle-content').html(data.content);
			break;
		case 'circleContentClass':
			$('#circle-content').attr('class', data.contentClass);
			break;
		}
	}

};

RigBagApp.prototype.parseHash = function() {

	var pHash = this.hash.split('/');
	this.queryParams = null;

	$('.sidebar .menu li').removeClass('active');

	switch(pHash[1]) {

		// NEWS
	case 'news':

		$('.sidebar .menu li.news').addClass('active');

		if(pHash[2] === 'list') {
			this.callAction('news/list/');
		}

		break;


		// SIGNUP
	case 'signup':

		$('.sidebar .menu li.settings').addClass('active');

		switch(pHash[2]) {
		case 'profile':
			this.callAction('signup/profile/');
			break;
		case 'mysports':
			this.callAction('signup/mysports/');
			break;
		case 'subscription':
			this.callAction('signup/subscription/');
			break;
		case 'done':
			this.callAction('signup/done/');
			break;
		}

		break;

		// GUEST
	case 'g':
		$('.sidebar .menu li.adverts').addClass('active');
		break;


		// ADVERTS
	case 'adverts':

		$('.sidebar .menu li.adverts').addClass('active');

		switch(pHash[2]) {
		case 'buy':
			this.callAction('advert/buy/' + pHash[3] + '/');
			break;
		case 'list':
			this.callAction('advert/list/' + pHash[3] + '/');
			break;
		case 'view':
			this.callAction('advert/view/' + pHash[3] + '/h/');
			break;
		case 'search':

			break;
		case 'add':
			if(pHash[3] === 'circle') {
				this.callAction('advert/add/?circles=' + pHash[4]);
			} else {
				this.callAction('advert/add/');
			}
			break;
		case 'edit':
			this.callAction('advert/edit/' + pHash[3] + '/');
			break;
		}

		break;

		// CIRCLES
	case 'circles':

		$('.sidebar .menu li.circles').addClass('active');

		if(pHash[2] === 'list') {
			this.callAction('circles/0/');
		} else  if( pHash[2] == 'browse' ) {
			this.callAction('circles/browse/');
		} else {

			switch(pHash[3]) {
			case 'adverts':
				if($('#circle-content').length) {
					this.callAction('circles/adverts/' + pHash[2] + '/simple/');
				} else {
					this.callAction('circles/adverts/' + pHash[2] + '/full/');
				}
				break;
			case 'qa':
				if($('#circle-content').length) {
					this.callAction('circles/qa/' + pHash[2] + '/simple/');
				} else {
					this.callAction('circles/qa/' + pHash[2] + '/full/');
				}
				break;
			case 'members':
				if($('#circle-content').length) {
					this.callAction('circles/members/' + pHash[2] + '/simple/');
				} else {
					this.callAction('circles/members/' + pHash[2] + '/full/');
				}
				break;
			}
		}

		break;

		// Q&A
	case 'qa':

		$('.sidebar .menu li.qa').addClass('active');

		switch(pHash[2]) {
		case 'list':
			this.callAction('q-and-a/none/0/');
			break;
		case 'add':
			this.callAction('q-and-a/op/1/');
			break;
		case 'view':
			this.callAction('q-and-a/view/' + pHash[3] + '/');
			break;
		}

		break;

		// PROFILE
	case 'profile':

		if(pHash[2] == this.user.id) {
			$('.sidebar .menu li.my-profile').addClass('active');
		}
		switch(pHash[3]) {
		case 'adverts':
			this.callAction('profile/' + pHash[2] + '/');
			break;
		case 'circles':
			switch(pHash[4]) {
			case 'join':
				if($('body').hasClass('my-profile')) {
					this.callAction('circles/join/simple/');
				} else {
					this.callAction('circles/join/full/');
				}
				break;
			case 'list':
				if($('body').hasClass('my-profile')) {
					this.callAction('profile/circles/' + pHash[2] + '/simple/');
				} else {
					this.callAction('profile/circles/' + pHash[2] + '/full/');
				}
				break;
			}
			break;
		case 'qa':
			if($('body').hasClass('my-profile')) {
				this.callAction('profile/qa/' + pHash[2] + '/simple/');
			} else {
				this.callAction('profile/qa/' + pHash[2] + '/full/');
			}
			break;
		default:
			this.callAction('profile/');
			$('.sidebar .menu li.my-profile').addClass('active');
		}

		break;

		// NEWS
	case 'news':

		$('.sidebar .menu li.news').addClass('active');

		break;

		// SETTINGS
	case 'settings':

		$('.sidebar .menu li.settings').addClass('active');

		switch(pHash[2]) {
		case 'profile':
			if(pHash[3] == 'reload') {
				this.callAction('settings/?r=1');
			} else {
				this.callAction('settings/');
			}
			break;
		case 'mysports':
			this.callAction('settings/my-sports/');
			break;
		case 'transactions':
			this.callAction('profile/transactions/');
			break;
		case 'subscription':
			this.callAction('profile/subscription/');
			break;
		}

		break;

		// DEFAULT:
	default:

		$('.sidebar .menu li.adverts').addClass('active');
		this.callAction('advert/list/sale/');
	}
};


RigBagApp.prototype.showFlash = function(msgTitle, msgContent, type, hideTime) {

	$('#flash-message').remove();


	var h = '<div id="flash-message" class="alert alert-block';
	if(type == 'success') {
		h = h + ' alert-success';
	} else if(type == 'error') {
		h = h + ' alert-error';
	} else if(type == 'info') {
		h = h + ' alert-info';
	}
	h = h + '">';
	h = h + '<button type="button" class="close" data-dismiss="alert">×</button>';
	if(msgTitle.length) {
		h = h + '<h4>' + msgTitle + '</h4>';
	}
	h = h + msgContent;
	h = h + '</div>';

	$('.container .content .body').prepend(h);

	if(hideTime) {
		setTimeout('rbApp.hideFlash()', hideTime);
	}
};

RigBagApp.prototype.hideFlash = function() {
	$('#flash-message').fadeOut(function() {
		$(this).remove();
	});
};
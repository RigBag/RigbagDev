RigBagQa = function() {

	this.searchParams = {
		query: '',
		lastCall: false
	};

};

RigBagQa.prototype.search = function() {

	var type, data;

	if($('body').hasClass('circles')) {
		type = 'circles';
	} else {
		type = 'main';
	}

	if(type == 'circles') {
		data = {
			query: $('#searchQuery').val(),
			circle: $('.circles-sidebar li.active').attr('data-id'),
			type: type
		};
	} else {
		data = {
			query: $('#searchQuery').val(),
			category: $('#searchCategory').val(),
			type: type
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
			url: APP_PATH + 'q-and-a/search/',
			data: data,
			cache: false,
			dataType: 'json',
			success: function(data) {
				hideLoader();
				if(rbApp.actionStamp == data.actionStamp) {
					if(type === 'circles') {
						$('#circle-content').html(data.content);
					} else {
						$('#qa-list').html(data.content);
					}
				}
			}
		});
	}

};

RigBagQa.prototype.remove = function(qid, type, extraData) {

	var data = {
		id: qid
	};

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'q-and-a/delete/',
		data: data,
		cache: false,
		dataType: 'json',
		success: function(data) {
			switch(type) {
			case 'call-action':
				rbApp.callAction(extraData);
				break;
			case 'fade-out':
				$(extraData).fadeOut();
				break;
			}
		}
	});
};

RigBagQa.prototype.answersRefresh = function() {

	var data = {
		qid: $('#qid').val()
	};

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'q-and-a/referesh/',
		data: data,
		cache: false,
		dataType: 'json',
		success: function(data) {
			$('#qa-answers').html(data.content);
		}
	});

};

RigBagQa.prototype.advertPrivateReply = function(obj) {

	if($(obj).valid()) {

		var msg = $(obj).val();
		var usrName = $('.usr-name').val();
		var parent = $(obj).attr('data-parent');
		var d = new Date();
		var ts = d.getTime();
		var advertId = $('.adv-id').val();

		var html = '<li class="hide" id="answer-' + ts + '">';
		html = html + '<div class="user">' + usrName + '</div>';
		html = html + '<div class="answer">' + msg + '</div>';
		html = html + '<div class="date" date-stamp="' + d.getTime() + '">now';
		//html = html + ' &bull; <a href="#" onclick="qaAdvertPrivateDelete( $(this).parent().parent() ); return false;" title="" class="hide remove-2">remove</a>';
		html = html + ' &bull; <a href="#" onclick="if( confirm( \'Are you sure?\' ) ) { rbApp.qa.remove( $(this).parent().parent().attr(\'data-id\'), \'fade-out\', $(this).parent().parent() ); } return false;" title="" class="hide remove-2">remove</a>';
		html = html + '</div>';
		html = html + '</li>';

		$('#answers-' + parent).append(html);
		$('#answer-' + ts).fadeIn();
		$(obj).val('');

		var data = {
			qid: parent,
			answer: msg,
			extraType: 'advertPrivate'
		};

		$.ajax({
			type: 'POST',
			url: APP_PATH + 'q-and-a/answer/',
			data: data,
			cache: false,
			dataType: 'json',
			success: function(data) {
				$('#answer-' + ts + ' .remove-2').fadeIn();
				$('#answer-' + ts).attr('data-id', data.id);
			}
		});
	}

};


RigBagQa.prototype.answerSend = function() {

	if($('#answer').valid()) {

		var data = {
			answer: $('#answer').val(),
			qid: $('#qid').val()
		};

		$.ajax({
			type: 'POST',
			url: APP_PATH + 'q-and-a/answer/',
			data: data,
			cache: false,
			dataType: 'json',
			success: function(data) {
				$('#answer').val('');
				$('#qa-answers').html(data.content);
			}
		});

	}
};

RigBagQa.prototype.send = function(circleId) {


	if($('#formQA').valid()) {
		showLoader();
		var extraData = '';
		extraData = $('#extraData').val();

		var dataIn = {
			question: $('#question').val(),
			circle: $('#questionCircle').val(),
			extraType: $('#extraType').val(),
			extraData: extraData,
			circleId: circleId
		};

		$.ajax({
			type: 'POST',
			url: APP_PATH + 'q-and-a/add/',
			data: dataIn,
			cache: false,
			dataType: 'json',
			error: function(data) {
				hideLoader();
			},
			success: function(data) {
				hideLoader();
				var dataIn2;
				//console.log(data);
				switch($('#extraType').val()) {
				case 'advertPrivate':
					$('#messages-list').html(data.content);
					dataIn2 = {
						advertId: extraData,
						qaId: data.qaId
					};
					sendMail('advertAsk', dataIn2);
					break;
				case 'advertFree':
					dataIn2 = {
						advertId: extraData,
						qaId: data.qaId
					};
					$('#messages-list').html(data.content);
					sendMail('freeOffer', dataIn2);
					break;
				case 'advertSwap':
					dataIn2 = {
						advertId: extraData,
						qaId: data.qaId
					};
					$('#messages-list').html(data.content);
					sendMail('swapOffer', dataIn2);
					break;
				case 'advertQuestion':
					rbApp.callAction('q-and-a/none/0/');
					break;
				default:
					$('.top .nav-right .add').removeClass('active');
					$('#qa-list').html(data.content);
					$('.no-items').remove();
				}
				$('#question').val('');
				$('.question-new-form').slideUp();
				$('.bottom .add-button').removeClass('active');
			}
		});
	}


};
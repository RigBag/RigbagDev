RigBagMySport = function() {

};

RigBagMySport.prototype.click = function(obj) {

	var id = $(obj).attr('data-id');
	var action;

	if($(obj).hasClass('selected')) {
		if($('#sports-list li.selected').length == 1) {
			alert('You have to select at least one sport.');
			return false;
		}
		$(obj).removeClass('selected');
		action = 'remove';
	} else {
		$(obj).addClass('selected');
		action = 'add';
	}

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'settings/my-sport-update/',
		data: {
			id: id,
			action: action
		},
		cache: false,
		dataType: 'json',
		success: function(data) {

		}
	});
};

RigBagMySport.prototype.setFilled = function(obj) {


	$.ajax({
		type: 'POST',
		url: APP_PATH + 'settings/mysportsfilled/',
		data: {},
		cache: false,
		dataType: 'json',
		success: function(data) {
			setUrl('/signup/subscription/');
		}
	});
};
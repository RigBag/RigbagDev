RigBagCircle = function() {

};

RigBagCircle.prototype.remove = function(cId) {


	if(confirm('Are you sure?')) {
		$('#circle-' + cId).fadeOut();
		$.ajax({
			type: 'POST',
			url: APP_PATH + 'circles/delete/' + cId + '/',
			data: {},
			cache: false,
			dataType: 'json',
			success: function(data) {

			}
		});
	}

};

RigBagCircle.prototype.searchJoin = function() {
	rbApp.callAction('circles/join/search/?q=' + $('#key').val());
};

RigBagCircle.prototype.join = function(obj) {

	var id = $(obj).attr('data-id');

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'circles/add/' + id + '/',
		data: {},
		cache: false,
		dataType: 'json',
		success: function(data) {
			$(obj).fadeOut();
		}
	});


};
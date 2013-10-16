function sandboxSendPayment() {
	
	showLoader();
	
	var data	= {
					amount:		$('.f-amount').val(),
					currency:	$('.f-currency').val(),
					method:		$('.f-method').val(),
					type:		$('.f-type').val(),
					result:		$('.f-result').val(),
					ret:		$('.f-return').val(),
					retData:	$('.f-return-data').val()
				}

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'sandbox/payment/process/',
		data: data,
		cache: false,
		dataType: 'json',
		success: function( data )
		{
			hideLoader();
			if( $('.f-return').val() == 'subscription' ) {
				rbApp.callAction( 'profile/subscription/' );
			} else if( $('.f-return').val() == 'advert' ) {
				rbApp.callAction(  'advert/view/' + $('.f-return-data').val() + '/i/' );
			}
		}
	});
	
}
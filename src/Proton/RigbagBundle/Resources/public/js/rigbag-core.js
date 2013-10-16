var coreLoadingData		= false;
var coreRefreshInterval	= null;

function init() {
	rbApp.user.refreshAccount();
	startRefresh();
}


function startRefresh() {
	coreRefreshInterval	= setInterval( 'rbApp.user.refreshAccount()', 30000 );
}

function stopRefresh() {
	clearInterval( coreRefreshInterval );
}

function setUrl( url ) {
	var force	= false;
	if( url == rbApp.getHash() ) {
		force = true;
	}
	window.location.hash	= url;
	if( force ) {
		rbApp.getHash();
		rbApp.parseHash();
	}
}

// ACCOUNT: start

function userPanelMessageClick( msgId ) {
	$.ajax({
		type: 'POST',
		url: APP_PATH + 'user/panel/message/read/' + msgId + '/',
		data: {},
		cache: false,
		dataType: 'json',
		success: function( data )
		{
			rbApp.user.refreshAccount();
		}
	});
	$('#user-panel .popover').fadeOut( function() {
		$(this).removeClass( 'in' );
	});
}

// ACCOUNT: end

function observerSearch( selInput ) {}

// MAIL
function sendMail( type, data ) {

	var dataIn	= {
					type: type,
					data: data
				}

	$.ajax({
		type: 'POST',
		url: APP_PATH + 'mail/send/',
		data: dataIn,
		cache: false,
		dataType: 'json',
		success: function( data )
		{

		}
	});
}

function parseGMapPlace( data ) {

	var retData = {
		formated: null,
		countryCode: null,
		lat: null,
		lng: null
	}

	for( var a in data.address_components ) {
		for( var b in data.address_components[a].types ) {
			if( data.address_components[a].types[b] == 'country' ) {
				retData.countryCode = data.address_components[a].short_name;
			}
		}
	}

	retData.formated = data.formatted_address;
	retData.lat = data.geometry.location.Ya;
	retData.lng = data.geometry.location.Za;

	return retData;

}


function selectMenuElement( obj ) {

	var p = $(obj).parent().parent();

	$(p).children( 'li' ).removeClass( 'active' );
	$(obj).parent().addClass( 'active' );
}

function selectMenuElement2( obj ) {

	var p = $(obj).parent();

	$(p).children( 'li' ).removeClass( 'active' );
	$(obj).addClass( 'active' );
}



// WIDGETS

// Dropdown
function initDropdown( sel, valSel ) {

	var ul 	= $(sel).children( '.cont' ).children( 'ul' );
	var li 	= $(ul).children( 'li' );
	var val = $(sel).children( '.cont' ).children( '.value' );

	$(li).click( function() {

		$(li).removeClass( 'current' );
		$(this).addClass( 'current' );
		$(ul).slideUp( 'fast' );
		$(valSel).val( $(this).attr( 'data-id' ) );
		$(val).html( $(this).html() );
	});

	$(sel).hover(
		function() {
			$(ul).slideDown( 'fast' );
		},
		function() {
			$(ul).slideUp( 'fast' );
		}
	);

}

function initDropdown2( sel, valSel, callback ) {

	var ul 	= $(sel).children( '.cont' ).children( 'ul' );
	var li 	= $(ul).children( 'li' );
	var val = $(sel).children( '.cont' ).children( '.value' );

	$(li).click( function() {

		$(li).removeClass( 'current' );
		$(this).addClass( 'current' );
		$(ul).slideUp( 'fast' );
		$(valSel).val( $(this).attr( 'data-id' ) );
		$(val).html( $(this).html() );

		if( callback != undefined ) {
			eval( callback );
		}

	});

	$(sel).click(
		function() {
			if( $(this).children( '.cont' ).hasClass( 'opened' ) ) {
				$(ul).slideUp( 'fast' );
				$(this).children( '.cont' ).removeClass( 'opened' );
			} else {
				$(ul).slideDown( 'fast' );
				$(this).children( '.cont' ).addClass( 'opened' );
			}

		}
	);
}


// ADDS

function showLoader() {
	$('#main-loader').show();
}

function hideLoader() {
	$('#main-loader').hide();
}
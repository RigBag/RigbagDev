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
	retData.lat = data.geometry.location.ib;
	retData.lng = data.geometry.location.jb;
	
	return retData;
	
}
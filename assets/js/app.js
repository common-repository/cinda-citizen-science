/**
 * 
 */

jQuery(function($) {
	$( ".datepicker" ).datepicker({
		  dateFormat: "dd/mm/yy"
	});
	
	$('button[name=replace], input[type=button][name=replace]').on('click', function(){
		$(this).siblings('input[type=file]').click();
	});
	
	$("input[type=file]").change(function(){
		if($(this).parent().find('img').length == 0)
			$(this).parent().prepend('<img />')
		
	    readURL(this, $(this).parent().find('img'));
	});
	
	function readURL(input, img) {

	    if (input.files && input.files[0]) {
	        var reader = new FileReader();

	        reader.onload = function (e) {
	        	img.attr('src', e.target.result);
	        }

	        reader.readAsDataURL(input.files[0]);
	    }
	}
	
	if (google.maps){
		var maps = new Array();
		var markers = new Array();
		var i = 1;
		
		$('.map').each(function(){
			var map, marker, clickable;
			var id_map = $(this).attr('id');
			var actLat, actLong;
			var input_geoposition = $(this).siblings('input.geoposition');
			
			if($(this).hasClass('not-clickable'))
				clickable = false;
			else
				clickable = true;
			
			if(0 < input_geoposition.length && "" != input_geoposition.val()){
				actualizeLatLong();
			}else{
				setCurrentPosition();
			}
			
			var map = new google.maps.Map(document.getElementById(id_map), {
				center: {lat: actLat, lng: actLong},
				zoom: 18,
				zoomControl: true,
				mapTypeControl: false,
				scaleControl: false,
				streetViewControl: false,
				rotateControl: false
		    });
	
			marker = new google.maps.Marker({
			    position: {lat: actLat, lng: actLong},
			    map: map
			});
			
			if(clickable){
				google.maps.event.addListener(map, 'click', function( event ){
					var latlng = new google.maps.LatLng(event.latLng.lat(), event.latLng.lng());
					actualizePosition(latlng);
				});
			}
	
			input_geoposition.on('change', function(){
				actualizeLatLong();
				marker.setPosition( new google.maps.LatLng(actLat, actLong) );
			}); 
		
			function actualizePosition(latlng){
				marker.setPosition(latlng);
				input_geoposition.val( latlng.toString().replace('(','').replace(')','') ); //event.latLng.lat()+','+event.latLng.lng()
			}
	
			function actualizeLatLong(){
				var geoposition = input_geoposition.val().split(",");
				actLat = parseFloat( geoposition[0] );
				actLong = parseFloat( geoposition[1] );
			}
			
			function setCurrentPosition(){
				  actLat = 37.17673139677148;
				  actLong = -3.597249984741211;
			
			}
			
			maps[i] = map;
			markers[i] = marker;
			
			i++;
		});
	}

	function formatDictionaryTermList (data) {
		var text = data.text.split('[/]');
		var object = $('<span><strong>' + text[0] + '</strong><p style="font-size:0.8em;">'+ text[1] +'</p></span>');
		return object;
	};
	
	function formatDictionaryTermSelection(data, container){
		var text = data.text.split('[/]');
		return text[0];
	}
	$("select.dictionary").select2({
	  templateResult: formatDictionaryTermList,
	  templateSelection: formatDictionaryTermSelection,
	});
	
});




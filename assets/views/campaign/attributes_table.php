<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<table class="widefat fixed tabla">
	<tbody>
		<tr>
			<th style="width:30%"><?php _e('Logo Image','Cinda'); ?></th>
			<td  style="text-align:left;">
				<?php 
					if($image =  wp_get_attachment_url( get_post_meta(get_the_ID(), CINDA_PREFIX.'logo_image', true) ) ){
						echo '<a href="'. $image .'" target="_blank"><img src="'. $image .'" class="image-table" /></a><br />';
					}
				?>
				<input type="hidden" name="<?php echo CINDA_PREFIX; ?>logo_image" value="<?php echo get_post_meta(get_the_ID(), CINDA_PREFIX.'logo_image', true)?>" />
				<button type="button" class="button button-primary button-large logo_image"><i class="fa fa-camera"></i> <?php _e('Select Image','Cinda'); ?></button>
			</th>
		</tr>
		<tr>
			<th style="width:30%"><?php _e('Cover Image','Cinda'); ?></th>
			<td  style="text-align:left;">
				<?php 
					if($image =  wp_get_attachment_url( get_post_meta(get_the_ID(), CINDA_PREFIX.'cover_image', true) ) ){
						echo '<a href="'. $image .'" target="_blank"><img src="'. $image .'" class="image-table" /></a><br />';
					}
				?>
				<input type="hidden" name="<?php echo CINDA_PREFIX; ?>cover_image" value="<?php echo get_post_meta(get_the_ID(), CINDA_PREFIX.'cover_image', true)?>" />
				<button type="button" class="button button-primary button-large cover_image"><i class="fa fa-camera"></i> <?php _e('Select Image','Cinda'); ?></button>
			</th>
		</tr>
		<tr>
			<th><?php _e('Start date','Cinda'); ?><span class="required" title="Required">*</span></th>
			<td><input type="date" name="<?php echo CINDA_PREFIX; ?>start_date" value="<?php echo get_post_meta(get_the_ID(), CINDA_PREFIX.'start_date', true)?>" required="required"></td>
		</tr>
		<tr>
			<th><?php _e('End date','Cinda'); ?><span class="required" title="Required">*</span></th>
			<td><input type="date" name="<?php echo CINDA_PREFIX; ?>end_date" value="<?php echo get_post_meta(get_the_ID(), CINDA_PREFIX.'end_date', true)?>" required="required"></td>
		</tr>
		<tr>
			<th><?php _e('Geographical Scope','Cinda'); ?></th>
			<td><input type="text" name="<?php echo CINDA_PREFIX; ?>scope" value="<?php echo get_post_meta(get_the_ID(), CINDA_PREFIX.'scope', true)?>"></td>
		</tr>
		<tr>
			<th><?php _e('Geolocalization','Cinda'); ?></th>
			<td>
				<div id="map"></div>
				<input type="text" name="<?php echo CINDA_PREFIX; ?>geoposition" value="<?php echo $geoposition = get_post_meta(get_the_ID(), CINDA_PREFIX.'geoposition', true)?>">
			</td>
		</tr>
		<tr>
			<th><?php _e('Radium (Kms)','Cinda'); ?></th>
			<td><input type="number" min="0" name="<?php echo CINDA_PREFIX; ?>radium" value="<?php echo $radium = get_post_meta(get_the_ID(), CINDA_PREFIX.'radium', true)?>"></td>
		</tr>	
		<tr>
			<th><?php _e('Color','Cinda'); ?></th>
			<td><input class="<?php echo CINDA_PREFIX; ?>color" type="text" name="<?php echo CINDA_PREFIX; ?>color" value="<?php echo get_post_meta(get_the_ID(), CINDA_PREFIX.'color', true)?>"></td>
		</tr>
		<tr>
			<th><?php _e('Tracking','Cinda'); ?></th>
			<td style="text-align:left">
				<label><input type="radio" value="1" name="<?php echo CINDA_PREFIX; ?>tracking" class="<?php echo CINDA_PREFIX; ?>tracking" <?php if(get_post_meta(get_the_ID(), CINDA_PREFIX.'tracking', true)) echo "checked=\"true\""; ?> /> <?php _e('Yes','Cinda'); ?></label>
				<label><input type="radio" value="0" name="<?php echo CINDA_PREFIX; ?>tracking" class="<?php echo CINDA_PREFIX; ?>tracking" <?php if(!get_post_meta(get_the_ID(), CINDA_PREFIX.'tracking', true)) echo "checked=\"true\""; ?> /> <?php _e('No','Cinda'); ?></label>
			</td>
		</tr>
		<tr>
			<th><?php _e('Administrator email','Cinda'); ?></th>
			<td style="text-align: left;">
				<input class="<?php echo CINDA_PREFIX; ?>administrator" type="email" name="<?php echo CINDA_PREFIX; ?>administrator" value="<?php echo get_post_meta(get_the_ID(), CINDA_PREFIX.'administrator', true)?>">
				<span class="text-info"><?php _e('(Optional) In this field, you can specify the administrator email acount to send notifications about the campaign (New contributions, etc.).','Cinda'); ?></span>
			</td>
		</tr>
	</tbody>
</table>
<script>
(function( $ ) {
	// Add Color Picker to all inputs that have 'color-field' class
	$(function() {
		$.wp.wpColorPicker.prototype.options = {
			palettes: ['#E91E63','#673AB7','#2196F3','#00BCD4','#FF5722','#FF9800','#795548','#607D8B']
		};
		$('.cinda_color').wpColorPicker({hide: true});
	});

	var map;
	var marker;
	var radium;
	var saved = false;
	
	<?php 
	
	if( $geoposition ){
		$latlong = explode(',',str_replace(array('(',')'), "", $geoposition ) );
		echo "var actLat = " .$latlong[0].";";
		echo "var actLong = " .$latlong[1].";";
	}else{
		echo "var actLat = 0;";
		echo "var actLong = 0;";
	}
	
	if($radium){
		echo "var actRadium = " . ($radium * 1000) .";";
	}else{
		echo "var actRadium = 0;";
	}
	
	?>
	if (google.maps){
		map = new google.maps.Map(document.getElementById('map'), {
			center: {lat: actLat, lng: actLong},
			zoom: 8,
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
	
		radium = new google.maps.Circle({
	        center: {lat: actLat, lng: actLong},
	        radius: actRadium,
	        strokeColor: "#00a99d",
	        strokeOpacity: 0.7,
	        strokeWeight: 1,
	        fillColor: "#00a99d",
	        fillOpacity: 0.2,
	        map: map,
	    });
	
		google.maps.event.addListener(map, 'click', function( event ){
			var latlng = new google.maps.LatLng(event.latLng.lat(), event.latLng.lng());
			actualizePosition(latlng);
		});
	
		google.maps.event.addListener(radium, "click", function(event){
		    google.maps.event.trigger(map, 'click', event);
		});
		
		$('input[name="cinda_radium"]').on('change', function(){
			radium.setRadius( parseInt( $(this).val() ) * 1000 );
		});
	
		function actualizePosition(latlng){
			marker.setPosition(latlng);
		    radium.setCenter(marker.getPosition());
		    console.log(latlng);
		    $('input[name="cinda_geoposition"]').val( latlng.toString() ); //event.latLng.lat()+','+event.latLng.lng()
		}
	}
	
})( jQuery );
</script>
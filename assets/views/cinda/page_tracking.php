<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap tracking">
	<h1><?php echo sprintf( __('Tracking for <strong>%s</strong> campaign','Cinda'), $campaign_name); ?></h1>
	<h2><?php echo sprintf( __('By <strong>%s</strong> in <strong>%s</strong>','Cinda'), $author_name, $tracking->get_createDate()); ?></h2>
	<hr>
	<div id="map">
	
	</div>
</div>
<script>
	if(google.maps){
		var map = new google.maps.Map(document.getElementById('map'), {
			zoomControl: true,
			mapTypeControl: false,
			scaleControl: false,
			streetViewControl: false,
			rotateControl: false
	    });
		
		jQuery.ajax({
			type: "GET",
			url: '<?php echo $tracking->get_gpx_url(); ?>',
			dataType: "xml",
			success: function(xml) {
				var points = [];
				var bounds = new google.maps.LatLngBounds ();
				jQuery(xml).find("trkpt").each(function() {
					var lat = jQuery(this).attr("lat");
					var lon = jQuery(this).attr("lon");
					var p = new google.maps.LatLng(lat, lon);
					points.push(p);
					bounds.extend(p);
				});
				var poly = new google.maps.Polyline({
					// use your own style here
					path: points,
					strokeColor: "#FF00AA",
					strokeOpacity: .7,
					strokeWeight: 4
				});
				poly.setMap(map);
				// fit bounds to track
				map.fitBounds(bounds);
			}
		});
	}
</script>
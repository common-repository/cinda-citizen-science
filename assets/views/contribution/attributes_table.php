<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<table class="widefat fixed tabla">
	<tbody>
		<tr>
			<td style="width:20%"><?php _e('Campaign','Cinda'); ?></td>
			<td><a href="<?php echo $campaign_link; ?>" target="_blank"><?php echo $campaign_name; ?></a></td>
		</tr>
		<tr>
			<td><?php _e('Volunteer','Cinda'); ?></td>
			<td><a href="<?php echo $volunteer_link; ?>" target="_blank"><?php echo $volunteer_name; ?></a></td>
		</tr>
	
	<?php foreach ($contribution->get_model() as $field){ ?>
		<tr>
			<td><?php echo $field->field_label; ?></td>
			<td>
				<?php 
				if('text' == $field->field_type){
					echo '<input type="text" name="'.CINDA_PREFIX.$field->field_name.'" placeholder="'.$field->field_label.'" value="'. $contribution->data[$field->field_name] .'" ';
					if($field->field_required)
						echo "required";
						
					echo '>';
				}
				
				else if('textarea' == $field->field_type){
					echo '<textarea type="text" name="'.CINDA_PREFIX.$field->field_name.'" placeholder="'.$field->field_label.'" ';
					if($field->field_required)
						echo 'required';
					echo ' />'. $contribution->data[$field->field_name] .'</textarea>';
				}
				
				else if('number' == $field->field_type){
					echo '<input type="number" name="'.CINDA_PREFIX.$field->field_name.'" placeholder="'.$field->field_label.'" value="'. $contribution->data[$field->field_name] .'"';
						
					if($field->field_required)
						echo "required";
						
					echo '>';
				}
				
				else if('date' == $field->field_type){
					echo '<input type="date" name="'.CINDA_PREFIX.$field->field_name.'" placeholder="'.$field->field_label.'" value="'. $contribution->data[$field->field_name] .'"';
				
					if($field->field_required)
						echo "required";
				
					echo '>';
				}
				
				else if('datetime' == $field->field_type){
					echo '<input type="datetime" name="'.CINDA_PREFIX.$field->field_name.'" placeholder="'.$field->field_label.'" value="'. $contribution->data[$field->field_name] .'"';
				
					if($field->field_required)
						echo "required";
				
					echo '>';
				}
				
				else if('geopos' == $field->field_type){
					echo '<div class="map" id="map'.$maps.'"></div>';
					echo '<input type="text" class="geoposition" name="'.CINDA_PREFIX.$field->field_name.'" placeholder="'.$field->field_label.'" value="'. $contribution->data[$field->field_name] .'" pattern="^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$"';
				
					if(!$field->field_required)
						echo "required";
				
					echo '>';
					$maps++;
				}
				
				else if('image' == $field->field_type){
					if( isset($contribution->data[$field->field_name]) && !empty($contribution->data[$field->field_name])){
						echo '<a href="'. $contribution->data[$field->field_name] .'" target="_blank"><img src="'. $contribution->data[$field->field_name] .'" class="image-table" /></a><br />';
						echo '<button type="button" name="replace" class="button button-primary button-large"><i class="fa fa-camera"></i> Replace</button>';
					}else{
						echo '<button type="button" name="replace" class="button button-primary button-large"><i class="fa fa-camera"></i> Select Image</button>';
						
					}
					echo '<input type="hidden" name="'.CINDA_PREFIX.$field->field_name.'" value="" >';
				}
				
				else if('file' == $field->field_type){
					
				}
				
				else if('select' == $field->field_type){
					$cinda_options = explode("|",$field->field_options);
					echo '<select type="file" name="'.CINDA_PREFIX.$field->field_name.'"';
				
					if(!$field->field_required)
						echo "required";
				
					echo '>';
					echo "<option>".trim( $field->field_label )."</option>";
					if(count($cinda_options)>0){
						
						if(isset($contribution->data[ $field->field_name ]))
							$option_selected = trim($contribution->data[ $field->field_name ]);
						else
							$option_selected = "";
						
						
						foreach($cinda_options as $option){
							
							$option = trim($option);
							
							echo "<option ";
							if( $option == $option_selected )
								echo "selected";
							echo ">".$option."</option>";
								
						}
				
					}
					echo '</select>';
				}
				
				if($field->field_description != "")
					echo '<div class="description">'.$field->field_description."</div>";

				?>
			</td>
		</tr>
	<?php } ?>
	<?php 
		if($tracking_link){ ?>
			<tr>
				<td>Tracking:</td>
				<td><a class="button button-primary button-large" href="<?php echo $tracking_link; ?>" title="<?php _e('View Tracking', 'Cinda')?>" target="_blank"><i class="fa fa-map-o"></i> <?php _e('View Tracking', 'Cinda')?></a></td>
			</tr>
	<?php 
		} 
	?>
	</tbody>
</table>
<script>
(function( $ ) {
	if (google.maps){
		var maps = new Array();
		var markers = new Array();
		var i = 1;
		
		$('.map').each(function(){
			var map, marker;
			var id_map = $(this).attr('id');
			var actLat, actLong;
			var input_geoposition = $(this).siblings('input.geoposition');
			
			if(0 < input_geoposition.length){
				actualizeLatLong();
			}else{
				return;
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
			
			google.maps.event.addListener(map, 'click', function( event ){
				var latlng = new google.maps.LatLng(event.latLng.lat(), event.latLng.lng());
				actualizePosition(latlng);
			});
	
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
	
			maps[i] = map;
			markers[i] = marker;
			
			i++;
		});
	}
})( jQuery );
</script>
<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<table class="widefat fixed tabla">
	<tbody>
		<tr>
			<th style="width:20%"><?php _e('CSV File:','Cinda'); ?></th>
			<td>
				<input class="csv_input" type="hidden" name="<?php echo CINDA_PREFIX . "csv_file"; ?>" >
				<span class="csv_file">
				
				</span>
				<button type="button" name="upload-btn" id="upload-btn" class="button-secondary"><?php _e('Select','Cinda'); ?></button>
			</td>
		</tr>
		<tr>
			<th><?php _e('CSV Encoding:','Cinda'); ?></th>
			<td>UTF-8</td>
		</tr>
		<tr>
			<th><?php _e('CSV Format:','Cinda'); ?></th>
			<td>code,name,description<br />0123456,<?php _e('Term name','Cinda'); ?>,<?php _e('Term long description','Cinda'); ?></td>
		</tr>
	</tbody>
</table>

<script type="text/javascript">
jQuery(document).ready(function($){

	var wpmedia = wp.media({ 
        title: 'Select / Upload File',
        multiple: false
    });
	
    $('#upload-btn').click(function(e) {
        e.preventDefault();
        wpmedia
        .open()
        .on('select', function(e){
            // This will return the selected image from the Media Uploader, the result is an object
            var selected = wpmedia.state().get('selection').first();
            console.log(selected);
			$('input.csv_input').val( selected.attributes.id );
            $('.csv_file').html("<img src=\""+ selected.attributes.icon +"\" style=\"height:20px;\" /> " + selected.attributes.filename+' ');
        });
    });
});
</script>
<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<tr class="field new-field edit">

	<th class="field_position">
		<i class="fa fa-bars"></i>
	</th>
	
	<td class="field_label" colspan="6">
		<label><?php _e('New Field','Cinda')?></label>
		<div class="field_form">
			<?php include(CINDA_DIR . 'assets/views/campaign/field_table.php'); ?>
		</div>
	</td>
	
	<td class="field_name">
		<label>-</label>
	</td>
	
	<td class="field_type">
		<label>-</label>
	</td>
	
	<td class="field_description">
		<label>-</label>
	</td>
	
	<td class="field_buttons">
		<button type="button" class="edit"><i class="fa fa-pencil"></i></button> 
		<button class="delete"><i class="fa fa-trash-o"></i></button>
	</td>
	
</tr>
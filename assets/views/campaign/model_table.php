<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

global $data_types;
?>

<div class="row">
	<button class="addnew">
		<i class="fa fa-plus"></i> <?php _e('Add new field','Cinda') ?>
	</button>
</div>

<table id="model" class="widefat fixed tabla">
	<thead>
		<tr>
			<th><?php _e('Pos','Cinda'); ?></th>
			<th><?php _e('Label','Cinda'); ?></th>
			<th><?php _e('Name','Cinda'); ?></th>
			<th><?php _e('Field Type','Cinda'); ?></th>
			<th><?php _e('Description','Cinda'); ?></th>
			<th><?php _e('Edit','Cinda'); ?></th>
		</tr>
	</thead>
	
	<tbody>
	<?php if( count( $fields ) > 0  ): 
			foreach( $fields as $field ):  ?>
		<tr class="field field-<?php echo $field->id; ?>">
		
				<th class="field_position">
					<i class="fa fa-bars"></i>
				</th>
				
				<td class="field_label">
					<label><?php echo $field->field_label; ?><?php if( $field->field_required ): ?><span class="required" title="Required">*</span><?php endif; ?></label>
					<div class="field_form">
						<?php include(CINDA_DIR . 'assets/views/campaign/field_table.php'); ?>
					</div>
				</td>
				
				<td class="field_name">
					<label><?php echo $field->field_name; ?></label>
				</td>
				
				<td class="field_type">
					
					<label><?php echo ucfirst( $field->field_type ); //  $data_types[$field->field_type]; ?></label>
					
					
				</td>
				
				<td class="field_description">
					<label>
						<?php echo $field->field_description; ?>
						<span>
						<?php if($field->field_type == "dictionary"){ ?>
							(<?php echo get_the_title( $field->field_options ); ?>)
						<?php } ?>
						</span>
					
					</label>
					
				</td>
				
				<td class="field_buttons">
					<button type="button" class="edit"><i class="fa fa-pencil"></i></button> 
					<button class="delete"><i class="fa fa-trash-o"></i></button>
				</td>

		</tr>
		<?php endforeach;
		else:
		?>
		<tr class="nofields">
			<td style="text-align:center" colspan="6">
				<?php _e('No fields yet.','Cinda'); ?>  
				<button class="addnew"><i class="fa fa-plus"></i><?php _e('Add new','Cinda'); ?></button>
			</td>
		</tr>
		<?php 
		endif; ?>
	</tbody>
</table>
<div class="row">
	<button class="addnew">
		<i class="fa fa-plus"></i> <?php _e('Add new field','Cinda') ?>
	</button>
</div>
<div class="row text-right">
	<a href="#" class="discard discardall"><?php _e('Discard All Changes','Cinda'); ?></a> 
	<button class="save saveall">
		<i class="fa fa-floppy-o"></i><?php _e('Save All Changes','Cinda') ?>
	</button>
</div>
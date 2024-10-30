<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if(isset($field))
	$id = $field->field_position;
else
	$id = '';
	
$dictionaries = \cinda\API\Dictionary::get_list();
?>
<table class="widefat fixed tabla field">
	<input class="field_id" name="field[<?php echo $id; ?>][field_id]" value="<?php if(isset($field)) echo $field->id; elseif(isset($id_campaign)) echo $id_campaign; ?>" type="hidden">
	<?php // INITIAL VALUES FOR CHANGES ?>
	<input class="field_json" name="field[<?php echo $id; ?>][field_json]" value='<?php if(isset($field)) echo json_encode($field); ?>' type="hidden">

	<thead>
		<tr>
			<th>Name</th>
			<th>Input</th>
		</tr>
	</thead>
	
	<tbody>
		<tr class="position">
			<td><?php _e('Position','Cinda'); ?></td>
			<td>
				<input class="field_position" name="field[<?php echo $id; ?>][position]" type="number" value="<?php if(isset($field)) echo $field->field_position; ?>" readonly>
				<span class="error"></span>
			</td>
		</tr>
		<tr class="label">
			<td><?php _e('Label','Cinda'); ?><span class="required" title="Required">*</span></td>
			<td>
				<input class="field_label" name="field[<?php echo $id; ?>][label]" type="text" value="<?php if(isset($field)) echo $field->field_label; ?>">
				<span class="error"></span>
			</td>
		</tr>
		<tr class="name">
			<td><?php _e('Name','Cinda'); ?><span class="required" title="Required">*</span></td>
			<td>
				<input class="field_name" name="field[<?php echo $id; ?>][name]" type="text" value="<?php if(isset($field)) echo $field->field_name; ?>" title="<?php _e('This field is automatically filled','Cinda')?>">
				<span class="error"></span>
			</td>
		</tr>
		<tr class="required">
			<td><?php _e('Required','Cinda'); ?></td>
			<td>
				<input class="field_required" name="field[<?php echo $id; ?>][required]" type="checkbox" <?php if(isset($field)) if($field->field_required): echo 'checked'; endif; ?>>
				<span class="error"></span>
			</td>
		</tr>
		<tr class="type">
			<td><?php _e('Type','Cinda'); ?><span class="required" title="Required">*</span></td>
			<td>
				<select class="field_type" name="field[<?php echo $id; ?>][type]">
					<option value=""><?php _e('Select','Cinda'); ?></option>
					<?php global $data_types; foreach($data_types as $type => $data): ?>
						<option value="<?php echo $type; ?>" <?php if(isset($field)) if($field->field_type == $type): echo 'selected'; endif;?>><?php echo $data ?></option>
					<?php endforeach;?>
				</select>
				<span class="error"></span>
			</td>
		</tr>
		<tr class="dictionaries" <?php echo (isset($field) && $field->field_type == "dictionary") ? " style=\"display:table-row;\"" : ""; ?>>
			<td><?php _e('Dictionary','Cinda'); ?><span class="required" title="Required">*</span></td>
			<td>
				<select class="field_dictionary" name="field[<?php echo $id; ?>][dictionary]">
					<option value=""><?php _e('Select a dictionary','Cinda'); ?></option>
					<?php 
					if(0 < count($dictionaries)){
						foreach($dictionaries as $dictionary){
					?>
						<option value="<?php echo $dictionary['id'];?>" <?php echo (intval($field->field_options) == $dictionary['id']) ? "selected=\"selected\"" : ""; ?>><?php echo $dictionary['name']; ?></option>
					<?php }
					} ?>
				</select>
			</td>
		</tr>
		<tr class="description">
			<td><?php _e('Description','Cinda'); ?></td>
			<td>
				<textarea class="field_description" name="field[<?php echo $id; ?>][description]"><?php if(isset($field)) echo $field->field_description; ?></textarea>
				<span class="error"></span>
			</td>
		</tr>
		<tr class="options">
			<td><?php _e('Options','Cinda'); ?><span class="required" title="Required">*</span></td>
			<td>
				<textarea class="field_options" name="field[<?php echo $id; ?>][options]" placeholder="<?php _e('Option 1 | Option 2 | ...','Cinda'); ?>"><?php if(isset($field)) echo $field->field_options; ?></textarea>
				<span><?php _e('Separate the options with |','Cinda'); ?></span>
				<span class="error"></span>
			</td>
		</tr>
		<tr class="buttons">
			<td colspan="2">
				<a href="#" class="discard"><?php  if(isset($field)) _e('Reset','Cinda'); else _e('Cancel','Cinda'); ?></a> 
				<button class="save"><?php _e('Accept','Cinda') ?></button> 
				<button class="delete"><i class="fa fa-trash-o"></i></button>
			</td>
		</tr>
	</tbody>
</table>
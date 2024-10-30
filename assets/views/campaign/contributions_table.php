<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<table id="contributions" class="widefat fixed tabla">
	<thead>
		<tr>
			<th><?php _e('ID','Cinda')?></th>
			<th><?php _e('Author','Cinda')?></th>
			<th><?php _e('Date','Cinda')?></th>
			<th><?php _e('Edit','Cinda')?></th>
		</tr>
	</thead>
	
	<tbody>
		<?php 
		if( 0 < count($contributions) ){
			foreach($contributions as $contribution){
				$edit_url = get_admin_url() ."post.php?post=". $contribution->ID."&action=edit";
			?>
				<tr>
					<td><?php echo $contribution->ID; ?></td>
					<td><?php echo $contribution->data['author_name']; ?></td>
					<td><?php echo $contribution->data['create_date']; ?></td>
					<td class="field_buttons">
						<a href="<?php echo $edit_url; ?>"><button type="button" class="edit"><i class="fa fa-pencil"></i></button></a>
						<button class="delete" data-id="<?php echo $contribution->ID; ?>"><i class="fa fa-trash-o"></i></button>
					</td>
				</tr>
			<?php 
			}
		}else{ ?>
		<tr>
			<td colspan="4" class="text-center"><?php _e('No contributions found.','Cinda')?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>
<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
 
$csv_file = get_post_meta(get_the_ID(),CINDA_PREFIX."csv_file",true);
$csv_date = get_post_meta(get_the_ID(),CINDA_PREFIX."csv_update",true);
$csv_url = wp_get_attachment_url($csv_file);
$terms = get_post_meta(get_the_ID(),CINDA_PREFIX."terms",true);

if(is_array($terms) && 0 < count($terms)){;
?>
<table class="widefat fixed tabla">
	<thead>
		<tr>
			<th colspan="4"><h2><?php _e('File Imported:','Cinda'); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th style="width:20%;"><?php _e('Name:','Cinda'); ?></th>
			<td colspan="3"><?php echo get_the_title($csv_file); ?></td>
		</tr>
		<tr>
			<th><?php _e('Upload at:','Cinda'); ?></th>
			<td colspan="3"><?php echo $csv_date; ?></td>
		</tr>
		<tr>
			<th><?php _e('File:','Cinda'); ?></th>
			<td colspan="3"><a href="<?php echo $csv_url; ?>" target="_blank"><?php _e('Download','Cinda'); ?></a></td>
		</tr>
	</tbody>
</table>
<table class="widefat fixed tabla">
	<thead>
		<tr>
			<th colspan="3"><h2><?php _e('Terms:','Cinda'); ?></h2></th>
		</tr>
		
		<tr>
			<th style="width:20%"><?php _e('Code:','Cinda'); ?></th>
			<th><?php _e('Name:','Cinda'); ?></th>
			<th><?php _e('Description:','Cinda'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php 
		foreach($terms as $term){
		?>
		<tr>
			<td style="width:20%"><?php echo $term['code']; ?></td>
			<td><?php echo $term['name']; ?></td>
			<td><?php echo (strlen($term['description']) > 50) ? substr($term['description'], 0, 50)."..." : $term['description']; ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>
<?php }else{ ?>
<?php _e('Doesn´t has imported csv file yet','Cinda'); ?>
<?php } ?>
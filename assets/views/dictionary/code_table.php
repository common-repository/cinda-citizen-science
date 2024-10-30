<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<table class="widefat fixed tabla">
	<tbody>
		<tr>
			<th style="width:20%"><?php _e('Code:','Cinda'); ?></th>
			<td><input type="text" name="<?php echo CINDA_PREFIX. "code"; ?>" value="<?php echo get_post_meta(get_the_ID(), CINDA_PREFIX.'code', true)?>" ></td>
		</tr>
	</tbody>
</table>
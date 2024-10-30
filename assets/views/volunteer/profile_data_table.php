<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<table class="widefat fixed tabla">
	<thead>
		<tr>
			<th style="width:30%"><?php _e('Field','Cinda'); ?></th>
			<th><?php _e('Value','Cinda'); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php _e('Nickname','Cinda'); ?></td>
			<td><input type="text" name="<?php echo CINDA_PREFIX; ?>nickname" value="<?php echo isset($fields[CINDA_PREFIX.'nickname']) ? $fields[CINDA_PREFIX.'nickname'][0] : ""; ?>"></td>
		</tr>
		<tr>
			<td><?php _e('Email','Cinda'); ?></td>
			<td><input type="email" name="<?php echo CINDA_PREFIX; ?>email" value="<?php echo isset($fields[CINDA_PREFIX.'email']) ? $fields[CINDA_PREFIX.'email'][0] : ""; ?>"></td>
		</tr>
		<tr>
			<td><?php _e('Device ID','Cinda'); ?></td>
			<td><input type="text" name="<?php echo CINDA_PREFIX; ?>device_id" readonly value="<?php echo isset($fields[CINDA_PREFIX.'device_id']) ? $fields[CINDA_PREFIX.'device_id'][0] : ""; ?>"></td>
		</tr>
		<tr>
			<td><?php _e('Avatar','Cinda'); ?></td>
			<td><?php if(isset($fields[CINDA_PREFIX.'avatar_url'])){ ?><img src="<?php echo $fields[CINDA_PREFIX.'avatar_url'][0]; ?>"  style="border-radius:50%;"/><?php } ?></td>
		</tr>
		<?php if(cinda_use_wp_users()): ?>
			<tr>
				<td><?php _e('Wordpress user','Cinda'); ?></td>
				<td>
					<select name="<?php echo CINDA_PREFIX; ?>wp_user_id">
						<option value=""><?php _e('Choose an option','Cinda'); ?></option>
						<?php foreach(get_users( array() ) as $user){ ?>
							<option value="<?php echo $user->ID; ?>" <?php if(isset($fields[CINDA_PREFIX.'wp_user_id']) && $fields[CINDA_PREFIX.'wp_user_id'][0] == $user->ID ) echo 'selected="selected"'; ?>><?php echo $user->user_login; ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>

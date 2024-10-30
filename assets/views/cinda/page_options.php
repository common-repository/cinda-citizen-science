<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Save OPTIONS
if( !empty($_POST) ){
	global $CINDA;
	if($CINDA->save_options_old()){
		$message = __('Options updated.','Cinda');
	}
}
global $CINDA;

?>

<h1><?php echo $CINDA->get_options('server')['name'] .": ". __('Plugin configuration.','Cinda')?></h1>

<?php
if( isset($message) ){ ?>
<div id="message" class="updated notice notice-success is-dismissible below-h2">
	<p><?php echo $message; ?></p>
</div>
<?php } ?>

<div class="page-content">
	<form method="post">

		<h2><?php _e('General options:','Cinda')?></h2>


		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e('Server Name','Cinda'); ?></th>
					<td><input type="text" name="<?php echo CINDA_PREFIX; ?>server_name" value="<?php echo ($CINDA->get_options('server')['name']) ? $CINDA->get_options('server')['name'] : ''; ?>" required="required" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Server Description','Cinda'); ?></th>
					<td><textarea name="<?php echo CINDA_PREFIX; ?>server_description" required="required" style="width:25em; height:150px;"><?php echo ($CINDA->get_options('server')['description']) ? $CINDA->get_options('server')['description'] : ''; ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Public URL','Cinda'); ?></th>
					<td><input type="url" name="<?php echo CINDA_PREFIX; ?>server_url" value="<?php echo ($CINDA->get_options('server')['url']) ? $CINDA->get_options('server')['url'] : ''; ?>" class="regular-text"></td>
				</tr>
			</tbody>
		</table>

		<h2><?php _e('Google Maps options:','Cinda')?></h2>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e('API KEY','Cinda'); ?></th>
					<td><input type="text" name="<?php echo CINDA_PREFIX; ?>gmap_API" value="<?php echo ($CINDA->get_options('gmaps')['api']) ? $CINDA->get_options('gmaps')['api'] : ''; ?>" class="regular-text"></td>
				</tr>
			</tbody>
		</table>

		<h2><?php _e('Notification options:','Cinda'); ?></h2>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e('Platform','Cinda'); ?></th>
					<td>
						<select name="<?php echo CINDA_PREFIX; ?>notification_platform">
							<option selected value="parse">Parse.com</option>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e('URL','Cinda'); ?></th>
					<td><input type="text" name="<?php echo CINDA_PREFIX; ?>notification_parse_url" value="<?php echo (isset($CINDA->get_options('notification')['parse']['url'])) ? $CINDA->get_options('notification')['parse']['url'] : ''; ?>" class="regular-text"></td>
				</tr>

				<tr>
					<th scope="row"><?php _e('Application Id','Cinda'); ?></th>
					<td><input type="text" name="<?php echo CINDA_PREFIX; ?>notification_parse_app_id" value="<?php echo (isset($CINDA->get_options('notification')['parse']['app_id'])) ? $CINDA->get_options('notification')['parse']['app_id'] : ''; ?>" class="regular-text"></td>
				</tr>

				<tr>
					<th scope="row"><?php _e('REST API Key','Cinda'); ?></th>
					<td><input type="text" name="<?php echo CINDA_PREFIX; ?>notification_parse_app_key" value="<?php echo (isset($CINDA->get_options('notification')['parse']['app_key'])) ? $CINDA->get_options('notification')['parse']['app_key'] : ''; ?>" class="regular-text"></td>
				</tr>

				<tr>
					<th scope="row"><?php _e('Client API Key','Cinda'); ?></th>
					<td><input type="text" name="<?php echo CINDA_PREFIX; ?>notification_parse_client_key" value="<?php echo (isset($CINDA->get_options('notification')['parse']['client_key'])) ? $CINDA->get_options('notification')['parse']['client_key'] : ''; ?>" class="regular-text"></td>
				</tr>

			</tbody>
		</table>

		<h2><?php _e('Others options:','Cinda'); ?></h2>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e('Use WordPress Users','Cinda'); ?></th>
					<td>
						<?php $is_selected = (isset($CINDA->get_options('others')['wp_users']) && $CINDA->get_options('others')['wp_users']) ? 1 : 0; ?>
						<label><input type="radio" name="<?php echo CINDA_PREFIX."wp_users"; ?>" value="1" <?php  echo $is_selected ? 'checked="checked"' : ''; ?> /> <?php _e('Yes','Cinda'); ?></label> |
						<label><input type="radio" name="<?php echo CINDA_PREFIX."wp_users"; ?>" value="0" <?php  echo !$is_selected ? 'checked="checked"' : ''; ?> /> <?php _e('No','Cinda'); ?></label>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<button class="button button-primary"><i class="fa fa-save"></i> <?php _e('Save changes','Cinda'); ?></button>
		</p>
	</form>
</div>

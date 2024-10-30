<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

cindaApp()->get_header();

$CindaAPP = cindaApp();
?>

<div id="login" class="box">
	<form method="post">
		<div class="logo">
			<img src="/wp-content/plugins/cinda-citizen-science/assets/images/logo_cinda.png" />
		</div>
		<input type="hidden" name="token" value="<?php echo $CindaAPP->getToken(); ?>" />
		<input type="hidden" name="nonce" value="<?php echo $CindaAPP->getNonce(); ?>" />
		<div class="field">
			<label>
				<?php _e('Email','Cinda'); ?>
				<input type="email" name="email" />
			</label>
		</div>
		<div class="field">
			<label>
				<?php _e('Password','Cinda'); ?>
				<input type="password" name="password" />
			</label>
		</div>
		<div class="field">
			<input type="submit" name="submit" value="<?php _e('Login','Cinda'); ?>" class="button" />
		</div>
		<div class="field">
			<h2><?php _e('How I can receive the login credentials?','Cinda'); ?></h2>
			<ol>
				<li><?php echo sprintf( __('Download <a href="%s">CINDA - Citizen Science</a> from Google Play','Cinda'), 'https://play.google.com/store/apps/details?id=info.si2.iista.volunteernetworks'); ?></li> 
				<li><?php _e('Login in','Cinda'); ?></li>
				<li><?php echo sprintf( __('Click on %s button','Cinda'), '<img src="/wp-content/plugins/cinda-citizen-science/assets/images/button-settings.png" width="25" alt="'.__('Settings','Cinda').'" />'); ?> <img src="/wp-content/plugins/cinda-citizen-science/assets/images/login1.png" alt="<?php _e('Screenshot 1','Cinda') ?>" /></li>
				<li><?php _e('Click on "Solicitar clave web"','Cinda'); ?> <img src="/wp-content/plugins/cinda-citizen-science/assets/images/login2.png" alt="<?php _e('Screenshot 2','Cinda') ?>" /></li>
				<li><?php _e('Click on "Enviar solicitud de clave web" button.','Cinda'); ?></li>
				<li><?php _e('Check your email\'s inbox','Cinda'); ?></li>
			</ol>
		</div>
	</form>
</div>

<?php 
cindaApp()->get_footer();
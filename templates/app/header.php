<?php
/**
 * @package Cinda
 * @since Cinda 1.1.3
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width">
		<link rel="profile" href="http://gmpg.org/xfn/11">
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
		<title><?php echo cindaApp()->getTitle(); ?></title>
		<?php wp_head(); ?>
	</head>
	
	<body <?php body_class(); ?>>
	
		<div class="row align-center">	
			<div  class="small-10 medium-6 row align-center">
				
				<?php if($this->is_logged_in):?>
					<header id="header" class="box">
						<div class="logo"><a href="<?php echo cindaApp()->getUrl(); ?>"><img src="<?php echo CINDA_URL ."assets/images/logo_cinda.png"?>" title="<?php _e('CINDA: Volunteers Network','Cinda'); ?>" /></a></div>
						<div class="actions">
							<a href="<?php echo cindaApp()->getUrl(); ?>" title="<?php _e('Home','Cinda'); ?>"><i class="fa fa-home" aria-hidden="true"></i></a>
							<a href="<?php echo cindaApp()->getUrl('profile'); ?>" title="<?php _e('Profile','Cinda'); ?>"><i class="fa fa-user" aria-hidden="true"></i></a>
							<a href="<?php echo cindaApp()->getUrl('logout'); ?>" title="<?php _e('Logout','Cinda'); ?>"><i class="fa fa-power-off" aria-hidden="true"></i></a>
						</div>
					</header>
				<?php endif; ?>
		
		
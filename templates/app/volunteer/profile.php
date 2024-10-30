<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

cindaApp()->get_header();
$result = cindaApp()->getResult();
?>

<div id="profile" class="box">
	<header>
		<div class="image" style="background-image: url( <?php echo cindaApp()->getVolunteer()->avatar ?> );"></div>
		<div class="content row align-center">
			<div class="small-6 medium-3">
				<img class="avatar" src="<?php echo cindaApp()->getVolunteer()->avatar ?>" />
				<div>
					<?php echo cindaApp()->getVolunteer()->nickname; ?><br />
					<span>
						<?php 
							$num_contributions = count($result['contributions']); 
							echo sprintf(_n('%s contribution','%s contributions', $num_contributions, 'Cinda'), $num_contributions);
						?>
					</span>
					<a href="<?php echo cindaApp()->getUrl('logout'); ?>"><?php _e('Logout', 'Cinda')?></a>
				</div>
			</div>
		</div>
	</header>
	
	<ul class="tabs" data-tabs id="activity-tabs" >
		<li class="tabs-title is-active small-6"><a href="#contributions" aria-selected="true"><?php _e('Contributions','Cinda'); ?></a></li>
		<?php /* <li class="tabs-title small-6"><a href="#routes"><?php _e('Routes','Cinda'); ?></a></li>*/ ?>
	</ul>
	<div class="tabs-content" data-tabs-content="activity-tabs">
		<div class="tabs-panel is-active" id="contributions">
			<?php 
			if( isset($result['contributions'])  && 0 < count($result['contributions'])):
				foreach($result['contributions'] as $contribution): ?>
					<article class="contribution" id="contribution-<?php echo $contribution->ID; ?>">
						<div class="small-12 medium-4 large-3 columns">
							<img src="<?php echo $contribution->campaign_image; ?>" />
						</div>
						<div class="small-12 medium-8 large-9 columns">
							<a href="<?php echo cindaApp()->getUrl('contribution',$contribution->ID); ?>" class="button right" title="<?php _e('View'); ?>"><i class="fa fa-eye"></i></a>
							<h2><a href="<?php echo cindaApp()->getUrl('contribution',$contribution->ID); ?>"><?php echo $contribution->campaign_name; ?></a></h2> 
							<span class="date"><?php echo date_i18n( 'l, j F Y' , strtotime( $contribution->create_date ) ); ?></span>
							<p><?php echo $contribution->description; ?></p>
						</div>
					</article>
					<hr>
			<?php endforeach;
			endif; ?>
			<br />
		</div>
		<div class="tabs-panel" id="routes">
			RUTAS
		</div>
	</div>
</div>

<?php 
cindaApp()->get_footer();
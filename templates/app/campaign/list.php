<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
$cindaApp = cindaApp();
$cindaApp->get_header();
$campaigns = cindaApp()->getResult()->get_campaigns();
?>

<div id="campaigns" class="box">
	<?php if(0 < count( $campaigns )): ?>
		<?php foreach ($campaigns as $campaign): ?>
		
			<div id="campaign-<?php echo $campaign->ID; ?>" class="campaign">
				<header style="background: url(<?php echo $campaign->cover; ?>)">
					<div class="content centered">
						<h2><?php echo $campaign->title; ?></h2>
					</div>
					<a href="<?php echo $cindaApp->getUrl('campaign',$campaign->ID); ?>" title="<?php echo sprintf(__('View campaign %s','Cinda'),$campaign->title); ?>" class="go" style="background-color: <?php echo $campaign->color; ?>;"><i class="fa fa-angle-right" aria-hidden="true"></i></a>
					<div class="volunteers-top">
						<?php if(0 < count( $campaign->volunteers_top)) foreach($campaign->volunteers_top as $volunteer): ?>
							<div class="item volunteer"><img src="<?php echo $volunteer['avatar']; ?>" title="<?php echo $volunteer['nickname']; ?>" /></div>
						<?php endforeach; ?>
					</div>
				</header>
				<div>
					<?php echo $campaign->description; ?>
				</div>
				<div class="actions">
					<a href="<?php echo $cindaApp->getUrl('campaign',$campaign->ID); ?>" class="button success"><?php _e('View Campaign','Cinda'); ?></a>
					<?php if($campaign->is_subscribed):?>
						<a href="<?php echo $cindaApp->getUrl('campaign', $campaign->ID, 'unsuscribe', array('redirect'=> cindaApp()->getUrl('campaigns') )); ?>" class="button alert"><?php _e('Unsuscribe','Cinda'); ?></a>
					<?php else:?>
						<a href="<?php echo $cindaApp->getUrl('campaign', $campaign->ID, 'suscribe', array('redirect'=> cindaApp()->getUrl('campaigns') )); ?>" class="button"><?php _e('Suscribe','Cinda'); ?></a>
					<?php endif;?>
				</div>
			</div>
		
		<?php endforeach;?>
	<?php endif ?>
</div>

<?php 
$cindaApp->get_footer();
<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
$campaign = cindaApp()->getResult();
cindaApp()->get_header();
?>

<div id="campaign-<? echo $campaign->ID; ?>" class="campaign box">
	<header style="background: url(<?php echo $campaign->cover; ?>)">
		<div class="content centered">
			<h2><?php echo $campaign->title; ?></h2>
		</div>
		<div class="volunteers-top">
			<?php if(0 < count( $campaign->volunteers_top)) foreach($campaign->volunteers_top as $volunteer): ?>
				<div class="item volunteer"><img src="<?php echo $volunteer['avatar']; ?>" title="<?php echo $volunteer['nickname']; ?>" /></div>
			<?php endforeach; ?>
		</div>
	</header>
	
	<div class="field">
		<div class="small-12"><?php echo $campaign->description; ?></div>
	</div>
	
	<div class="field">
		<div class="small-4 medium-3 columns"><?php _e('Start Date','Cinda')?></div>
		<div class="small-8 medium-9 columns"><?php echo date_i18n('l, d M Y',strtotime($campaign->date_start)); ?></div>
	</div>
	
	<div class="field">
		<div class="small-4 medium-3 columns"><?php _e('End Date','Cinda')?></div>
		<div class="small-8 medium-9 columns"><?php echo date_i18n('l, d M Y',strtotime($campaign->date_end)); ?></div>
	</div>
	
	<div class="field actions">
		
		<a href="<?php echo cindaApp()->getUrl('campaign', $campaign->ID, 'sendData'); ?>" class="button success"><?php _e('Send Contribution','Cinda'); ?></a>
	
		<?php if($campaign->is_subscribed):?>
			<a href="<?php echo cindaApp()->getUrl('campaign', $campaign->ID, 'unsuscribe', array('redirect'=> cindaApp()->getUrl('campaign', $campaign->ID) )); ?>" class="button alert"><?php _e('Unsuscribe','Cinda'); ?></a>
		<?php else:?>
			<a href="<?php echo cindaApp()->getUrl('campaign', $campaign->ID, 'suscribe', array('redirect'=> cindaApp()->getUrl('campaign', $campaign->ID) )); ?>" class="button"><?php _e('Suscribe','Cinda'); ?></a>
		<?php endif;?>
		
		
	</div>
	
	<h3><?php _e('Contributions', 'Cinda'); ?></h3>
	<ul class="tabs" data-tabs id="activity-tabs" >
		<li class="tabs-title is-active small-6"><a href="#own-contributions" aria-selected="true"><?php _e('Own','Cinda'); ?></a></li>
		<li class="tabs-title small-6"><a href="#all-contributions"><?php _e('Others','Cinda'); ?></a></li>
	</ul>
	<div class="tabs-content" data-tabs-content="activity-tabs">
		<div class="tabs-panel is-active contributions" id="own-contributions">
			<?php 
				if( count( $campaign->get_contributions() ) ):
					$contributions = 0;
					foreach($campaign->get_contributions() as $contribution):
						if(cindaApp()->getVolunteer()->get_id() == $contribution->author_id ): $contributions++; ?>
						<article class="contribution" id="contribution-<?php echo $contribution->ID; ?>">
							<div class="small-12 medium-4 large-3 columns">
								<img class="avatar" src="<?php echo $contribution->data['author_image']; ?>" />
							</div>
							<div class="small-12 medium-8 large-9 columns">
								<a href="<?php echo cindaApp()->getUrl('contribution', $contribution->ID); ?>" class="button right" title="<?php _e('View'); ?>"><i class="fa fa-eye"></i></a>
								<h2><a href="<?php echo cindaApp()->getUrl('contribution',$contribution->ID); ?>"><?php echo $contribution->author_name; ?></a></h2> 
								<span class="date"><?php echo date_i18n( 'l, j F Y' , strtotime( $contribution->create_date ) ); ?></span>
								<p><?php echo $contribution->description; ?></p>
							</div>
						</article>
					<?php endif; endforeach; 
					if(!$contributions){
						?>
							<div><?php _e('You have not published contributions yet','Cinda'); ?></div>
						<?php 
					}
				endif; ?>
		</div>
		<div class="tabs-panel contributions" id="all-contributions">
			<?php 
				if( count( $campaign->get_contributions() ) ):
					$contributions = 0;
					foreach($campaign->get_contributions() as $contribution): if(cindaApp()->getVolunteer()->get_id() != $contribution->author_id ): $contributions++; ?>
						<article class="contribution" id="contribution-<?php echo $contribution->ID; ?>">
							<div class="small-12 medium-4 large-3 columns">
								<img class="avatar" src="<?php echo $contribution->data['author_image']; ?>" />
							</div>
							<div class="small-12 medium-8 large-9 columns">
								<a href="<?php echo cindaApp()->getUrl('contribution', $contribution->ID); ?>" class="button right" title="<?php _e('View'); ?>"><i class="fa fa-eye"></i></a>
								<h2><a href="<?php echo cindaApp()->getUrl('contribution',$contribution->ID); ?>"><?php echo $contribution->author_name; ?></a></h2> 
								<span class="date"><?php echo date_i18n( 'l, j F Y' , strtotime( $contribution->create_date ) ); ?></span>
								<p><?php echo $contribution->description; ?></p>
							</div>
						</article>
					<?php endif; endforeach;
					if(!$contributions){
						?>
							<div><?php _e('No contributions found','Cinda'); ?></div>
						<?php 
					}
				endif; ?>
		</div>
	</div>
	
	<h3><?php _e('Actions', 'Cinda')?></h3>
	<div>
		<a class="button secondary" href="javascript: window.history.back();"><i class="fa fa-arrow-left"></i> <?php _e('Back','Cinda'); ?></a> 
	</div>
</div>

<?php 
cindaApp()->get_footer();
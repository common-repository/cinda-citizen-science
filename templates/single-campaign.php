<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Template for Single Campaign
 *
 * If you want edit this page template, copy it in themes/mythemename/cinda/templates/
 *
 */
get_header('cinda');

global $campaign;

//print_r($campaign);

?>
<article class="cinda campaign-<?php echo $campaign->ID; ?>">
	<div class="cinda-campaign-cover" style="background-image: url(<?php echo $campaign->cover; ?>)">
		<div class="cinda-campaign-color" style="background-color:<?php echo $campaign->color; ?>;"></div>
		<div class="cinda-campaign-image">
			<img src="<?php echo $campaign->image; ?>" alt="<?php echo $campaign->title; ?>" />
		</div>
		<div class="cinda-campaign-title">
			<h1><?php echo $campaign->title; ?></h1>
		</div>
	</div>
	<div class="cinda-campaign-description">
		<h2><?php _e('Description', 'Cinda')?></h2>
		<?php echo $campaign->description; ?>
	</div>
	<div class="cinda-campaign-data">
		<div class="tr">
			<div class="th"><?php _e('Start date', 'Cinda'); ?>:</div>
			<div class="td"><?php echo $campaign->date_start; ?></div>
		</div>
		
		<div class="tr">
			<div class="th"><?php _e('End date', 'Cinda'); ?>:</div>
			<div class="td"><?php echo $campaign->date_end; ?></div>
		</div>
		
		<div class="tr">
			<div class="th"><?php _e('Geographical Scope', 'Cinda'); ?>:</div>
			<div class="td"><?php echo $campaign->scope; ?></div>
		</div>
		
		<div class="tr">
			<div class="th"><?php _e('Volunteers subscribed', 'Cinda'); ?>:</div>
			<div class="td"><?php echo $campaign->suscriptions; ?></div>
		</div>
	</div>
	<hr >
	<div class="cinda-contributions">
		<h2><?php echo sprintf( _n( '%s Contribution', '%s Contributions', $campaign->contributions_number, 'Cinda' ), $campaign->contributions_number); ?></h2>
		
		<?php 
		$contributions = $campaign->get_contributions('serialized');
		if(0 < count($contributions)){ 
			foreach($contributions as $contribution) { ?>
				<div class="cinda-contribution">
					<div class="cinda-contribution-image">
						<a href="<?php echo get_permalink($contribution['id'])?>">
							<img src="<?php echo $contribution['image']; ?>" alt="<?php echo $contribution['author_name']; ?>" />
						</a>
					</div>
					<div class="cinda-contribution-data">
						<h3><a href="<?php echo get_permalink($contribution['id'])?>"><?php echo $contribution['author_name']; ?></a></h3>
						<span class="cinda-contribution-date"><?php echo $contribution['create_date']; ?></span>
					</div>
					<div class="cinda-contribution-description">
						<a href="<?php echo get_permalink($contribution['id'])?>"><?php echo $contribution['description']; ?></a>
					</div>
					<div class="cinda-contribution-actions">
						<a class="btn" href="<?php echo get_permalink($contribution['id'])?>"><?php _e('View Contribution', 'Cinda'); ?></a>
					</div>
				</div>
			<?php } 
		} ?>
	</div>
</article>
<?php 
get_footer('cinda');
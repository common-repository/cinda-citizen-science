<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
$result = cindaApp()->getResult();
$contribution = $result['contribution'];
cindaApp()->get_header();
?>
<div id="contribution" class="box">
	<h1><?php _e('Contribution data', 'Cinda')?></h1>
	<div class="field">
		<div class="small-4 medium-3 columns"><?php _e('Campaign','Cinda')?></div>
		<div class="small-8 medium-9 columns"><?php echo $result['campaign']->title; ?></div>
	</div>
	
	<div class="field">
		<div class="small-4 medium-3 columns"><?php _e('Created Date','Cinda')?></div>
		<div class="small-8 medium-9 columns"><?php echo $contribution->create_date; ?></div>
	</div>
	
	<div class="field">
		<div class="small-4 medium-3 columns"><?php _e('Modified Date','Cinda')?></div>
		<div class="small-8 medium-9 columns"><?php echo $contribution->modified_date; ?></div>
	</div>
	
	<h2><?php _e('Fields', 'Cinda')?></h2>
	<?php if(isset($result['model'])) foreach($result['model'] as $field){ ?>
		<div class="field">
			<div class="small-4 medium-3 columns"><?php echo $field->field_label; ?></div>
			<div class="small-8 medium-9 columns">
			<?php echo get_field_html( $field, $contribution->{$field->field_name} ); ?></div>
		</div>
		
	<?php } ?>
	<h3><?php _e('Actions', 'Cinda')?></h3>
	<div>
		<a class="button secondary" href="javascript: window.history.back();"><i class="fa fa-arrow-left"></i> <?php _e('Back','Cinda'); ?></a>
		<?php /*<a class="button alert right" href="delete/"><i class="fa fa-times"></i> <?php _e('Delete', 'Cinda')?></a>  */ ?>
		<?php if($contribution->author_id == cindaApp()->getVolunteer()->get_id()): ?>
			<a class="button success right" href="edit/"><i class="fa fa-pencil"></i> <?php _e('Edit', 'Cinda')?></a> 
		<?php endif; ?>
	</div>
</div>
<?php 
cindaApp()->get_footer();
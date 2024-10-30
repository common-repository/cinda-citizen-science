<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

$result = cindaApp()->getResult();
cindaApp()->get_header();
?>

<div id="contribution" class="box">
		
	<?php cindaApp()->get_errors_html(); ?>
	
	<form id="edit" method="post" enctype="multipart/form-data">
		
		<input type="hidden" name="nonce" value="<?php echo cindaApp()->getNonce(); ?>" />
		<input type="hidden" name="token" value="<?php echo cindaApp()->getToken(); ?>" />
		<input type="hidden" name="ID" value="<?php echo $result['contribution']->ID; ?>" />
		<input type="hidden" name="campaign" value="<?php echo $result['campaign']->ID; ?>" />
		
		<h1><?php _e('Contribution data', 'Cinda')?></h1>
		<div class="field">
			<div class="small-4 medium-3 columns"><?php _e('Campaign','Cinda')?></div>
			<div class="small-8 medium-9 columns"><?php echo $result['campaign']->title; ?></div>
		</div>
		<hr>
		<div class="field">
			<div class="small-4 medium-3 columns"><?php _e('Created Date','Cinda')?></div>
			<div class="small-8 medium-9 columns"><?php echo $result['contribution']->create_date; ?></div>
		</div>
		<hr>
		<div class="field">
			<div class="small-4 medium-3 columns"><?php _e('Modified Date','Cinda')?></div>
			<div class="small-8 medium-9 columns"><?php echo $result['contribution']->modified_date; ?></div>
		</div>
		<hr>
		<h2><?php _e('Fields', 'Cinda')?></h2>
		<?php if(isset($result['model'])) foreach($result['model'] as $field){ ?>
			<div class="field">
				<div class="small-4 medium-3 columns">
					<?php if($field->field_type != "description"){ echo $field->field_label; if($field->field_required){ echo "<span class=\"required\" title=\"".__('Field required','Cinda')."\">*</span> "; } } ?>
				</div>
				<div class="small-8 medium-9 columns">
					<?php echo get_field_input( $field, $result['contribution']->{$field->field_name} ); ?>
				</div>
			</div>
			<hr>
		<?php } ?>
		<div>
			<a class="button secondary" href="javascript: window.history.back();"><i class="fa fa-arrow-left"></i> <?php _e('Back','Cinda'); ?></a> 
			<button class="button success right"><i class="fa fa-floppy-o"></i> <?php _e('Save','Cinda'); ?></a>
		</div>
	</form>
</div>

<?php 
cindaApp()->get_footer();
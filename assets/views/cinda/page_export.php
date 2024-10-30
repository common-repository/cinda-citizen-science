<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
 
global $wpdb;
$campaigns = $wpdb->get_results("SELECT ID, post_title as title FROM ".$wpdb->prefix."posts WHERE post_type='".CINDA_PREFIX."campaign' AND post_status = 'publish';");
$volunteers = $wpdb->get_results("SELECT ID, post_title as title FROM ".$wpdb->prefix."posts WHERE post_type='".CINDA_PREFIX."volunteer' AND post_status = 'publish';");
?>

<div class="wrap">

	<h1>Export to CSV</h1>
	<hr>
	<table class="form-table export">
		<tbody>
		
			<form method="post">
				<input type="hidden" name="<?php echo CINDA_PREFIX; ?>export_action" value="campaigns_list">
				<tr>
					<th colspan="2"><h3><strong><?php _e('Campaign List','Cinda')?></strong></h3></th>
				</tr>
				<tr>
					<th><?php _e('Filename:','Cinda')?></th>
					<td>
						<input type="text" name="<?php echo CINDA_PREFIX; ?>export_name" placeholder="<?php _e('Name','Cinda'); ?>"> <span class="optional"><?php _e('(Optional)','Cinda')?></span>
					</td>
				</tr>
				
				<tr>
					<th><?php _e('Export:','Cinda')?></th>
					<td>
						<label><input type="radio" name="<?php echo CINDA_PREFIX; ?>export_all" value="1" checked="true"><?php _e('All','Cinda')?></label><br />
						<label><input type="radio" name="<?php echo CINDA_PREFIX; ?>export_all" value="0"><?php _e('Only actives','Cinda')?></label><br />
						<label><input type="radio" name="<?php echo CINDA_PREFIX; ?>export_all" value="2"><?php _e('Between dates','Cinda')?></label>
					</td>
				</tr>
				
				<tr class="date-range">
					<th><?php _e('Start date:','Cinda')?></th>
					<td>
						<input type="date" name="<?php echo CINDA_PREFIX; ?>export_date_start">
					</td>
				</tr>
				<tr class="date-range">
					<th><?php _e('End date:','Cinda')?></th>
					<td>
						<input type="date" name="<?php echo CINDA_PREFIX; ?>export_date_end">
					</td>
				</tr>
				<tr>
					<th></th>
					<td><button class="button-export export_campaigns"><i class="fa fa-floppy-o"></i> <?php _e('Export','Cinda'); ?></button></td>
				</tr>
			</form>
			
			<tr>
				<th colspan="2"><hr></th>
			</tr>
			
			<tr>
				<th colspan="2"><h3><strong><?php _e("Campaign's contributions",'Cinda')?></strong></h3></th>
			</tr>
			<form method="post">
				<input type="hidden" name="<?php echo CINDA_PREFIX; ?>export_action" value="contributions">
				<tr>
					<th><?php _e('Campaign:','Cinda')?></th>
					<td>
						<select name="<?php echo CINDA_PREFIX; ?>export_id">
							<option value="">Select Campaign</option>
							<?php 
								foreach ($campaigns as $campaign){
									?>
									<option value="<?php echo $campaign->ID; ?>"><?php _e($campaign->title,'Cinda'); ?></option>
									<?php 
								}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th><?php _e('Filename:','Cinda')?></th>
					<td><input type="text" name="<?php echo CINDA_PREFIX; ?>export_name" placeholder="<?php _e('Name','Cinda'); ?>"> <span class="optional"><?php _e('(Optional)','Cinda')?></span></td>
				</tr>
				
				<?php 
				/* <tr>
					<th><?php _e('¿Export Contributions?','Cinda')?></th>
					<td><input type="radio" name="<?php echo CINDA_PREFIX; ?>export_contributions" value="1" ><?php _e('Yes','Cinda')?> | <input type="radio" name="<?php echo CINDA_PREFIX; ?>export_contributions" value="0" checked="true"><?php _e('No','Cinda')?></td>
				</tr>
				 */ 
				?>
				 
				<tr>
					<th></th>
					<td><button class="button-export export_campaign"><i class="fa fa-floppy-o"></i> <?php _e('Export','Cinda'); ?></button></td>
				</tr>
							
			</form>
			<?php /* 
			<tr>
				<th colspan="2"><h2><?php _e('Contributions','Cinda')?></h2></th>
			</tr>
			
			<tr>
				<th><?php _e('Export all Contributions','Cinda')?></th>
				<td>
					<form method="post">
						<input type="hidden" name="<?php echo CINDA_PREFIX; ?>export_action" value="contributions">
						<select name="<?php echo CINDA_PREFIX; ?>export_id">
							<option value="">Select Campaign</option>
							<?php 
								foreach ($campaigns as $campaign){
									?>
									<option value="<?php echo $campaign->ID; ?>"><?php _e($campaign->title,'Cinda'); ?></option>
									<?php 
								}
							?>
						</select>
						<input type="text" name="<?php echo CINDA_PREFIX; ?>export_name" placeholder="<?php _e('Export Name (Optional)','Cinda'); ?>">
						<button class="button-export export_contributions"><i class="fa fa-floppy-o"></i> <?php _e('Export','Cinda'); ?></button>
					</form>
				</td>
			</tr>
			
			<tr>
				<th><?php _e('Export all contributions of a volunteer','Cinda')?></th>
				<td>
					<select>
						<option value=""><?php _e('Select Volunteer','Cinda'); ?></option>
						<?php 
							foreach ($volunteers as $volunteer){
								?>
								<option value="<?php echo $volunteer->ID; ?>"><?php _e($volunteer->title,'Cinda'); ?></option>
								<?php 
							}
						?>
					</select> 
					<button class="button-export export_campaign"><i class="fa fa-floppy-o"></i> <?php _e('Export','Cinda'); ?></button>
				</td>
			</tr>
			
			<tr>
				<th><?php _e('Export contributions of a volunteer to a campaign','Cinda')?></th>
				<td>
					<select>
						<option value=""><?php _e('Select Volunteer','Cinda'); ?></option>
						<?php 
							foreach ($volunteers as $volunteer){
								?>
								<option value="<?php echo $volunteer->ID; ?>"><?php _e($volunteer->title,'Cinda'); ?></option>
								<?php 
							}
						?>
					</select>
					<select>
						<option value=""><?php _e('Select Campaign','Cinda'); ?></option>
						<?php 
							foreach ($campaigns as $campaign){
								?>
								<option value="<?php echo $campaign->ID; ?>"><?php _e($campaign->title,'Cinda'); ?></option>
								<?php 
							}
						?>
					</select> 
					<button class="button-export export_contributions"><i class="fa fa-floppy-o"></i> <?php _e('Export','Cinda'); ?></button>
				</td>
			</tr>
			
			<tr>
				<th colspan="2"><h2><?php _e('Volunteers','Cinda')?></h2></th>
			</tr>
			
			<tr>
				<th><?php _e('Export Volunteers list','Cinda')?></th>
				<td>
					<form method="post">
						<input type="hidden" name="type" value="volunteers_list">
						<button class="button-export export_campaigns"><i class="fa fa-floppy-o"></i> <?php _e('Export','Cinda'); ?></button>
					</form>
				</td>
			</tr>
			*/?>
		</tbody>
	</table>

</div>
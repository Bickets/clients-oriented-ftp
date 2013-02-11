<?php
/**
 * Define the common functions used on the installer and updates.
 *
 * @package		ProjectSend
 * @subpackage	Updates
 */

	/**
	 * If any update was made to the database structure, show the message
	 */
	if(isset($updates_made) && $updates_made > 0) {
?>
		<div id="system_msg">
			<p><strong><?php _e('System Notice:', 'cftp_admin');?></strong> <?php _e('The database was updated to support this version of the software.', 'cftp_admin'); ?></p>
		</div>
		<div class="container-fluid">
			<div class="row-fluid">
				<div class="span12">
					<div class="message message_info" id="donations_message">
							<h3><strong><?php _e('Want to support ProjectSend?', 'cftp_admin');?></strong></h3>
							<p><?php _e('Please remember that this tool is free software. If you find the system useful', 'cftp_admin'); ?>
							<a href="<?php echo DONATIONS_URL; ?>" target="_blank"><?php _e('please consider making a donation to support further development.', 'cftp_admin'); ?></a>
							<?php _e('Thank you!', 'cftp_admin'); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
<?php
	}
	
	if(isset($updates_error_messages) && !empty($updates_error_messages)) {
?>
		<div class="container-fluid">
			<div class="row-fluid">
				<div class="span12">
					<?php
						foreach ($updates_error_messages as $updates_error_msg) {
							echo system_message('error',$updates_error_msg);
						}
					?>
				</div>
			</div>
		</div>
<?php
	}
?>

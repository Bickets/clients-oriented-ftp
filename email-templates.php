<?php
/**
 * Allows the administrator to customize the emails
 * sent by the system.
 *
 * @package ProjectSend
 * @subpackage Options
 */
$easytabs = 1;
$allowed_levels = array(9);
require_once('sys.includes.php');

$page_title = __('E-mail templates','cftp_admin');

include('header.php');

$database->MySQLDB();

if ($_POST) {
	/**
	 * Escape all the posted values on a single function.
	 * Defined on functions.php
	 */
	$_POST = mysql_real_escape_array($_POST);
	$keys = array_keys($_POST);

	$options_total = count($keys);

	$updated = 0;
	for ($j = 0; $j < $options_total; $j++) {
		$q = 'UPDATE tbl_options SET value="'.$_POST[$keys[$j]].'" WHERE name="'.$keys[$j].'"';
		$sql = mysql_query($q, $database->connection);
		$updated++;
	}
	if ($updated > 0){
		$query_state = 'ok';
	}
	else {
		$query_state = 'err';
	}
}
?>
<div id="main">
	<h2><?php echo $page_title; ?></h2>

	<?php
		if (isset($query_state)) {
			switch ($query_state) {
				case 'ok':
					$msg = __('Options updated succesfuly.','cftp_admin');
					echo system_message('ok',$msg);
					break;
				case 'err':
					$msg = __('There was an error. Please try again.','cftp_admin');
					echo system_message('error',$msg);
					break;
			}
		}
		else {
			$show_options_form = 1;
		}
		
		if(isset($show_options_form)) {
	?>
		
			<script type="text/javascript">
				$(document).ready(function() {
					$('#tab-container').easytabs();
				});
			</script>
			
			<?php
				$options_groups = array(
										1	=> array(
														'tab'			=> 'file_by_user',
														'name'			=> __('New file by user','cftp_admin'),
														'checkbox'		=> 'email_new_file_by_user_customize',
														'textarea'		=> 'email_new_file_by_user_text',
														'description'	=> __('This email will be sent to a client whenever a new file has been assigned to his account.','cftp_admin'),
														'option_check'	=> EMAILS_FILE_BY_USER_USE_CUSTOM,
														'option_text'	=> EMAILS_FILE_BY_USER_TEXT,
													),
										2	=> array(
														'tab'			=> 'file_by_client',
														'name'			=> __('New file by client','cftp_admin'),
														'checkbox'		=> 'email_new_file_by_client_customize',
														'textarea'		=> 'email_new_file_by_client_text',
														'description'	=> __('This email will be sent to the system administrator whenever a client uploads a new file.','cftp_admin'),
														'option_check'	=> EMAILS_FILE_BY_CLIENT_USE_CUSTOM,
														'option_text'	=> EMAILS_FILE_BY_CLIENT_TEXT,
													),
										3	=> array(
														'tab'			=> 'client_by_user',
														'name'			=> __('New client by system user','cftp_admin'),
														'checkbox'		=> 'email_new_client_by_user_customize',
														'textarea'		=> 'email_new_client_by_user_text',
														'description'	=> __('This email will be sent to the new client after an administrator has created his new account. It would be best to include the log in details (username and password).','cftp_admin'),
														'option_check'	=> EMAILS_CLIENT_BY_USER_USE_CUSTOM,
														'option_text'	=> EMAILS_CLIENT_BY_USER_TEXT,
													),
										4	=> array(
														'tab'			=> 'client_by_self',
														'name'			=> __('New client self-registered','cftp_admin'),
														'checkbox'		=> 'email_new_client_by_self_customize',
														'textarea'		=> 'email_new_client_by_self_text',
														'description'	=> __('This email will be sent to the system administrator after a new client has created a new account for himself.','cftp_admin'),
														'option_check'	=> EMAILS_CLIENT_BY_SELF_USE_CUSTOM,
														'option_text'	=> EMAILS_CLIENT_BY_SELF_TEXT,
													),
										5	=> array(
														'tab'			=> 'new_user_welcome',
														'name'			=> __('New user welcome','cftp_admin'),
														'checkbox'		=> 'email_new_user_customize',
														'textarea'		=> 'email_new_user_text',
														'description'	=> __('This email will be sent to the new system user after an administrator has created his new account. It would be best to include the log in details (username and password).','cftp_admin'),
														'option_check'	=> EMAILS_NEW_USER_USE_CUSTOM,
														'option_text'	=> EMAILS_NEW_USER_TEXT,
													),
									);
			?>
		
			<form action="email-templates.php" name="templatesform" method="post">
				<div id="outer_tabs_wrapper">
	
					<div id="tab-container" class='tab-container'>
						<ul class="etabs">
							<?php
								foreach ($options_groups as $group) {
							?>
									<li class="tab">
										<a href="#tab_<?php echo $group['tab']; ?>">
											<?php echo $group['name']; ?>
										</a>
									</li>
							<?php
								}
							?>
						</ul>
						<div class="panel-container">
							<?php
								foreach ($options_groups as $group) {
							?>
									<div id="tab_<?php echo $group['tab']; ?>">
										<div class="options_box whitebox">
											<ul class="form_fields">
												<li>
													<h3><?php echo $group['name']; ?></h3>
													<p class="text-warning"><?php echo $group['description']; ?></p>
												</li>
												<li>
													<label for="<?php echo $group['checkbox']; ?>"><?php _e('Use custom template','cftp_admin'); ?></label>
													<input type="hidden" value="0" name="<?php echo $group['checkbox']; ?>" class="checkbox_options" <?php echo ($group['option_check'] == 0) ? '' : 'checked="checked"'; ?> />
													<input type="checkbox" value="1" name="<?php echo $group['checkbox']; ?>" class="checkbox_options" <?php echo ($group['option_check'] == 1) ? 'checked="checked"' : ''; ?> />
												</li>
												<li>
													<label for="<?php echo $group['textarea']; ?>"><?php _e('Template text','cftp_admin'); ?></label>
													<textarea name="<?php echo $group['textarea']; ?>" id="<?php echo $group['textarea']; ?>"><?php echo $group['option_text']; ?></textarea>
												</li>	
												
												<li>
													<p class="field_note"><?php _e('You can use HTML tags here.','cftp_admin'); ?></p>
												</li>				
								
											</ul>
										</div>
									</div>
							<?php
								}
							?>
						</div>
					</div>
				</div>

				<ul class="form_fields">		
					<li class="form_submit_li">
						<input type="submit" name="Submit" value="<?php _e('Update all options','cftp_admin'); ?>" class="button button_blue button_submit" />
					</li>
				</ul>
			</form>
		</div>

<?php } ?>

	<div class="clear"></div>
</div>

<?php include('footer.php'); ?>
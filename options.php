<?php
/**
 * Options page and form.
 *
 * @package ProjectSend
 */
$easytabs = 1;
$allowed_levels = array(9);
require_once('sys.includes.php');
$page_title = __('System options','cftp_admin');

$database->MySQLDB();

/** Uses TextBoxList for the allowed file types box. */
$textboxlist = 1;
include('header.php');

if ($_POST) {
	/**
	 * Escape all the posted values on a single function.
	 * Defined on functions.php
	 */
	 
	/** Temp fix, this should be done in a different way */
	$allowed_empty_values = array('mail_copy_addresses','mail_smtp_host','mail_smtp_port','mail_smtp_user','mail_smtp_pass');
	foreach ($allowed_empty_values as $check_field) {
		if (empty($_POST[$check_field])) { $_POST[$check_field] = ''; }
	}
	
	$_POST = mysql_real_escape_array($_POST);
	$keys = array_keys($_POST);

	$options_total = count($keys);
	$options_filled = 0;

	/**
	 * Check if all the options are filled.
	 */
	for ($i = 0; $i < $options_total; $i++) {
		if (!isset($_POST[$keys[$i]])) {
			$query_state = 'err_fill';
		}
		else {
			$options_filled++;
		}
	}

	/** If every option is completed, continue */
	if ($options_filled == $options_total) {
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

}

/**
 * Replace | with , to use the tags system when showing
 * the allowed filetypes on the form. This value comes from
 * site.options.php
*/
$allowed_file_types = str_replace('|',',',$allowed_file_types);
/** Explode, sort, and implode the values to list them alphabetically */
$allowed_file_types = explode(',',$allowed_file_types);
sort($allowed_file_types);
$allowed_file_types = implode(',',$allowed_file_types);

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
				case 'err_fill':
					$msg = __('Some fields were not completed. Options could not be saved.','cftp_admin');
					echo system_message('error',$msg);
					$show_options_form = 1;
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

				$(function(){
					var t = new $.TextboxList('#allowed_file_types', {unique: true, bitsOptions:{editable:{addKeys: 188}}});
				});

				$("form").submit(function() {
					clean_form(this);

					is_complete_all_options(this,'<?php _e('Please complete all the fields.','cftp_admin'); ?>');

					// show the errors or continue if everything is ok
					if (show_form_errors() == false) { alert('<?php _e('Please complete all the fields.','cftp_admin'); ?>'); return false; }
				});
			});
		</script>
	
		<form action="options.php" name="optionsform" method="post">
			<div id="outer_tabs_wrapper">

				<div id="tab-container" class='tab-container'>
					<ul class="etabs">
						<li class="tab"><a href="#tab_general"><?php _e('General Options','cftp_admin'); ?></a></li>
						<li class="tab"><a href="#tab_clients"><?php _e('Clients','cftp_admin'); ?></a></li>
						<li class="tab"><a href="#tab_email"><?php _e('E-mail notifications','cftp_admin'); ?></a></li>
						<li class="tab"><a href="#tab_security"><?php _e('Security','cftp_admin'); ?></a></li>
						<li class="tab"><a href="#tab_thumbs"><?php _e('Thumbnails','cftp_admin'); ?></a></li>
						<li class="tab"><a href="#tab_logo"><?php _e('Company logo','cftp_admin'); ?></a></li>
					</ul>
					<div class="panel-container">
	
						<div id="tab_general">
							<div class="options_box whitebox">
								<ul class="form_fields">
									<li>
										<h3><?php _e('System location','cftp_admin'); ?></h3>
										<p class="text-warning"><?php _e('These options are to be changed only if you are moving the system to another place. Changes here can cause ProjectSend to stop working.','cftp_admin'); ?></p>
									</li>
									<li>
										<label for="base_uri"><?php _e('System URI','cftp_admin'); ?></label>
										<input type="text" name="base_uri" id="base_uri" value="<?php echo BASE_URI; ?>" />
									</li>
					
									<li class="options_divide"></li>
					
									<li>
										<h3><?php _e('General','cftp_admin'); ?></h3>
										<p><?php _e('Basic information to be shown around the site. The time format and zones values affect how the clients see the dates on their files lists.','cftp_admin'); ?></p>
									</li>
									<li>
										<label for="this_install_title"><?php _e('Site name','cftp_admin'); ?></label>
										<input type="text" name="this_install_title" id="this_install_title" value="<?php echo THIS_INSTALL_SET_TITLE; ?>" />
									</li>
									<li>
										<label for="selected_clients_template"><?php _e("Client's template",'cftp_admin'); ?></label>
										<select name="selected_clients_template" id="selected_clients_template">
											<?php
												$templates = look_for_templates();
												foreach ($templates as $template) {
													echo '<option value="'.$template['folder'].'"';
														if($template['folder'] == TEMPLATE_USE) {
															echo ' selected="selected"';
														}
													echo '>'.$template['name'].'</option>';
												}
											?>
										</select>
									</li>
									<li>
										<label for="timezone"><?php _e('Timezone','cftp_admin'); ?></label>
										<?php
											/** 
											 * Generates a select field.
											 * Code is stored on a separate file since it's pretty long.
											 */
											include_once('includes/timezones.php');
										?>
									</li>
									<li>
										<label for="timeformat"><?php _e('Time format','cftp_admin'); ?></label>
										<input type="text" name="timeformat" id="timeformat" value="<?php echo TIMEFORMAT_USE; ?>" />
										<p class="field_note"><?php _e('For example, d/m/Y h:i:s will result in something like','cftp_admin'); ?> <strong><?php echo date('d/m/Y h:i:s'); ?></strong>.
										<?php _e('For the full list of available values, visit','cftp_admin'); ?> <a href="http://php.net/manual/en/function.date.php" target="_blank"><?php _e('this page','cftp_admin'); ?></a>.</p>
									</li>
					
									<li class="options_divide"></li>
								</ul>
							</div>
						</div>


						<div id="tab_clients">
							<div class="options_box whitebox">
								<ul class="form_fields">
									<li>
										<h3><?php _e('New registrations','cftp_admin'); ?></h3>
										<p><?php _e('Used only on self-registrations. These options will not apply to clients registered by system administrators.','cftp_admin'); ?></p>
									</li>
									<li>
										<label for="clients_can_register"><?php _e('Clients can register themselves','cftp_admin'); ?></label>
										<input type="hidden" value="0" name="clients_can_register" class="checkbox_options" <?php echo (CLIENTS_CAN_REGISTER == 0) ? '' : 'checked="checked"'; ?> />
										<input type="checkbox" value="1" name="clients_can_register" class="checkbox_options" <?php echo (CLIENTS_CAN_REGISTER == 1) ? 'checked="checked"' : ''; ?> />
									</li>
									<li>
										<label for="clients_auto_approve"><?php _e('Auto approve new accounts','cftp_admin'); ?></label>
										<input type="hidden" value="0" name="clients_auto_approve" class="checkbox_options" <?php echo (CLIENTS_AUTO_APPROVE == 0) ? '' : 'checked="checked"'; ?> />
										<input type="checkbox" value="1" name="clients_auto_approve" class="checkbox_options" <?php echo (CLIENTS_AUTO_APPROVE == 1) ? 'checked="checked"' : ''; ?> />
									</li>
									<li>
										<label for="clients_auto_group"><?php _e('Add clients to this group:','cftp_admin'); ?></label>
										<select name="clients_auto_group" id="clients_auto_group">
											<option value="0"><?php _e('None (does not enable this feature)','cftp_admin'); ?></option>
										<?php
											/** Fill the groups array that will be used on the form */
											$groups = array();
											$cq = "SELECT id, name FROM tbl_groups ORDER BY name ASC";
											$sql = $database->query($cq);
											while($grow = mysql_fetch_array($sql)) {
												?>
													<option value="<?php echo $grow["id"]; ?>"
														<?php
															if (CLIENTS_AUTO_GROUP == $grow["id"]) {
																echo 'selected="selected"';
															}
														?>
														><?php echo $grow["name"]; ?>
													</option>
												<?php
											}
										?>
										</select>
										<p class="field_note"><?php _e('New clients will automatically be assigned to the group you have selected.','cftp_admin'); ?></p>
									</li>
									
									<li class="options_divide"></li>
									<li>
										<h3><?php _e('Files','cftp_admin'); ?></h3>
										<p><?php _e('Options related to the files that clients upload themselves.','cftp_admin'); ?></p>
									</li>
									<li>
										<label for="clients_can_upload"><?php _e('Clients can upload files','cftp_admin'); ?></label>
										<input type="hidden" value="0" name="clients_can_upload" class="checkbox_options" <?php echo (CLIENTS_CAN_UPLOAD == 0) ? '' : 'checked="checked"'; ?> />
										<input type="checkbox" value="1" name="clients_can_upload" class="checkbox_options" <?php echo (CLIENTS_CAN_UPLOAD == 1) ? 'checked="checked"' : ''; ?> />
									</li>
					
									<li class="options_divide"></li>
								</ul>
							</div>
						</div>


						<div id="tab_email">
							<div class="options_box whitebox">
								<ul class="form_fields">
									<li>
										<h3><?php _e('"From" information','cftp_admin'); ?></h3>
									</li>
									<li>
										<label for="admin_email_address"><?php _e('E-mail address','cftp_admin'); ?></label>
										<input type="text" name="admin_email_address" id="admin_email_address" value="<?php echo ADMIN_EMAIL_ADDRESS; ?>" />
									</li>					
									<li>
										<label for="mail_from_name"><?php _e('Name','cftp_admin'); ?></label>
										<input type="text" name="mail_from_name" id="mail_from_name" value="<?php echo MAIL_FROM_NAME; ?>" />
									</li>					
									<li class="options_divide"></li>

									<li>
										<h3><?php _e('Send copies','cftp_admin'); ?></h3>
									</li>
									<li>
										<label for="mail_copy_user_upload"><?php _e('When a system user uploads files','cftp_admin'); ?></label>
										<input type="hidden" value="0" name="mail_copy_user_upload" class="mail_copy_user_upload" <?php echo (COPY_MAIL_ON_USER_UPLOADS == 0) ? '' : 'checked="checked"'; ?> />
										<input type="checkbox" value="1" name="mail_copy_user_upload" class="mail_copy_user_upload" <?php echo (COPY_MAIL_ON_USER_UPLOADS == 1) ? 'checked="checked"' : ''; ?> />
									</li>
									<li>
										<label for="mail_copy_client_upload"><?php _e('When a client uploads files','cftp_admin'); ?></label>
										<input type="hidden" value="0" name="mail_copy_client_upload" class="mail_copy_client_upload" <?php echo (COPY_MAIL_ON_CLIENT_UPLOADS == 0) ? '' : 'checked="checked"'; ?> />
										<input type="checkbox" value="1" name="mail_copy_client_upload" class="mail_copy_client_upload" <?php echo (COPY_MAIL_ON_CLIENT_UPLOADS == 1) ? 'checked="checked"' : ''; ?> />
									</li>
									<li class="options_nested_note">
										<p><?php _e('Define here who will receive copies of this emails. These are sent as BCC so neither recipient will see the other addresses.','cftp_admin'); ?></p>
									</li>
									<li>
										<label for="mail_copy_main_user"><?php _e('Address supplied above (on "From")','cftp_admin'); ?></label>
										<input type="hidden" value="0" name="mail_copy_main_user" class="mail_copy_main_user" <?php echo (COPY_MAIL_MAIN_USER == 0) ? '' : 'checked="checked"'; ?> />
										<input type="checkbox" value="1" name="mail_copy_main_user" class="mail_copy_main_user" <?php echo (COPY_MAIL_MAIN_USER == 1) ? 'checked="checked"' : ''; ?> />
									</li>
									<li>
										<label for="mail_copy_addresses"><?php _e('Also to this addresses','cftp_admin'); ?></label>
										<input type="text" name="mail_copy_addresses" id="mail_copy_addresses" class="mail_data empty" value="<?php echo COPY_MAIL_ADDRESSES; ?>" />
										<p class="field_note"><?php _e('Separate e-mail addresses with a comma.','cftp_admin'); ?></p>
									</li>					
									<li class="options_divide"></li>

									<li>
										<h3><?php _e('E-mail sending options','cftp_admin'); ?></h3>
										<p><?php _e('Here you can select which mail system will be used when sending the notifications. If you have a valid e-mail account, SMTP is the recommended option.','cftp_admin'); ?></p>
									</li>
									<li>
										<label for="mail_system_use"><?php _e('Mailer','cftp_admin'); ?></label>
										<select name="mail_system_use" id="mail_system_use">
											<option value="mail" <?php echo (MAIL_SYSTEM == 'mail') ? 'selected="selected"' : ''; ?>>PHP Mail (basic)</option>
											<option value="smtp" <?php echo (MAIL_SYSTEM == 'smtp') ? 'selected="selected"' : ''; ?>>SMTP</option>
											<option value="gmail" <?php echo (MAIL_SYSTEM == 'gmail') ? 'selected="selected"' : ''; ?>>Gmail</option>
											<option value="sendmail" <?php echo (MAIL_SYSTEM == 'sendmail') ? 'selected="selected"' : ''; ?>>Sendmail</option>
										</select>
									</li>					
									<li>
					
									<li class="options_divide"></li>
									<li>
										<h3><?php _e('SMTP & Gmail shared options','cftp_admin'); ?></h3>
										<p><?php _e('You need to include your username (usually your e-mail address) and password if you have selected either SMTP or Gmail as your mailer.','cftp_admin'); ?></p>
									</li>
									<li>
										<label for="mail_smtp_user"><?php _e('Username','cftp_admin'); ?></label>
										<input type="text" name="mail_smtp_user" id="mail_smtp_user" class="mail_data empty" value="<?php echo SMTP_USER; ?>" />
									</li>					
									<li>
										<label for="mail_smtp_pass"><?php _e('Password','cftp_admin'); ?></label>
										<input type="password" name="mail_smtp_pass" id="mail_smtp_pass" class="mail_data empty" value="<?php echo SMTP_PASS; ?>" />
									</li>					
									<li class="options_divide"></li>

									<li>
										<h3><?php _e('SMTP options','cftp_admin'); ?></h3>
										<p><?php _e('If you selected SMTP as your mailer, please complete these options.','cftp_admin'); ?></p>
									</li>
									<li>
										<label for="mail_smtp_host"><?php _e('Host','cftp_admin'); ?></label>
										<input type="text" name="mail_smtp_host" id="mail_smtp_host" class="mail_data empty" value="<?php echo SMTP_HOST; ?>" />
									</li>					
									<li>
										<label for="mail_smtp_port"><?php _e('Port','cftp_admin'); ?></label>
										<input type="text" name="mail_smtp_port" id="mail_smtp_port" class="mail_data empty" value="<?php echo SMTP_PORT; ?>" />
									</li>					

									<li class="options_divide"></li>
								</ul>
							</div>
						</div>
						
						<div id="tab_security">
							<div class="options_box whitebox">
								<ul class="form_fields">		
									<li>
										<h3><?php _e('Allowed file extensions','cftp_admin'); ?></h3>
										<p><?php _e('Be careful when changing this options. They could affect not only the system but the whole server it is installed on.','cftp_admin'); ?><br />
										<strong><?php _e('Important','cftp_admin'); ?></strong>: <?php _e('Separate allowed file types with a comma. You can navigate the box with the left/right arrows, backspace and delete keys.','cftp_admin'); ?></p>
									</li>
									<li>
										<input name="allowed_file_types" id="allowed_file_types" value="<?php echo $allowed_file_types; ?>" />
									</li>
					
									<li class="options_divide"></li>
								</ul>
							</div>
						</div>
						
						<div id="tab_thumbs">
							<div class="options_box whitebox">
								<ul class="form_fields">		
									<li>
										<h3><?php _e('Thumbnails','cftp_admin'); ?></h3>
										<p><?php _e("Thumbnails are used on files lists. It is recommended to keep them small, unless you are using the system to upload only images, and will change the default client's template accordingly.",'cftp_admin'); ?></p>
									</li>
									<li class="options_column">
										<div class="options_col_left">
											<label for="max_thumbnail_width"><?php _e('Max width','cftp_admin'); ?></label>
											<input type="text" name="max_thumbnail_width" id="max_thumbnail_width" value="<?php echo THUMBS_MAX_WIDTH; ?>" /><br />

											<label for="max_thumbnail_height"><?php _e('Max height','cftp_admin'); ?></label>
											<input type="text" name="max_thumbnail_height" id="max_thumbnail_height" value="<?php echo THUMBS_MAX_HEIGHT; ?>" /><br />
										</div>
										<div class="options_col_right">
											<label for="thumbnail_default_quality"><?php _e('JPG Quality','cftp_admin'); ?></label>
											<input type="text" name="thumbnail_default_quality" id="thumbnail_default_quality" value="<?php echo THUMBS_QUALITY; ?>" />
										</div>
									</li>
					
									<li class="options_divide"></li>
									<li>
										<h3><?php _e("File's path", 'cftp_admin'); ?></h3>
										<p><?php _e("If thumbnails are not showing (your company logo and file's preview on the branding page and client's files lists) try setting this option ON. It they still don't work, a folders permission issue might be the cause.",'cftp_admin'); ?></p>
									</li>
									<li>
										<label for="thumbnails_use_absolute"><?php _e("Use file's absolute path",'cftp_admin'); ?></label>
										<input type="hidden" value="0" name="thumbnails_use_absolute" class="thumbnails_use_absolute" <?php echo (THUMBS_USE_ABSOLUTE == 0) ? '' : 'checked="checked"'; ?> />
										<input type="checkbox" value="1" name="thumbnails_use_absolute" class="thumbnails_use_absolute" <?php echo (THUMBS_USE_ABSOLUTE == 1) ? 'checked="checked"' : ''; ?> />
									</li>

									<li class="options_divide"></li>
								</ul>
							</div>
						</div>
						
						<div id="tab_logo">
							<div class="options_box whitebox">
								<ul class="form_fields">		
									<li>		
										<h3><?php _e('Size settings','cftp_admin'); ?></h3>
										<p><?php _e("Like the thumbnails options, these have to be changed taking into account the client's template design, since it can be shown there. The default template uses a fixed width for the logo, however the Gallery template uses this setting to show the image on top.",'cftp_admin'); ?></p>
									</li>
									<li class="options_column">
										<div class="options_col_left">
											<label for="max_logo_width"><?php _e('Max width','cftp_admin'); ?></label>
											<input type="text" name="max_logo_width" id="max_logo_width" value="<?php echo LOGO_MAX_WIDTH; ?>" />
										</div>
										<div class="options_col_right">
											<label for="max_logo_height"><?php _e('Max height','cftp_admin'); ?></label>
											<input type="text" name="max_logo_height" id="max_logo_height" value="<?php echo LOGO_MAX_HEIGHT; ?>" />
										</div>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
	
				<ul class="form_fields">		
					<li class="form_submit_li">
						<input type="submit" name="Submit" value="<?php _e('Update all options','cftp_admin'); ?>" class="button button_blue button_submit" />
					</li>
				</ul>

			</div>
		</form>

		<?php } ?>

	</div>

</div>

<?php
	$database->Close();
	include('footer.php');
?>
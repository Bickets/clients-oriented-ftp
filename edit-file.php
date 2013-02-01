<?php
/**
 * Edit a file name or description.
 * Files can only be edited by the uploader and level 9 or 8 users.
 *
 * @package ProjectSend
 */
$multiselect = 1;
$allowed_levels = array(9,8,7,0);
require_once('sys.includes.php');
$page_title = __('Edit file','cftp_admin');
include('header.php');

/**
 * The file's id is passed on the URI.
 */
if (!empty($_GET['file_id'])) {
	$this_file_id = $_GET['file_id'];
}

/** Fill the users array that will be used on the notifications process */
$users = array();
$cq = "SELECT id, name, level FROM tbl_users";
$sql = $database->query($cq);
while($row = mysql_fetch_array($sql)) {
	$users[$row["id"]] = $row["name"];
	if ($row["level"] == '0') {
		$clients[$row["id"]] = $row["name"];
	}
}
/** Fill the groups array that will be used on the form */
$groups = array();
$cq = "SELECT id, name FROM tbl_groups ORDER BY name ASC";
$sql = $database->query($cq);
	while($row = mysql_fetch_array($sql)) {
	$groups[$row["id"]] = $row["name"];
}

/**
 * Get the user level to determine if the uploader is a
 * system user or a client.
 */
$current_level = get_current_user_level();

?>

<div id="main">
	<h2><?php echo $page_title; ?></h2>

	<?php
		/**
		 * Show an error message if no ID value is passed on the URI.
		 */
		if(empty($this_file_id)) {
			$no_results_error = 'no_id_passed';
		}
		else {
			$database->MySQLDB();
			$files_query = 'SELECT * FROM tbl_files WHERE id="' . $this_file_id . '"';
	
			/**
			 * Count the files assigned to this client. If there is none, show
			 * an error message.
			 */
			$sql = $database->query($files_query);
			$count = mysql_num_rows($sql);
			if (!$count) {
				$no_results_error = 'id_not_exists';
			}
	
			/**
			 * Continue if client exists and has files under his account.
			 */
			while($row = mysql_fetch_array($sql)) {
				$edit_file_info['url'] = $row['url'];
				$edit_file_info['id'] = $row['id'];

				$edit_file_allowed = array(7,0);
				if (in_session_or_cookies($edit_file_allowed)) {
					if ($row['uploader'] != $global_user) {
						$no_results_error = 'not_uploader';
					}
				}
			}
		}

		/** Show the error if it is defined */
		if (isset($no_results_error)) {
			switch ($no_results_error) {
				case 'no_id_passed':
					$no_results_message = __('Please go to the clients or groups administration page, select "Manage files" from any client and then click on "Edit" on any file to return here.','cftp_admin');;
					break;
				case 'id_not_exists':
					$no_results_message = __('There is not file with that ID number.','cftp_admin');;
					break;
				case 'not_uploader':
					$no_results_message = __("You don't have permission to edit this file.",'cftp_admin');;
					break;
			}
	?>
			<div class="whiteform whitebox whitebox_text">
				<?php echo $no_results_message; ?>
			</div>
	<?php
		}
		else {

			/**
			 * See what clients or groups already have this file assigned.
			 */
			$file_on_clients = array();
			$file_on_groups = array();

			if(isset($_POST['submit'])) {

				$assignments_query = 'SELECT file_id, client_id, group_id FROM tbl_files_relations WHERE file_id="' . $this_file_id . '"';
				$assignments_sql = $database->query($assignments_query);
				$assignments_count = mysql_num_rows($assignments_sql);
				if ($assignments_count > 0) {
					while ($assignment_row = mysql_fetch_array($assignments_sql)) {
						if (!empty($assignment_row['client_id'])) {
							$file_on_clients[] = $assignment_row['client_id'];
						}
						elseif (!empty($assignment_row['group_id'])) {
							$file_on_groups[] = $assignment_row['group_id'];
						}
					}
				}
	
				$n = 0;
				foreach ($_POST['file'] as $file) {
					$n++;
					if(!empty($file['name'])) {
						/**
						* If the uploader is a client, set the "client" var to the current
						* uploader username, since the "client" field is not posted.
						*/
						if ($current_level == 0) {
							$file['assignments'] = 'c'.$global_user;
						}
						
						$this_upload = new PSend_Upload_File();
						/**
						 * Unassigned files are kept as orphans and can be related
						 * to clients or groups later.
						 */
					
						/** Add to the database for each client / group selected */
						$add_arguments = array(
												'file' => $edit_file_info['url'],
												'name' => $file['name'],
												'description' => $file['description'],
												'uploader' => $global_user,
												'uploader_id' => $global_id
											);
					
						/** Set notifications to YES by default */
						$send_notifications = true;
					
						if (!empty($file['hidden'])) {
							$add_arguments['hidden'] = $file['hidden'];
							$send_notifications = false;
						}
						
						if ($current_level != 0) {
							if (!empty($file['assignments'])) {
								/**
								 * Remove already assigned clients/groups from the list.
								 * Only adds assignments to the NEWLY selected ones.
								 */
								$full_list = $file['assignments'];
								foreach ($file_on_clients as $this_client) { $compare_clients[] = 'c'.$this_client; }
								foreach ($file_on_groups as $this_group) { $compare_groups[] = 'g'.$this_group; }
								if (!empty($compare_clients)) {
									$full_list = array_diff($full_list,$compare_clients);
								}
								if (!empty($compare_groups)) {
									$full_list = array_diff($full_list,$compare_groups);
								}
								$add_arguments['assign_to'] = $full_list;
								
								/**
								 * On cleaning the DB, only remove the clients/groups
								 * That just have been deselected.
								 */
								$clean_who = $file['assignments'];
							}
							else {
								$clean_who = 'All';
							}
							
							/** CLEAN deletes the removed users/groups from the assignments table */
							if ($clean_who == 'All') {
								$clean_all_arguments = array(
																'owner_id' => $global_id, /** For the log */
																'file_id' => $this_file_id,
																'file_name' => $file['name']
															);
								$clean_assignments = $this_upload->clean_all_assignments($clean_all_arguments);
							}
							else {						
								$clean_arguments = array (
														'owner_id' => $global_id, /** For the log */
														'file_id' => $this_file_id,
														'file_name' => $file['name'],
														'assign_to' => $clean_who,
														'current_clients' => $file_on_clients,
														'current_groups' => $file_on_groups
													);
								$clean_assignments = $this_upload->clean_assignments($clean_arguments);
							}
						}
						
						/** Uploader is a client */
						if ($current_level == 0) {
							$add_arguments['assign_to'] = array('c'.$global_id);
							$add_arguments['hidden'] = '0';
							$add_arguments['uploader_type'] = 'client';
							$action_log_number = 33;
						}
						else {
							$add_arguments['uploader_type'] = 'user';
							$action_log_number = 32;
						}
						/**
						 * 1- Add the file to the database
						 */
						$process_file = $this_upload->upload_add_to_database($add_arguments);
						if($process_file['database'] == true) {
							$add_arguments['new_file_id'] = $process_file['new_file_id'];
							$add_arguments['all_users'] = $users;
							$add_arguments['all_groups'] = $groups;
							
							if ($current_level != 0) {
								/**
								 * 2- Add the assignments to the database
								 */
								$process_assignment = $this_upload->upload_add_assignment($add_arguments);
								/**
								 * 3- Add the notifications to the database
								 */
								if ($send_notifications == true) {
									$process_notifications = $this_upload->upload_add_notifications($add_arguments);
								}
							}

							$new_log_action = new LogActions();
							$log_action_args = array(
													'action' => $action_log_number,
													'owner_id' => $global_id,
													'owner_user' => $global_user,
													'affected_file' => $process_file['new_file_id'],
													'affected_file_name' => $file['name']
												);
							$new_record_action = $new_log_action->log_action_save($log_action_args);

							$msg = 'The file has been edited succesfuly.';
							echo system_message('ok',$msg);
							
							include(ROOT_DIR.'/upload-send-notifications.php');
						}
					}
				}
			}
			/** Validations OK, show the editor */
	?>
			<form action="edit-file.php?file_id=<?php echo $this_file_id; ?>" method="post" name="edit_file" id="edit_file">
				<?php
					/** Reconstruct the current assignments arrays */
					$file_on_clients = array();
					$file_on_groups = array();
					$assignments_query = 'SELECT file_id, client_id, group_id FROM tbl_files_relations WHERE file_id="' . $this_file_id . '"';
					$assignments_sql = $database->query($assignments_query);
					$assignments_count = mysql_num_rows($assignments_sql);
					if ($assignments_count > 0) {
						while ($assignment_row = mysql_fetch_array($assignments_sql)) {
							if (!empty($assignment_row['client_id'])) {
								$file_on_clients[] = $assignment_row['client_id'];
							}
							elseif (!empty($assignment_row['group_id'])) {
								$file_on_groups[] = $assignment_row['group_id'];
							}
						}
					}

					$i = 1;
					$files_query = 'SELECT * FROM tbl_files WHERE id="' . $this_file_id . '"';
					$sql = $database->query($files_query);
					while($row = mysql_fetch_array($sql)) {
				?>
						<div class="row-fluid edit_files">
							<div class="span1">
								<div class="file_number">
									<p><?php echo $i; ?></p>
								</div>
							</div>
							<div class="span11 file_data">
								<div class="row-fluid">
									<div class="span6">
										<div class="row-fluid">
											<div class="span12">
												<p class="on_disc_name">
													<?php echo $row['url']; ?>
												</p>
												<label><?php _e('Name', 'cftp_admin');?></label>
												<input type="text" name="file[<?php echo $i; ?>][name]" value="<?php echo $row['filename']; ?>" class="required" />
												<label><?php _e('Description', 'cftp_admin');?></label>
												<textarea name="file[<?php echo $i; ?>][description]" class="txtfield"><?php echo (!empty($row['description'])) ? $row['description'] : ''; ?></textarea>

												<?php if ($global_level != 0) { ?>
													<label><input type="checkbox" name="file[<?php echo $i; ?>][hidden]" value="1" /> <?php _e('Mark as hidden (will not send notifications) for new assigned clients and groups', 'cftp_admin');?></label>
												<?php } ?>
											</div>
										</div>
									</div>
									<div class="span6">
										<?php
											/**
											* Only show the CLIENTS select field if the current
											* uploader is a system user, and not a client.
											*/
											if ($global_level != 0) {
										?>
												<label><?php _e('Assign this file to', 'cftp_admin');?>:</label>
												<select multiple="multiple" name="file[<?php echo $i; ?>][assignments][]" class="assign_select" >
													<optgroup label="<?php _e('Clients', 'cftp_admin');?>">
														<?php
															/**
															 * The clients list is generated early on the file so the
															 * array doesn't need to be made once on every file.
															 */
															foreach($clients as $client => $client_name) {
															?>
																<option value="<?php echo 'c'.$client; ?>"<?php if (in_array($client,$file_on_clients)) { echo ' selected="selected"'; } ?>>
																	<?php echo $client_name; ?>
																</option>
															<?php
															}
														?>
													<optgroup label="<?php _e('Groups', 'cftp_admin');?>">
														<?php
															/**
															 * The groups list is generated early on the file so the
															 * array doesn't need to be made once on every file.
															 */
															foreach($groups as $group => $group_name) {
															?>
																<option value="<?php echo 'g'.$group; ?>"<?php if (in_array($group,$file_on_groups)) { echo ' selected="selected"'; } ?>>
																	<?php echo $group_name; ?>
																</option>
															<?php
															}
														?>
												</select>
												<div class="list_mass_members">
													<a href="#" class="btn add-all"><?php _e('Add all','cftp_admin'); ?></a>
													<a href="#" class="btn remove-all"><?php _e('Remove all','cftp_admin'); ?></a>
												</div>
										<?php
											} /** Close $current_level check */
										?>
									</div>
								</div>
							</div>
						</div>
				<?php
					}
				?>
				<div align="right">
					<input type="submit" name="submit" value="<?php _e('Continue','cftp_admin'); ?>" class="button button_blue button_submit" id="upload_continue" />
				</div>
			</form>
	<?php
		}

		$database->Close();
	?>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$('.assign_select').multiSelect({
			selectableHeader: "<div class='multiselect_header'><?php _e('Available','cftp_admin'); ?></div>",
			selectionHeader: "<div class='multiselect_header'><?php _e('Selected','cftp_admin'); ?></div>"
		})
		$('.add-all').click(function(){
		  $(this).parent().parent().find('select').multiSelect('select_all');
		  return false;
		});
		$('.remove-all').click(function(){
		  $(this).parent().parent().find('select').multiSelect('deselect_all');
		  return false;
		});

		$("form").submit(function() {
			clean_form(this);

			$(this).find('input[name$="[name]"]').each(function() {	
				is_complete($(this)[0],'<?php echo $validation_no_name; ?>');
			});

			// show the errors or continue if everything is ok
			if (show_form_errors() == false) { return false; }

		});
	});
</script>

<?php include('footer.php'); ?>
<?php
/**
 * Uploading files, step 2
 *
 * This file handles all the uploaded files, whether you are
 * coming from the "Upload from computer" or "Import from FTP"
 * pages. The only difference is from which POST array it takes
 * the information to list the avaiable files to process.
 *
 * It can display up tp 3 tables:
 * One that will list all the files that were brought in from
 * the first step. One with the confirmed uploaded and assigned
 * files, and a possible third one with the ones that failed.
 *
 * @package ProjectSend
 * @subpackage Upload
 */
$tablesorter = 1;
$multiselect = 1;
$allowed_levels = array(9,8,7,0);
require_once('sys.includes.php');
$page_title = __('Upload files', 'cftp_admin');
include('header.php');
?>

<div id="main">
	<h2><?php echo $page_title; ?></h2>

<?php
$database->MySQLDB();

/**
 * Get the user level to determine if the uploader is a
 * system user or a client.
 */
$current_level = get_current_user_level();

$work_folder = USER_UPLOADS_TEMP_FOLDER;

/** Coming from the web uploader */
if(isset($_POST['finished_files'])) {
	$uploaded_files = array_filter($_POST['finished_files']);
}
/** Coming from upload by FTP */
if(isset($_POST['add'])) {
	$uploaded_files = $_POST['add'];
}

/**
 * A hidden field sends the list of failed files as a string,
 * where each filename is separated by a comma.
 * Here we change it into an array so we can list the files
 * on a separate table.
 */
if(isset($_POST['upload_failed'])) {
	$upload_failed_hidden_post = array_filter(explode(',',$_POST['upload_failed']));
}
/**
 * Files that failed are removed from the uploaded files list.
 */
if(isset($upload_failed_hidden_post) && count($upload_failed_hidden_post) > 0) {
	foreach ($upload_failed_hidden_post as $failed) {
		$delete_key = array_search($failed, $uploaded_files);					
		unset($uploaded_files[$delete_key]);
	}
}

/** Define the arrays */
$upload_failed = array();
$move_failed = array();
$clients_to_email = array();

/**
 * Another hidden field sends the list of e-mails from users
 * which have new files assigned.
 * This is also passed as a string separated by commas, and
 * transformed into a filtered array.
 * E-mails are only sent after there are no more files to
 * assign, so they must be sent on the form on every step so
 * no clients are lost.
 */
if(isset($_POST['upload_email_clients'])) {
	$clients_to_email = array_filter(explode(',',$_POST['upload_email_clients']));
}

/**
 * $empty_fields counts the amount of "name" fields that
 * were not completed.
 */
$empty_fields = 0;

/** Fill the clients array that will be used on the form */
$clients = array();
$cq = "SELECT * FROM tbl_users WHERE level = '0' ORDER BY name ASC";
$sql = $database->query($cq);
	while($row = mysql_fetch_array($sql)) {
	$clients['c'.$row["id"]] = $row["name"];
}
/** Fill the groups array that will be used on the form */
$groups = array();
$cq = "SELECT * FROM tbl_groups ORDER BY name ASC";
$sql = $database->query($cq);
	while($row = mysql_fetch_array($sql)) {
	$groups['g'.$row["id"]] = $row["name"];
}

/**
 * A posted form will include information of the uploaded files
 * (name, description and client).
 */
	if(isset($_POST['submit'])) {
		/**
		 * Get the username of the current logged in account
		 * and use it when saving the files on the database.
		 */
		$this_admin = get_current_user_username();

		foreach ($_POST['file'] as $file) {
			if(!empty($file['name'])) {
				/**
				* If the uploader is a client, set the "client" var to the current
				* uploader username, since the "client" field is not posted.
				*/
				if ($current_level == 0) {
					$file['assignments'] = 'c'.$this_admin;
				}
				
				$this_upload = new PSend_Upload_File();
				$file['file'] = $this_upload->safe_rename($file['file']);
				$location = $work_folder.'/'.$file['file'];
				if(file_exists($location)) {
					$move_arguments = array(
											'uploaded_name' => $location,
											//'move_to_folder' => ROOT_DIR.'/upload/'.$file['client'].'/',
											'move_to_folder' => UPLOADED_FILES_FOLDER,
											'filename' => $file['file']
										);
					$new_filename = $this_upload->upload_move($move_arguments);
					if (!empty($new_filename)) {
						$delete_key = array_search($file['original'], $uploaded_files);					
						unset($uploaded_files[$delete_key]);
						$add_arguments = array(
												'file' => $new_filename,
												'name' => $file['name'],
												'description' => $file['description'],
												'assign_to' => $file['assignments'],
												'uploader' => $this_admin
											);
						if($this_upload->upload_add_to_database($add_arguments)) {
							$upload_finish[] = array(
													'file' => $file['file'],
													'name' => $file['name'],
													'description' => $file['description'],
													'assignments' => $file['assignments'],
													'client_name' => $clients[$file['assignments']]
												);
							/**
							 * If the uploader is a client, notify the user who
							 * created this clients' account.
							 */
							if ($current_level == 0) {
								$who_made_me = get_client_by_username(get_current_user_username());
								$creator_info = get_user_by_username($who_made_me['created_by']);
								$clients_to_email[] = $creator_info['email'];
							}
							else {
								$clients_to_email[] = $file['client'];
							}
						}
					}
				}
			}
			else {
				$empty_fields++;
			}
		}

		/**
		 * E-mail the clients which just got new files uploaded.
		 * First, remove any duplicates from the array of clients.
		 */
		$clients_to_email = array_unique($clients_to_email);

		if(empty($uploaded_files) && !empty($upload_finish)) {
			foreach ($clients_to_email as $client) {
				$get_notify_email = check_if_notify_client($client);
				if ($get_notify_email != false) {
					$notify_client = new PSend_Email();
					/**
					 * When sending the e-mail, save the returned value of
					 * the function to an array where the key is the username
					 * of the client.
					 * This is used later on the confirmation table, in the
					 * Notify column.
					 * The possible returned values are 1 for ok and 2 for
					 * error.
					 */
					$users_emailed[$client] = $notify_client->psend_send_email('new_file_by_user',$get_notify_email);
				}
				else {
					/** A value of 0 means that notify is disable for this client */
					$users_emailed[$client] = 0;
				}
			}
		}

	}
	
	/**
	 * Generate the table of files that were assigned to a client
	 * on this last POST. These files appear on this table only once,
	 * so if there is another submition of the form, only the new
	 * assigned files will be displayed.
	 */
	if(!empty($upload_finish)) {
?>
		<h3><?php _e('Files uploaded correctly','cftp_admin'); ?></h3>
		<table id="uploaded_files_tbl" class="tablesorter edit_files vertical_middle">
			<thead>
				<tr>
					<th><?php _e('File Name','cftp_admin'); ?></th>
					<th><?php _e('Name','cftp_admin'); ?></th>
					<th><?php _e('Description','cftp_admin'); ?></th>
					<?php
						/*
						 * Don't generate this columns if the uploader is a client.
						 */
						if ($current_level != 0) {
					?>
							<th><?php _e('Assigned to','cftp_admin'); ?></th>
							<th><?php _e('Client was notified','cftp_admin'); ?></th>
					<?php
						}
					?>
					<th><?php _e("Actions",'cftp_admin'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
				foreach($upload_finish as $uploaded) {
			?>
					<tr>
						<td><?php echo $uploaded['file']; ?></td>
						<td><?php echo $uploaded['name']; ?></td>
						<td><?php echo $uploaded['description']; ?></td>
						<?php
							/*
							 * Don't generate this columns if the uploader is a client.
							 */
							if ($current_level != 0) {
						?>
							<td><?php echo $uploaded['client_name']; ?></td>
							<td><?php
									/**
									 * Unset $reason so the next client will start fresh
									 */
									if(isset($reason)) {
										unset($reason);
									}
									switch ($users_emailed[$uploaded['client']]) {
										case 0:
											_e('No','cftp_admin');
											$reason = __('Disabled for this client','cftp_admin');
											break;
										case 1:
											_e('Yes','cftp_admin');
											break;
										case 2:
											_e('No','cftp_admin');
											$reason = __('Error sending notification','cftp_admin');
											break;
									}
									echo (isset($reason) ? ' ('.$reason.')' : '');
								?>
							</td>
						<?php
							}
						?>
						<td>
							<?php
								/*
								 * Show the different actions buttons depending on the uploader
								 * account type (user or client).
								 */
								if ($current_level != 0) {
							?>
									<a href="edit-file.php?id=" class="button button_blue"><?php _e('Edit file','cftp_admin'); ?></a>
							<?php
								}
								else {
							?>
									<a href="my_files/" class="button button_blue"><?php _e('View my files','cftp_admin'); ?></a>
							<?php
								}
							?>
						</td>
					</tr>
			<?php
				}
			?>
			</tbody>
		</table>
<?php
		/**
		 * If the uploader is a client, files are correctly assigned,
		 * and no files are left, notify the system administrator.
		 */
		if ($current_level == 0) {
			if(empty($uploaded_files)) {
				$client_uploader_id = get_client_by_username($this_admin);
				$notify_sysadmin = new PSend_Email();
				$email_sysadmin = $notify_sysadmin->psend_send_email('new_file_by_client',ADMIN_EMAIL_ADDRESS,'','',$client_uploader_id['id']);
				
				if ($email_sysadmin == 1) {
					$msg = __('An e-mail was sent to the site administrator as a notification about the new files.','cftp_admin');
					echo system_message('ok',$msg);
				}
				else {
					$msg = __('The site administrator could not be e-mailed about the new files.','cftp_admin');
					echo system_message('error',$msg);
				}
			}
		}

	}

		/**
		 * Generate the table of files ready to be assigned to a client.
		 */
		if(!empty($uploaded_files)) {
	?>
			<h3><?php _e('Files ready to upload','cftp_admin'); ?></h3>
			<p><?php _e('Please complete the following information to finish the uploading proccess. Remember that "Name" is a required field.','cftp_admin'); ?></p>
	<?php
			/**
			 * First, do a server side validation for files that were submited
			 * via the form, but the name field was left empty.
			 */
			if(!empty($empty_fields)) {
				$msg = 'Name and client are required fields for all uploaded files.';
				echo system_message('error',$msg);
			}
	?>

			<script type="text/javascript">
				$(document).ready(function() {
					$("form").submit(function() {
						clean_form(this);

						$(this).find('input[name$="[name]"]').each(function() {	
							is_complete($(this)[0],'<?php echo $validation_no_name; ?>');
						});
						$(this).find('select').each(function() {	
							is_selected($(this)[0],'<?php echo $validation_no_client; ?>');
						});

						// show the errors or continue if everything is ok
						if (show_form_errors() == false) { return false; }

					});
				});
			</script>

			<form action="upload-process-form.php" name="save_files" id="save_files" method="post">
				<?php
					foreach($uploaded_files as $add_uploaded_field) {
						echo '<input type="hidden" name="finished_files[]" value="'.$add_uploaded_field.'" />
						';
					}
				?>
				<table id="edit_files_tbl" class="tablesorter edit_files">
					<thead>
						<tr>
							<th style="width:65px;"><?php _e('File','cftp_admin'); ?></th>
							<th><?php _e('File Name','cftp_admin'); ?></th>
							<th><?php _e('Information','cftp_admin'); ?></th>
							<?php
								/**
								* Only show the ASSIGN TO column if the current
								* uploader is a system user, and not a client.
								*/
								if ($current_level != 0) {
							?>
									<th><?php _e('Assign to','cftp_admin'); ?></th>
							<?php
								}
							?>
						</tr>
					</thead>
					<tbody>
						<?php
							$i = 0;
							foreach ($uploaded_files as $file) {
								clearstatcache();
								$this_upload = new PSend_Upload_File();
								$file_original = $file;
								/** Generate a safe filename */
								$file = $this_upload->safe_rename($file);
		
								$location = $work_folder.'/'.$file;

								/**
								 * Check that the file is indeed present on the folder.
								 * If not, it is added to the failed files array.
								 */
								if(file_exists($location)) {
									/**
									 * Remove the extension from the file name and replace every
									 * underscore with a space to generate a valid upload name.
									 */
									$filename_no_ext = substr($file, 0, strrpos($file, '.'));
									$file_title = str_replace('_',' ',$filename_no_ext);
									if ($this_upload->is_filetype_allowed($file)) {
							?>
											<tr>
												<td class="file_number">
													<p>
														<?php echo $i; ?>
													</p>
												</td>
												<td>
													<p class="on_disc_name"><?php echo $file; ?></p>
													<input type="hidden" name="file[<?php echo $i; ?>][original]" value="<?php echo $file_original; ?>" />
													<input type="hidden" name="file[<?php echo $i; ?>][file]" value="<?php echo $file; ?>" />
												</td>
												<td>
													<label><?php _e('Name', 'cftp_admin');?></label>
													<input type="text" name="file[<?php echo $i; ?>][name]" value="<?php echo $file_title; ?>" class="txtfield required" />
													<label><?php _e('Description', 'cftp_admin');?></label>
													<textarea name="file[<?php echo $i; ?>][description]" class="txtfield"></textarea>
												</td>
												<?php
													/**
													* Only show the CLIENTS select field if the current
													* uploader is a system user, and not a client.
													*/
													if ($current_level != 0) {
												?>
														<td>
															<select multiple="multiple" name="file[<?php echo $i; ?>][assignments][]" class="assign_select" >
																<optgroup label="<?php _e('Clients', 'cftp_admin');?>">
																	<?php
																		/**
																		 * The clients list is generated early on the file so the
																		 * array doesn't need to be made once on every file.
																		 */
																		foreach($clients as $client => $client_name) {
																		?>
																			<option value="<?php echo $client; ?>"><?php echo $client_name; ?></option>
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
																			<option value="<?php echo $group; ?>"><?php echo $group_name; ?></option>
																		<?php
																		}
																	?>
															</select>
														</td>
												<?php
													}
												?>
											</tr>
							<?php
										$i++;
									}
								}
								else {
									$upload_failed[] = $file;
								}
							}
						?>
					</tbody>
				</table>
				<?php
					/**
					 * Take the list of failed files and store them as a text string
					 * that will be passed on a hidden field when posting the form.
					 */
					$upload_failed = array_filter($upload_failed);
					$upload_failed_hidden = implode(',',$upload_failed);
				?>
				<input type="hidden" name="upload_failed" value="<?php echo $upload_failed_hidden; ?>" />
		
				<?php
					/**
					 * Pass the clients to email on a hidden input, so only when the
					 * queue is empty we send the e-mails to avoid sending several
					 * copies to the same client.
					 */
					if(!empty($clients_to_email)) {
						$clients_to_email = array_filter($clients_to_email);
						$clients_to_email = implode(',',$clients_to_email);
				?>
						<input type="hidden" name="upload_email_clients" value="<?php echo $clients_to_email; ?>" />
				<?php
					}
				?>
		
				<div align="right">
					<input type="submit" name="submit" value="<?php _e('Continue','cftp_admin'); ?>" class="button button_blue button_submit" id="upload_continue" />
				</div>
			</form>

	<?php
		}
		
		/**
		 * Generate the table for the failed files.
		 */
		if(count($upload_failed) > 0) {
	?>
			<h3><?php _e('Files not uploaded','cftp_admin'); ?></h3>
			<table id="failed_files_tbl" class="tablesorter edit_files">
				<thead>
					<tr>
						<th><?php _e('File Name','cftp_admin'); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
					foreach($upload_failed as $failed) {
				?>
						<tr>
							<td><?php echo $failed; ?></td>
						</tr>
				<?php
					}
				?>
				</tbody>
			</table>
	<?php
		}
	?>

</div>

<script type="text/javascript">
	$(document).ready(function() {
		<?php
			if(!empty($uploaded_files)) {
		?>
				$("#edit_files_tbl").tablesorter( {
					sortList: [[0,0]], widgets: ['zebra'], headers: {
						2: { sorter: false }, 
						3: { sorter: false }
					}
				})

				// Autoclick the continue button
				//$('#upload_continue').click();

				$('.assign_select').multiSelect({
					selectableHeader: "<div class='multiselect_header'><?php _e('Available','cftp_admin'); ?></div>",
					selectionHeader: "<div class='multiselect_header'><?php _e('Assign to','cftp_admin'); ?></div>"
				})

		<?php
			}
			if(!empty($upload_finish)) {
		?>
				$("#uploaded_files_tbl").tablesorter( {
					sortList: [[0,0]], widgets: ['zebra'], headers: {
						<?php
							/**
							 * Different sortable columns if the upload is client or user.
							 */
							if ($current_level != 0) {
						?>
								5: { sorter: false }
						<?php
							}
							else {
						?>
								3: { sorter: false }
						<?php
							}
						?>
					}
				})
				
		<?php
			}
		?>
	});
</script>

<?php
	$database->Close();
	include('footer.php');
?>
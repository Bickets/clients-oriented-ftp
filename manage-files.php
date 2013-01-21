<?php
/**
 * Allows to hide, show or delete the files assigend to the
 * selected client.
 *
 * @package ProjectSend
 */
$tablesorter = 1;
$allowed_levels = array(9,8);
require_once('sys.includes.php');

/**
 * The client's id is passed on the URI.
 * Then get_client_by_id() gets all the other account values.
 */
$this_id = $_GET['client_id'];
$this_client = get_client_by_id($this_id);

$page_title = __('Manage files','cftp_admin');

/** Add the name of the client to the page's title. */
if(!empty($this_client)) {
	$page_title .= ' for '.html_entity_decode($this_client['name']);
}

include('header.php');
?>

<script type="text/javascript">
	$(document).ready(function() {
		$("#files_list")
			.tablesorter( {
				sortList: [[1,1]], widgets: ['zebra'], headers: {
					0: { sorter: false },
					8: { sorter: false }
				}
		})
		.tablesorterPager({container: $("#pager")})

		$("#select_all").click(function(){
			var status = $(this).prop("checked");
			$("td>input:checkbox").prop("checked",status);
		});

		$("#do_action").click(function() {
			var checks = $("td>input:checkbox").serializeArray(); 
			if (checks.length == 0) { 
				alert('<?php _e('Please select at least one file to proceed.','cftp_admin'); ?>');
				return false; 
			} 
			else {
				var action = $('#files_actions').val();
				if (action == 'delete') {
					var msg_1 = '<?php _e("You are about to delete",'cftp_admin'); ?>';
					var msg_2 = '<?php _e("files permanently and for every client/group. Are you sure you want to continue?",'cftp_admin'); ?>';
					if (confirm(msg_1+' '+checks.length+' '+msg_2)) {
						return true;
					} else {
						return false;
					}
				}
				else if (action == 'unassign') {
					var msg_1 = '<?php _e("You are about to unassign",'cftp_admin'); ?>';
					var msg_2 = '<?php _e("files from this account. Are you sure you want to continue?",'cftp_admin'); ?>';
					if (confirm(msg_1+' '+checks.length+' '+msg_2)) {
						return true;
					} else {
						return false;
					}
				}
			}
		});

	});
</script>

<div id="main">
	<h2><?php echo $page_title; ?></h2>

	<?php
		/**
		 * Show an error message if no ID value is passed on the URI.
		 */
		if(empty($this_id)) {
	?>
			<div class="whiteform whitebox whitebox_text">
				<p><?php _e('Please go to the clients administration page and select "Manage files" from any client.','cftp_admin'); ?></p>
			</div>
	<?php
		}
		else {
	?>
	
		<?php

			/**
			 * Apply the corresponding action to the selected files.
			 */
			if(isset($_POST['do_action'])) {
				/** Continue only if 1 or more files were selected. */
				if(!empty($_POST['files'])) {
					$selected_files = $_POST['files'];
					switch($_POST['files_actions']) {
						case 'hide':
							/**
							 * Changes the value on the "hidden" column value on the database.
							 * This files are not shown on the client's file list. They are
							 * also not counted on the home.php files count when the logged in
							 * account is the client.
							 */
							foreach ($selected_files as $work_file) {
								$this_file = new FilesActions();
								$hide_file = $this_file->change_files_hide_status($work_file,'1');
							}
							$msg = __('The selected files were marked as hidden.','cftp_admin');
							echo system_message('ok',$msg);
							break;

						case 'show':
							/**
							 * Reverse of the previous action. Setting the value to 0 means
							 * that the file is visible.
							 */
							foreach ($selected_files as $work_file) {
								$this_file = new FilesActions();
								$show_file = $this_file->change_files_hide_status($work_file,'0');
							}
							$msg = __('The selected files were marked as visible.','cftp_admin');
							echo system_message('ok',$msg);
							break;

						case 'unassign':
							/**
							 * Remove the file from this client's account only.
							 */
							foreach ($selected_files as $work_file) {
								$this_file = new FilesActions();
								$unassign_file = $this_file->unassign_file($work_file);
							}
							$msg = __('The selected files were unassigned from this client.','cftp_admin');
							echo system_message('ok',$msg);
							break;

						case 'delete':
							foreach ($selected_files as $work_file) {
								$this_file = new FilesActions();
								$delete_file = $this_file->delete_files($work_file);
							}
							$msg = __('The selected files were deleted.','cftp_admin');
							echo system_message('ok',$msg);
							break;
					}
				}
				else {
					$msg = __('Please select at least one file.','cftp_admin');
					echo system_message('error',$msg);
				}
			}

			$database->MySQLDB();
			$cq = 'SELECT * FROM tbl_files_relations WHERE client_id="' . $this_id .'"';

			/** Add the status filter */	
			if(isset($_POST['status']) && $_POST['status'] != 'all') {
				$status_filter = $_POST['status'];
				$cq .= " AND hidden='$status_filter'";
				$no_results_error = 'filter';
			}

			/** Add the download count filter */	
			if(isset($_POST['download_count']) && $_POST['download_count'] != 'all') {
				$count_filter = $_POST['download_count'];
				switch ($count_filter) {
					case '0':
						$cq .= " AND download_count='$count_filter'";
						break;
					case '1':
						$cq .= " AND download_count >='$count_filter'";
						break;
				}
				$no_results_error = 'filter';
			}

			/**
			 * Count the files assigned to this client. If there is none, show
			 * an error message.
			 */
			$sql = $database->query($cq);

			if (mysql_num_rows($sql) > 0) {
				/**
				 * Get the IDs of files that match the previous query.
				 */
				while($row_files = mysql_fetch_array($sql)) {
					$files_ids[] = $row_files['file_id'];
					$gotten_files = implode(',',$files_ids);
				}
				
				/**
				 * Get the files
				 */
				$fq = "SELECT * from tbl_files WHERE id IN ($gotten_files)";
	
				/** Add the search terms */	
				if(isset($_POST['search']) && !empty($_POST['search'])) {
					$search_terms = $_POST['search'];
					$fq .= " AND (filename LIKE '%$search_terms%' OR description LIKE '%$search_terms%')";
					$no_results_error = 'search';
				}
	
				$sql_files = $database->query($fq);
				$count = mysql_num_rows($sql_files);
			}
			else {
				$count = 0;
				$no_results_error = 'filter';
			}
		?>
			<div class="form_actions_left">
				<div class="form_actions_limit_results">
					<form action="manage-files.php?client_id=<?php echo $this_id; ?>" name="files_search" method="post" class="inline_form">
						<input type="text" name="search" id="search" value="<?php if(isset($_POST['search']) && !empty($_POST['search'])) { echo $_POST['search']; } ?>" class="txtfield form_actions_search_box" />
						<input type="submit" id="btn_proceed_search" value="<?php _e('Search','cftp_admin'); ?>" class="button_form" />
					</form>
	
					<form action="manage-files.php?client_id=<?php echo $this_id; ?>" name="files_filters" method="post" class="inline_form">
						<select name="status" id="status" class="txtfield">
							<option value="all"><?php _e('All statuses','cftp_admin'); ?></option>
							<option value="1"><?php _e('Hidden','cftp_admin'); ?></option>
							<option value="0"><?php _e('Visible','cftp_admin'); ?></option>
						</select>
						<select name="download_count" id="download_count" class="txtfield">
							<option value="all"><?php _e('Download count','cftp_admin'); ?></option>
							<option value="all"><?php _e('Indistinct','cftp_admin'); ?></option>
							<option value="0"><?php _e('0 times','cftp_admin'); ?></option>
							<option value="1"><?php _e('1 or more times','cftp_admin'); ?></option>
						</select>
						<input type="submit" id="btn_proceed_filter_clients" value="<?php _e('Filter','cftp_admin'); ?>" class="button_form" />
					</form>
				</div>
			</div>
	
			<form action="manage-files.php?client_id=<?php echo $this_id; ?>" name="files_list" method="post">
				<div class="form_actions_right">
					<div class="form_actions">
						<div class="form_actions_submit">
							<label><?php _e('Selected files actions','cftp_admin'); ?>:</label>
							<select name="files_actions" id="files_actions" class="txtfield">
								<option value="hide"><?php _e('Hide','cftp_admin'); ?></option>
								<option value="show"><?php _e('Show','cftp_admin'); ?></option>
								<option value="unassign"><?php _e('Unassign','cftp_admin'); ?></option>
								<option value="delete"><?php _e('Delete for everyone','cftp_admin'); ?></option>
							</select>
							<input type="submit" name="do_action" id="do_action" value="<?php _e('Proceed','cftp_admin'); ?>" class="button_form" />
						</div>
					</div>
				</div>

				<div class="clear"></div>
		
				<div class="form_actions_count">
					<p class="form_count_total"><?php _e('Showing','cftp_admin'); ?>: <span><?php echo $count; ?> <?php _e('files','cftp_admin'); ?></span></p>
				</div>
		
				<div class="clear"></div>

				<?php
					if (!$count) {
						if (isset($no_results_error)) {
							switch ($no_results_error) {
								case 'search':
									$no_results_message = __('Your search keywords returned no results.','cftp_admin');;
									break;
								case 'filter':
									$no_results_message = __('The filters you selected returned no results.','cftp_admin');;
									break;
								case 'none_assigned':
									$no_results_message = __('There are no files assigned to this client.','cftp_admin');;
									break;
							}
						}
						else {
							$no_results_message = __('There are no files for this client.','cftp_admin');;
						}
						echo system_message('error',$no_results_message);
					}
				?>

				<table id="files_list" class="tablesorter">
					<thead>
						<tr>
							<th class="td_checkbox">
								<input type="checkbox" name="select_all" id="select_all" value="0" />
							</th>
							<th><?php _e('Uploaded','cftp_template'); ?></th>
							<th><?php _e('Name','cftp_template'); ?></th>
							<th><?php _e('Description','cftp_template'); ?></th>
							<th><?php _e('Size','cftp_template'); ?></th>
							<th><?php _e('Status','cftp_template'); ?></th>
							<th><?php _e('Uploader','cftp_template'); ?></th>
							<th><?php _e('Download count','cftp_template'); ?></th>
							<th><?php _e('Actions','cftp_template'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
							if ($count > 0) {
								while($row = mysql_fetch_array($sql_files)) {
									/**
									 * Construct the complete file URI to use on the download button.
									 */
									$this_file_uri = UPLOADED_FILES_FOLDER.$row['url'];
									$sql_this_file = $database->query("SELECT * FROM tbl_files_relations WHERE file_id='".$row['id']."' AND client_id = ".$this_id);
									while($data_file = mysql_fetch_array($sql_this_file)) {
										$file_id = $data_file['id'];
										$hidden = $data_file['hidden'];
										$download_count = $data_file['download_count'];
									}
						?>
									<tr>
										<td><input type="checkbox" name="files[]" value="<?php echo $file_id; ?>" /></td>
										<td><?php echo date(TIMEFORMAT_USE, $row['timestamp']); ?></td>
										<td><strong><?php echo htmlentities($row['filename']); ?></strong></td>
										<td><?php echo htmlentities($row['description']); ?></td>
										<td><?php $this_file = filesize($this_file_uri); echo format_file_size($this_file); ?></td>
										<td class="<?php echo ($hidden === '1') ? 'file_status_hidden' : 'file_status_visible'; ?>">
											<?php
												$status_hidden = __('Hidden','cftp_admin');
												$status_visible = __('Visible','cftp_admin');
												echo ($hidden === '1') ? $status_hidden : $status_visible;
											?>
										</td>
										<td><?php echo $row['uploader']; ?></td>
										<td><?php echo $download_count; ?></td>
										<td>
											<a href="<?php echo $this_file_uri; ?>" target="_blank" class="button button_blue">
												<?php _e('Download','cftp_template'); ?>
											</a>
											<a href="edit-file.php?file_id=<?php echo $row["id"]; ?>" target="_blank" class="button button_blue">
												<?php _e('Edit file','cftp_template'); ?>
											</a>
										</td>
									</tr>
						<?php
								}
							}
						?>
					</tbody>
				</table>
			</form>

			<?php if ($count > 10) { ?>
				<div id="pager" class="pager">
					<form>
						<input type="button" class="first pag_btn" value="<?php _e('First','cftp_admin'); ?>" />
						<input type="button" class="prev pag_btn" value="<?php _e('Prev.','cftp_admin'); ?>" />
						<span><strong><?php _e('Page','cftp_admin'); ?></strong>:</span>
						<input type="text" class="pagedisplay" disabled="disabled" />
						<input type="button" class="next pag_btn" value="<?php _e('Next','cftp_admin'); ?>" />
						<input type="button" class="last pag_btn" value="<?php _e('Last','cftp_admin'); ?>" />
						<span><strong><?php _e('Show','cftp_admin'); ?></strong>:</span>
						<select class="pagesize">
							<option selected="selected" value="10">10</option>
							<option value="20">20</option>
							<option value="30">30</option>
							<option value="40">40</option>
						</select>
					</form>
				</div>
			<?php } else { ?>
				<div id="pager">
					<form>
						<input type="hidden" value="<?php echo $count; ?>" class="pagesize" />
					</form>
				</div>
			<?php } ?>

			<div class="message message_info"><?php _e('Please note that downloading a file from here will not add to the download count.','cftp_admin'); ?></div>

	<?php
		/**
		 * End the IF statement for counting files.
		 */
		}
	
		$database->Close();
	?>

	</div>

</div>

<?php include('footer.php'); ?>
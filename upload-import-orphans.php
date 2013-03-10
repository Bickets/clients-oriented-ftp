<?php
/**
 * Find files that where uploaded but not assigned, step 1
 * Shows a list of files found on the upload/ folder,
 * if they are allowed according to the sytem settings.
 * Files uploaded by the "Upload from computer" form also
 * remain on this folder until assigned to a client, so if
 * that form was not completed, the files can be imported
 * from here later on.
 * Submits an array of file names.
 *
 * @package ProjectSend
 * @subpackage Upload
 */
$tablesorter = 1;
$allowed_levels = array(9,8,7);
require_once('sys.includes.php');
$page_title = __('Find orphan files', 'cftp_admin');
include('header.php');

$database->MySQLDB();

/**
 * Use the folder defined on sys.vars.php
 * Composed of the absolute path to that file plus the
 * default uploads folder.
 */
$work_folder = UPLOADED_FILES_FOLDER;
?>

<div id="main">
	<h2><?php echo $page_title; ?></h2>

	<?php
		$msg = __('This list only shows the files that are allowed according to your security settings. If the file type you need to add is not listed here, add the extension to the "Allowed file extensions" box on the options page.', 'cftp_admin');
		echo system_message('info',$msg);
	?>
	
	<?php
		/** Count clients to show an error message, or the form */
		$sql = $database->query("SELECT * FROM tbl_users WHERE level = '0'");
		$count = mysql_num_rows($sql);
		if (!$count) {
			/** Echo the "no clients" default message */
			message_no_clients();
		}
		else {
			/**
			 * Make a list of existing files on the database.
			 * When a file doesn't correspond to a record, it can
			 * be safely renamed.
			 */
			$sql = $database->query("SELECT url, id FROM tbl_files");
			$db_files = array();
			while($row = mysql_fetch_array($sql)) {
				$db_files[$row["url"]] = $row["id"];
			}

			/** Make an array of already assigned files */
			$sql = $database->query("SELECT DISTINCT file_id FROM tbl_files_relations");
			$assigned = array();
			while($row = mysql_fetch_array($sql)) {
				$assigned[] = $row["file_id"];
			}

			/** This array will be compared to files on the DB */
			$found_disc_files = array();

			/** Read the temp folder and list every allowed file */
			if ($handle = opendir($work_folder)) {
				while (false !== ($filename = readdir($handle))) {
					$filename_path = $work_folder.'/'.$filename;
					if(!is_dir($filename_path)) {
						if ($filename != "." && $filename != "..") {
							/** Check types of files that are not on the database */							
							if (!array_key_exists($filename,$db_files)) {
								$file_object = new PSend_Upload_File();
								$new_filename = $file_object->safe_rename_on_disc($filename,$work_folder);
								/** Check if the filetype is allowed */
								if ($file_object->is_filetype_allowed($new_filename)) {
									/** Add it to the array of available files */
									$new_filename_path = $work_folder.'/'.$new_filename;
									$files_to_add[$new_filename] = $new_filename_path;
									$found_disc_files[] = $new_filename;
								}
							}
							else {
								/**
								 * These following files EXIST on DB ($db_files)
								 * but not on the assigned table ($assigned)
								 */
								if(!in_array($db_files[$filename],$assigned)) {
									$files_to_add[$filename] = $filename_path;
								}
							}
						}
					}
				}
				closedir($handle);
			}
			
			foreach ($found_disc_files as $found_file) {
			}
			
			/**
			 * Generate the list of files if there is at least 1
			 * available and allowed.
			 */
			if(isset($files_to_add) && count($files_to_add) > 0) {
		?>

				<form action="upload-process-form.php" name="upload_by_ftp" id="upload_by_ftp" method="post" enctype="multipart/form-data">
					<table id="add_files_from_ftp" class="tablesorter">
						<thead>
							<tr>
								<th class="td_checkbox">
									<input type="checkbox" name="select_all" id="select_all" value="0" />
								</th>
								<th><?php _e('File name','cftp_admin'); ?></th>
								<th><?php _e('File size','cftp_admin'); ?></th>
								<th><?php _e('Last modified','cftp_admin'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
								foreach ($files_to_add as $add_file => $add_file_path) {
									?>
										<tr>
											<td><input type="checkbox" name="add[]" value="<?php echo $add_file; ?>" /></td>
											<td><?php echo $add_file; ?></td>
											<td><?php echo format_file_size(filesize($add_file_path)); ?></td>
											<td><?php echo date(TIMEFORMAT_USE, filemtime($add_file_path)); ?></td>
										</tr>
									<?php
								}
							?>
						</tbody>
					</table>

					<?php
						$msg = __('Please note that the listed files will be renamed if they contain invalid characters.','cftp_admin');
						echo system_message('info',$msg);
					?>
	
					<ul class="form_fields">
						<li class="form_submit_li">
							<input type="submit" name="Submit" value="<?php _e('Continue','cftp_admin'); ?>" class="button button_blue button_submit" />
						</li>
					</ul>
				</form>
	
				<script type="text/javascript">
					$(document).ready(function() {
						$("#add_files_from_ftp").tablesorter( {
							sortList: [[1,1]], widgets: ['zebra'], headers: {
								0: { sorter: false }
							}
						})

						$("#select_all").click(function(){
							var status = $(this).prop("checked");
							$("td>input:checkbox").prop("checked",status);
						});
						
						$("form").submit(function() {
							var checks = $("td>input:checkbox").serializeArray(); 
							if (checks.length == 0) { 
								alert('<?php _e('Please select at least one file to proceed.','cftp_admin'); ?>');
								return false; 
							} 
						});

					});
				</script>
	
	<?php
			}
			else {
			/** No files found */
			?>
				<div class="whitebox whiteform whitebox_text">
					<p><?php _e('There are no files available to add right now.', 'cftp_admin'); ?></p>
					<p>
						<?php
							_e('To use this feature you need to upload your files via FTP to the folder', 'cftp_admin');
							echo ' <strong>'.$work_folder.'</strong>.';
						?>
					</p>
					<p><?php _e('This is the same folder where the files uploaded by the web interface will be stored. So if you finish uploading your files but do not assign them to any clients/groups, the files will still be there for later use.', 'cftp_admin'); ?></p>
				</div>
			<?php
			}
		} /** End if for users count */
	?>

</div>

<?php
	$database->Close();
	include('footer.php');
?>
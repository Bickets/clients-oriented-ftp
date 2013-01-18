<?php
/**
 * Import files uploaded from FTP, step 1
 * Shows a list of files found on the upload/temp folder,
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
$page_title = __('Import files from FTP', 'cftp_admin');
include('header.php');

$database->MySQLDB();

/**
 * Use the folder defined on sys.vars.php
 * Composed of the absolute path to that file plus the
 * default temp folder (upload/temp).
 */
$work_folder = USER_UPLOADS_TEMP_FOLDER;
?>

<div id="main">
	<h2><?php echo $page_title; ?></h2>
	
	<?php
		/** Count clients to show an error message, or the form */
		$sql = $database->query("SELECT * FROM tbl_users WHERE level = '0'");
		$count = mysql_num_rows($sql);
		if (!$count) {
			/** Echo the "no clients" default message */
			message_no_clients();
		}
		else {
			/** Read the temp folder and list every allowed file */
			if ($handle = opendir($work_folder)) {
				while (false !== ($filename = readdir($handle))) {
					$filename_path = $work_folder.'/'.$filename;
					if(!is_dir($filename_path)) {
						if ($filename != "." && $filename != "..") {
							/** Safe rename the file in case it was uploadad by FTP */
							$file_object = new PSend_Upload_File();
							$new_filename = $file_object->safe_rename_on_disc($filename,$work_folder);
							if(isset($new_filename)) {
								/** Check if the filetype is allowed */
								if ($file_object->is_filetype_allowed($new_filename)) {
									/** Add it to the array of available files */
									$files_to_add[$new_filename] = $filename_path;
								}
							}
						}
					}
				}
				closedir($handle);
			}
			
			/**
			 * Generate the list of files if there is at least 1
			 * available and allowed.
			 */
			if(isset($files_to_add) && count($files_to_add) > 0) {
	?>

				<p><strong><?php _e('Important','cftp_admin'); ?>:</strong> <?php _e('This list only shows the files that are allowed according to your security settings. If the file you need to add is not listed here, make sure to add the extension to the "Allowed file extensions" box on the options page.','cftp_admin'); ?></p>
				<p><?php _e('Also, please note that the listed files were renamed if they contained invalid characters.','cftp_admin'); ?></p>

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
							var status = $(this).attr("checked");
							$("td>input:checkbox").attr("checked",status);
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
					<p><?php _e('This is the same folder where the files uploaded by the web interface will be located before you assign them to a client. So if you finish uploading your files but then fail to complete the form, the files will still be there for you to use later.', 'cftp_admin'); ?></p>
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
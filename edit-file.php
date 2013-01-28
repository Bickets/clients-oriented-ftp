<?php
/**
 * Edit a file name or description.
 * Files can only be edited by the uploader and level 9 users.
 *
 * @package ProjectSend
 */
$allowed_levels = array(9,8,7,0);
require_once('sys.includes.php');

/**
 * The file's id is passed on the URI.
 */
if (!empty($_GET['file_id'])) {
	$this_file_id = $_GET['file_id'];
}

$page_title = __('Edit file','cftp_admin');

include('header.php');
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
			/** Validations OK */
	?>
			<form action="edit-file.php?id=<?php echo $this_file_id; ?>" method="post">aaaaaa
			</form>
	<?php
		}

		$database->Close();
	?>

</div>

<?php include('footer.php'); ?>
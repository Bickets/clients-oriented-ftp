<?php
/**
 * Show the form to edit a system user.
 *
 * @package		ProjectSend
 @ @subpackage	Users
 *
 */
$allowed_levels = array(9);
require_once('sys.includes.php');

$page_title = __('Edit system user','cftp_admin');

include('header.php');

$database->MySQLDB();

/** Create the object */
$edit_user = new UserActions();

/** Check if the id parameter is on the URI. */
if (isset($_GET['id'])) {
	$user_id = $_GET['id'];
	/**
	 * Check if the id corresponds to a real user.
	 * Return 1 if true, 2 if false.
	 **/
	$page_status = (user_exists_id($user_id)) ? 1 : 2;
}
else {
	/**
	 * Return 0 if the id is not set.
	 */
	$page_status = 0;
}

/**
 * Get the user information from the database to use on the form.
 */
if ($page_status === 1) {
	$editing = $database->query("SELECT * FROM tbl_users WHERE id=$user_id");
	while($data = mysql_fetch_array($editing)) {
		$add_user_data_name = $data['name'];
		$add_user_data_user = $data['user'];
		$add_user_data_email = $data['email'];
		$add_user_data_level = $data['level'];
		if ($data['active'] == 1) { $add_user_data_active = 1; } else { $add_user_data_active = 0; }
	}
}

if ($_POST) {
	/**
	 * Clean the posted form values to be used on the user actions,
	 * and again on the form if validation failed.
	 * Also, overwrites the values gotten from the database so if
	 * validation failed, the new unsaved values are shown to avoid
	 * having to type them again.
	 */
	$add_user_data_name = mysql_real_escape_string($_POST['add_user_form_name']);
	$add_user_data_email = mysql_real_escape_string($_POST['add_user_form_email']);
	$add_user_data_level = mysql_real_escape_string($_POST['add_user_form_level']);
	$add_user_data_active = (isset($_POST["add_user_form_active"])) ? 1 : 0;

	/** Arguments used on validation and user creation. */
	$edit_arguments = array(
							'id' => $user_id,
							'name' => $add_user_data_name,
							'email' => $add_user_data_email,
							'role' => $add_user_data_level,
							'active' => $add_user_data_active,
							'type' => 'edit_user'
						);

	/**
	 * If the password field, or the verification are not completed,
	 * send an empty value to prevent notices.
	 */
	$edit_arguments['password'] = (isset($_POST['add_user_form_pass'])) ? $_POST['add_user_form_pass'] : '';
	$edit_arguments['password_repeat'] = (isset($_POST['add_user_form_pass2'])) ? $_POST['add_user_form_pass2'] : '';

	/** Validate the information from the posted form. */
	$edit_validate = $edit_user->validate_user($edit_arguments);
	
	/** Create the user if validation is correct. */
	if ($edit_validate == 1) {
		$edit_response = $edit_user->edit_user($edit_arguments);
	}
	
}
?>

<div id="main">
	<h2><?php echo $page_title; ?></h2>
	
	<div class="whiteform whitebox">
		
		<?php
			/**
			 * If the form was submited with errors, show them here.
			 */
			$valid_me->list_errors();
		?>
		
		<?php
			if (isset($edit_response)) {
				/**
				 * Get the process state and show the corresponding ok or error message.
				 */
				switch ($edit_response['query']) {
					case 1:
						$msg = __('User edited correctly.','cftp_admin');
						echo system_message('ok',$msg);

						$saved_user = get_user_by_id($user_id);
						/** Record the action log */
						$new_log_action = new LogActions();
						$log_action_args = array(
												'action' => 13,
												'owner_id' => $global_id,
												'affected_account' => $user_id,
												'affected_account_name' => $saved_user['username']
											);
						$new_record_action = $new_log_action->log_action_save($log_action_args);
					break;
					case 0:
						$msg = __('There was an error. Please try again.','cftp_admin');
						echo system_message('error',$msg);
					break;
				}
			}
			else {
			/**
			 * If not $edit_response is set, it means we are just entering for the first time.
			 */
			 	$direct_access_error = __('This page is not intended to be accessed directly.','cftp_admin');
			 	if ($page_status === 0) {
					$msg = __('No user was selected.','cftp_admin');
					echo system_message('error',$msg);
					echo '<p>'.$direct_access_error.'</p>';
				}
				else if ($page_status === 2) {
					$msg = __('There is no user with that ID number.','cftp_admin');
					echo system_message('error',$msg);
					echo '<p>'.$direct_access_error.'</p>';
				}
				else {
					/**
					 * Include the form.
					 */
					$user_form_type = 'edit_user';
					include('users-form.php');
				}
			}
		?>
		
	</div>
</div>

<?php
	$database->Close();
	include('footer.php');
?>
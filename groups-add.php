<?php
/**
 * Show the form to add a new group.
 *
 * @package		ProjectSend
 @ @subpackage	Groups
 *
 */
$allowed_levels = array(9,8);
require_once('sys.includes.php');

$page_title = __('Add clients group','cftp_admin');

include('header.php');

$database->MySQLDB();

if ($_POST) {
	$new_group = new GroupActions();

	/**
	 * Clean the posted form values to be used on the groups actions,
	 * and again on the form if validation failed.
	 */
	$add_group_data_name = encode_html($_POST['add_group_form_name']);
	$add_group_data_description = encode_html($_POST['add_group_form_description']);

	/** Arguments used on validation and group creation. */
	$new_arguments = array(
							'id' => '',
							'name' => $add_group_data_name,
							'description' => $add_group_data_description
						);

	/** Validate the information from the posted form. */
	$new_validate = $new_group->validate_group($new_arguments);
	
	/** Create the group if validation is correct. */
	if ($new_validate == 1) {
		$new_response = $new_group->create_group($new_arguments);
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
			if (isset($new_response)) {
				/**
				 * Get the process state and show the corresponding ok or error messages.
				 */
				switch ($new_response['query']) {
					case 1:
						$msg = __('Group added correctly.','cftp_admin');
						echo system_message('ok',$msg);
					break;
					case 0:
						$msg = __('There was an error. Please try again.','cftp_admin');
						echo system_message('error',$msg);
					break;
				}
			}
			else {
				/**
				 * If not $new_response is set, it means we are just entering for the first time.
				 * Include the form.
				 */
				$groups_form_type = 'new_group';
				include('groups-form.php');
			}
		?>

	</div>
</div>

<?php
	$database->Close();
	include('footer.php');
?>
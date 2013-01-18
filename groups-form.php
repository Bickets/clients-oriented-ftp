<?php
/**
 * Contains the form that is used when adding or editing groups.
 *
 * @package		ProjectSend
 @ @subpackage	Groups
 *
 */
?>

<script type="text/javascript">
	$(document).ready(function() {
		$("form").submit(function() {
			clean_form(this);

			is_complete(this.add_group_form_name,'<?php echo $validation_no_name; ?>');
			// show the errors or continue if everything is ok
			if (show_form_errors() == false) { return false; }
		});
	});
</script>

<?php
switch ($groups_form_type) {
	case 'new_group':
		$submit_value = __('Create group','cftp_admin');
		$form_action = 'groups-add.php';
		break;
	case 'edit_group':
		$submit_value = __('Save group','cftp_admin');
		$form_action = 'groups-edit.php?id='.$group_id;
		break;
}
?>

<form action="<?php echo $form_action; ?>" name="addgroup" method="post">
	<ul class="form_fields">
		<li>
			<label for="add_group_form_name"><?php _e('Group name','cftp_admin'); ?></label>
			<input type="text" name="add_group_form_name" id="add_group_form_name" class="required" value="<?php echo (isset($add_group_data_name)) ? stripslashes($add_group_data_name) : ''; ?>" />
		</li>
		<li>
			<label for="add_group_form_description"><?php _e('Description','cftp_admin'); ?></label>
			<textarea name="add_group_form_description" id="add_group_form_description"><?php echo (isset($add_group_data_description)) ? stripslashes($add_group_data_description) : ''; ?></textarea>
		</li>
		<li class="form_submit_li">
			<input type="submit" name="Submit" value="<?php echo $submit_value; ?>" class="button button_blue button_submit" />
		</li>
	</ul>
</form>
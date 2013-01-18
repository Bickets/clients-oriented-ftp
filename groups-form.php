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
		<li>
			<label for="add_group_form_members"><?php _e('Members','cftp_admin'); ?></label>
			<select multiple="multiple" id="members-select" name="add_group_form_members[]">
				<?php
					$sql = $database->query("SELECT * FROM tbl_users WHERE level = '0' ORDER BY name ASC");
					while($row = mysql_fetch_array($sql)) {
				?>
						<option value="<?php echo $row["id"]; ?>"
							<?php
								if($groups_form_type == 'edit_group') {
									if (in_array($row["id"],$current_members)) {
										echo ' selected="selected"';
									}
								}
							?>
						><?php echo $row["name"]; ?></option>
				<?php
					}
				?>
			</select>
			<div class="list_mass_members">
				<a href="#" class="button button_gray button_big" id="add-all"><?php _e('Add all','cftp_admin'); ?></a>
				<a href="#" class="button button_gray button_big" id="remove-all"><?php _e('Remove all','cftp_admin'); ?></a>
			</div>
		</li>
		<li class="form_submit_li">
			<input type="submit" name="Submit" value="<?php echo $submit_value; ?>" class="button button_blue button_submit" />
		</li>
	</ul>
</form>

<script type="text/javascript">
	$(document).ready(function() {
		$('#members-select').multiSelect({
			selectableHeader: "<div class='multiselect_header'><?php _e('Available','cftp_admin'); ?></div>",
			selectionHeader: "<div class='multiselect_header'><?php _e('Assigned to this group','cftp_admin'); ?></div>"
		})
		$('#add-all').click(function(){
		  $('#members-select').multiSelect('select_all');
		  return false;
		});
		$('#remove-all').click(function(){
		  $('#members-select').multiSelect('deselect_all');
		  return false;
		});
	});
</script>

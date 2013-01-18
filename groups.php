<?php
/**
 * Show the list of current groups.
 *
 * @package		ProjectSend
 @ @subpackage	Groups
 *
 */
$tablesorter = 1;
$allowed_levels = array(9,8);
require_once('sys.includes.php');
$page_title = __('Groups administration','cftp_admin');;
include('header.php');
?>

<script type="text/javascript">
	$(document).ready( function() {
		$("#groups_tbl").tablesorter( {
			sortList: [[1,0]], widgets: ['zebra'], headers: {
				0: { sorter: false }, 
				6: { sorter: false }
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
				alert('<?php _e('Please select at least one group to proceed.','cftp_admin'); ?>');
				return false; 
			}
			else {
				var msg_1 = '<?php _e("You are about to delete",'cftp_admin'); ?>';
				var msg_2 = '<?php _e("groups. Are you sure you want to continue?",'cftp_admin'); ?>';
				if (confirm(msg_1+' '+checks.length+' '+msg_2)) {
					return true;
				} else {
					return false;
				}
			}
		});

	});
</script>

<div id="main">
	<h2><?php echo $page_title; ?></h2>

<?php

	/**
	 * Apply the corresponding action to the selected users.
	 */
	if(isset($_POST['groups_actions']) && $_POST['groups_actions'] != 'none') {
		/** Continue only if 1 or more users were selected. */
		if(!empty($_POST['groups'])) {
			$selected_groups = $_POST['groups'];
			switch($_POST['groups_actions']) {
				case 'delete':
					$deleted_groups = 0;

					foreach ($selected_groups as $groups) {
						$this_group = new GroupActions();
						$delete_group = $this_group->delete_group($groups);
						$deleted_groups++;
					}
					
					if ($deleted_groups > 0) {
						$msg = __('The selected groups were deleted.','cftp_admin');
						echo system_message('ok',$msg);
					}
				break;
			}
		}
		else {
			$msg = __('Please select at least one group.','cftp_admin');
			echo system_message('error',$msg);
		}
	}

	$database->MySQLDB();
	$cq = "SELECT * FROM tbl_groups";

	/** Add the search terms */	
	if(isset($_POST['search']) && !empty($_POST['search'])) {
		$search_terms = $_POST['search'];
		$cq .= " WHERE (name LIKE '%$search_terms%' OR description LIKE '%$search_terms%')";
		$no_results_error = 'search';
	}

	$cq .= " ORDER BY name ASC";
	
	$sql = $database->query($cq);
	$count = mysql_num_rows($sql);
?>

	<div class="form_actions_left">
		<div class="form_actions_limit_results">
			<form action="groups.php" name="groups_search" method="post" class="inline_form">
				<input type="text" name="search" id="search" value="<?php if(isset($_POST['search']) && !empty($_POST['search'])) { echo $_POST['search']; } ?>" class="txtfield form_actions_search_box" />
				<input type="submit" id="btn_proceed_search" value="<?php _e('Search','cftp_admin'); ?>" class="button_form" />
			</form>

			<form action="groups.php" name="groups_filters" method="post" class="inline_form">
				<select name="role" id="role" class="txtfield">
					<option value="all"><?php _e('All groups','cftp_admin'); ?></option>
					<option value="0"><?php _e('Empty groups','cftp_admin'); ?></option>
					<option value="1"><?php _e('Has at least 1 member','cftp_admin'); ?></option>
				</select>
				<input type="submit" id="btn_proceed_filter_clients" value="<?php _e('Filter','cftp_admin'); ?>" class="button_form" />
			</form>
		</div>
	</div>

	<form action="groups.php" name="groups_list" method="post">
		<div class="form_actions_right">
			<div class="form_actions">
				<div class="form_actions_submit">
					<label><?php _e('Selected groups actions','cftp_admin'); ?>:</label>
					<select name="groups_actions" id="groups_actions" class="txtfield">
						<option value="none"><?php _e('Select action','cftp_admin'); ?></option>
						<option value="delete"><?php _e('Delete','cftp_admin'); ?></option>
					</select>
					<input type="submit" id="do_action" name="proceed" value="<?php _e('Proceed','cftp_admin'); ?>" class="button_form" />
				</div>
			</div>
		</div>
		<div class="clear"></div>

		<div class="form_actions_count">
			<p><?php _e('Showing','cftp_admin'); ?>: <span><?php echo $count; ?> <?php _e('groups','cftp_admin'); ?></span></p>
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
					}
				}
				else {
					$no_results_message = __('There are no groups created yet.','cftp_admin');;
				}
				echo system_message('error',$no_results_message);
			}
		?>

		<table id="groups_tbl" class="tablesorter vertical_middle">
			<thead>
				<tr>
					<th class="td_checkbox">
						<input type="checkbox" name="select_all" id="select_all" value="0" />
					</th>
					<th><?php _e('Group name','cftp_admin'); ?></th>
					<th><?php _e('Description','cftp_admin'); ?></th>
					<th><?php _e('Members','cftp_admin'); ?></th>
					<th><?php _e('Created by','cftp_admin'); ?></th>
					<th><?php _e('Added on','cftp_admin'); ?></th>
					<th><?php _e('Actions','cftp_admin'); ?></th>
				</tr>
			</thead>
			<tbody>
			
			<?php
				while($row = mysql_fetch_array($sql)) {
				?>
				<tr>
					<td>
						<?php if ($row["id"] != '1') { ?>
							<input type="checkbox" name="groups[]" value="<?php echo $row["id"]; ?>" />
						<?php } ?>
					</td>
					<td><?php echo html_entity_decode($row["name"]); ?></td>
					<td><?php echo html_entity_decode($row["description"]); ?></td>
					<td>
						<?php
							$members_sql = $database->query("SELECT id FROM tbl_members WHERE group_id = '".$row["id"]."'");
							echo mysql_num_rows($members_sql);
						?>
					</td>
					<td><?php echo html_entity_decode($row["created_by"]); ?></td>
					<td><?php echo date(TIMEFORMAT_USE,$row['timestamp']); ?></td>
					<td>
						<a href="groups-edit.php?id=<?php echo $row["id"]; ?>" class="button button_small button_blue"><?php _e('Edit','cftp_admin'); ?></a>
					</td>
				</tr>
						
				<?php
				}
			
				$database->Close();
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

</div>

<?php include('footer.php'); ?>
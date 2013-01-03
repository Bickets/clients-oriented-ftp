<?php
/**
 * Show the list of current clients.
 *
 * @package		ProjectSend
 @ @subpackage	Clients
 *
 */
$tablesorter = 1;
$allowed_levels = array(9,8);
require_once('sys.includes.php');
$page_title = __('Clients Administration','cftp_admin');
include('header.php');
?>

<script type="text/javascript">
$(document).ready(function() {
	$("#clients_tbl").tablesorter( {
		sortList: [[1,0]], widgets: ['zebra'], headers: {
			0: { sorter: false },
			10: { sorter: false },
			11: { sorter: false }
		}
	})
	.tablesorterPager({container: $("#pager")})

	$("#select_all").click(function(){
		var status = $(this).attr("checked");
		$("td>input:checkbox").attr("checked",status);
	});
	
	$("form").submit(function() {
		var checks = $("td>input:checkbox").serializeArray(); 
		if (checks.length == 0) { 
			alert('<?php _e('Please select at least one client to proceed.','cftp_admin'); ?>');
			return false; 
		} 
		else {
			var msg_1 = '<?php _e("You are about to delete",'cftp_admin'); ?>';
			var msg_2 = '<?php _e("clients and all of the assigned files. Are you sure you want to continue?",'cftp_admin'); ?>';
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

	// Mass delete
	if(isset($_POST['btn_delete_clients'])) {
		if(!empty($_POST['delete'])) {
			$selected_clients = $_POST['delete'];
			foreach ($selected_clients as $client) {
				$this_client = new ClientActions();
				$delete_client = $this_client->delete_client($client);
			}
			
			$msg = __('The selected clients were deleted.','cftp_admin');
			echo system_message('ok',$msg);
		}
		else {
			$msg = __('Please select at least one client to delete.','cftp_admin');
			echo system_message('error',$msg);
		}
	}

	$database->MySQLDB();
	$cq = "SELECT * FROM tbl_clients";

/**
	// if the current user role is "account manager", only show the clients created by this user
	if (get_current_user_level() == '8') {
		$u = get_current_user_username();
		$cq .= " WHERE created_by = '$u'";
	}
*/
	$sql = $database->query($cq);
	$count = mysql_num_rows($sql);
	if (!$count) {
		/** Echo the no clients default message */
		message_no_clients();
	}
	else {
?>

		<form action="clients.php" name="clients_list" method="post">
			<div class="form_actions">
				<div class="form_actions_count">
					<p><?php _e('Clients','cftp_admin'); ?>: <span><?php echo $count; ?></span></p>
				</div>
				<div class="form_actions_submit">
					<label><?php _e('Selected clients actions','cftp_admin'); ?>:</label>
					<input type="submit" name="btn_delete_clients" id="btn_delete_clients" value="<?php _e('Delete','cftp_admin'); ?>" class="button_form" />
				</div>
			</div>

			<table id="clients_tbl" class="tablesorter vertical_middle">
				<thead>
					<tr>
						<th class="td_checkbox">
							<input type="checkbox" name="select_all" id="select_all" value="0" />
						</th>
						<th><?php _e('Full name','cftp_admin'); ?></th>
						<th><?php _e('Log in username','cftp_admin'); ?></th>
						<th><?php _e('Address','cftp_admin'); ?></th>
						<th><?php _e('Telephone','cftp_admin'); ?></th>
						<th><?php _e('E-mail','cftp_admin'); ?></th>
						<th><?php _e('Notify','cftp_admin'); ?></th>
						<th><?php _e('Internal contact','cftp_admin'); ?></th>
						<th><?php _e('Added on','cftp_admin'); ?></th>
						<th><?php _e('Files','cftp_admin'); ?></th>
						<th><?php _e('Actions','cftp_admin'); ?></th>
					</tr>
				</thead>
				<tbody>
				
				<?php
						while($row = mysql_fetch_array($sql)) {
						$client_user = $row["client_user"];
				?>
				
					<tr>
						<td><input type="checkbox" name="delete[]" value="<?php echo $row["id"]; ?>" /></td>
						<td><?php echo html_entity_decode($row["name"]); ?></td>
						<td><?php echo html_entity_decode($row["client_user"]); ?></td>
						<td><?php echo html_entity_decode($row["address"]); ?></td>
						<td><?php echo html_entity_decode($row["phone"]); ?></td>
						<td><?php echo html_entity_decode($row["email"]); ?></td>
						<td><?php if ($row["notify"] == '1') { _e('Yes','cftp_admin'); } else { _e('No','cftp_admin'); }?></td>
						<td><?php echo html_entity_decode($row["contact"]); ?></td>
						<td><?php echo date(TIMEFORMAT_USE,$row['timestamp']); ?></td>
						<td>
							<?php
								$sql_files = $database->query("SELECT * FROM tbl_files WHERE client_user='$client_user'");
								$count_files=mysql_num_rows($sql_files);
								echo $count_files;
							?>
						</td>
						<td>
							<a href="manage-files.php?id=<?php echo $row["id"]; ?>" class="button button_blue"><?php _e('Manage files','cftp_admin'); ?></a>
							<a href="upload/<?php echo $row["client_user"]; ?>/" class="button button_blue" target="_blank"><?php _e('View as client','cftp_admin'); ?></a>
							<a href="clients-edit.php?id=<?php echo $row["id"]; ?>" class="button button_small button_blue"><?php _e('Edit','cftp_admin'); ?></a>
						</td>
					</tr>
				
					<?php
						}
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

</div>

<?php include('footer.php'); ?>
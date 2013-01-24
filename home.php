<?php
/**
 * Home page for logged in system users.
 *
 * @package		ProjectSend
 *
 */
$allowed_levels = array(9,8,7);
require_once('sys.includes.php');
$page_title = __('Welcome to ProjectSend', 'cftp_admin');

$flot = 1;
include('header.php');
$database->MySQLDB();
?>

<div id="main">
	<h2><?php echo $page_title; ?></h2>

	<?php
	/**
	 * Icons are from the set: Origami Colored Pencil
	 *
	 * @link		http://www.iconfinder.com/search/?q=iconset%3Acolorful
	 * @author		Double-J designs
	 */
	?>
	<div class="home">
		<div class="container-fluid">
			<div class="row-fluid">
				<div class="span4">
					<ul class="home_spaces">
						<?php
							/** Show SIMPLE UPLOAD widget and current logo to clients */
							$upload_allowed = array(0);
							if (in_session_or_cookies($upload_allowed)) {
						?>
								<li class="logo_home_li">
									<div class="home_container">
										<img src="<?php echo BASE_URI; ?>includes/timthumb/timthumb.php?src=<?php echo BASE_URI; ?>img/custom/logo/<?php echo LOGO_FILENAME; ?>&amp;w=300" alt="<?php echo SYSTEM_NAME; ?> Logo" />
									</div>
								</li>

								<li class="home_widget_small">
									<div class="home_container">
										<h4><?php _e('Files','cftp_admin'); ?></h4>
										<img src="img/home-widget-files.png" alt="" />
										<a href="upload-from-computer.php" class="button button_blue button_big"><?php _e('Upload from computer','cftp_admin'); ?></a>
										<a href="<?php echo BASE_URI.'my_files/'; ?>" class="button button_blue button_big"><?php _e('Access my files list','cftp_admin'); ?></a>
										<div class="message message_info">
											<p><?php _e('Total files on account:','cftp_admin'); ?>
												<strong>
												<?php
													/** Count the VISIBLE files on this client's account */
													$my_username = get_current_user_username();
													$sql = $database->query("SELECT distinct id FROM tbl_files WHERE client_user = '$my_username' AND hidden = '0'");
													$total_files = mysql_num_rows($sql);
													echo $total_files;
												?>
												</strong>
											</p>
										</div>
									</div>
								</li>
						<?php
							}
							/** Show COMPLETE UPLOAD widget to allowed users */
							$upload_allowed = array(9,8,7);
							if (in_session_or_cookies($upload_allowed)) {
						?>
								<li class="home_widget_small">
									<div class="home_container">
										<h4><?php _e('Upload files','cftp_admin'); ?></h4>
										<img src="img/home-widget-files.png" alt="" />
										<a href="upload-from-computer.php" class="button button_blue button_big"><?php _e('Upload from computer','cftp_admin'); ?></a>
										<a href="upload-by-ftp.php" class="button button_blue button_big"><?php _e('Find orphan files','cftp_admin'); ?></a>
										<div class="message message_info">
											<p><?php _e('Total files:','cftp_admin'); ?>
												<strong>
												<?php
													$sql = $database->query("SELECT distinct id FROM tbl_files");
													$total_files = mysql_num_rows($sql);
													echo $total_files;
												?>
												</strong>
											</p>
										</div>
									</div>
								</li>
						<?php
							}
							/** Show CLIENTS widget to allowed users */
							$clients_allowed = array(9,8);
							if (in_session_or_cookies($clients_allowed)) {
						?>
								<li class="home_widget_small">
									<div class="home_container">
										<?php
											$sql_inactive = $database->query("SELECT distinct user FROM tbl_users WHERE active='0' AND level='0'");
											$count_inactive = mysql_num_rows($sql_inactive);
											if ($count_inactive > 0) {
										?>
												<div class="home_inactive_msg">
													<?php echo $count_inactive; ?>
												</div>
										<?php
											}
										?>
										<h4><?php _e('Clients','cftp_admin'); ?></h4>
										<img src="img/home-widget-clients.png" alt="" />
										<a href="clients-add.php" class="button button_blue button_big"><?php _e('Add new client','cftp_admin'); ?></a>
										<a href="clients.php" class="button button_blue button_big"><?php _e('Manage clients','cftp_admin'); ?></a>
										<div class="message message_info">
											<p><?php _e('Total clients:','cftp_admin'); ?>
												<strong>
												<?php
													$sql = $database->query("SELECT distinct user FROM tbl_users WHERE level='0'");
													$total_clients = mysql_num_rows($sql);
													echo $total_clients;
												?>
												</strong>
											</p>
										</div>
									</div>
								</li>
						<?php
							}
				
							/** Show GROUPS widget to allowed users */
							$groups_allowed = array(9,8);
							if (in_session_or_cookies($groups_allowed)) {
						?>
								<li class="home_widget_small">
									<div class="home_container">
										<h4><?php _e('Clients groups','cftp_admin'); ?></h4>
										<img src="img/home-widget-groups.png" alt="" />
										<a href="groups-add.php" class="button button_blue button_big"><?php _e('Add new group','cftp_admin'); ?></a>
										<a href="groups.php" class="button button_blue button_big"><?php _e('Manage groups','cftp_admin'); ?></a>
										<div class="message message_info">
											<p><?php _e('Total groups:','cftp_admin'); ?>
												<strong>
												<?php
													$sql = $database->query("SELECT distinct id FROM tbl_groups");
													$total_groups = mysql_num_rows($sql);
													echo $total_groups;
												?>
												</strong>
											</p>
										</div>
									</div>
								</li>
						<?php
							}
				
							/** Show USERS and BRANDING widgets to system administrators (Level 9) only */
							$users_allowed = array(9);
							if (in_session_or_cookies($users_allowed)) {
						?>
								<li class="home_widget_small">
									<div class="home_container">
										<h4><?php _e('System users','cftp_admin'); ?></h4>
										<img src="img/home-widget-users.png" alt="" />
										<a href="users-add.php" class="button button_blue button_big"><?php _e('Add new user','cftp_admin'); ?></a>
										<a href="users.php" class="button button_blue button_big"><?php _e('Manage users','cftp_admin'); ?></a>
										<div class="message message_info">
											<p><?php _e('Total users:','cftp_admin'); ?>
												<strong>
												<?php
													$sql = $database->query("SELECT distinct user FROM tbl_users WHERE level != '0'");
													$total_users = mysql_num_rows($sql);
													echo $total_users;
												?>
												</strong>
											</p>
										</div>
									</div>
								</li>
								<li class="home_widget_small">
									<div class="home_container">
										<h4><?php _e('Branding','cftp_admin'); ?></h4>
										<img src="img/home-widget-branding.png" alt="" />
										<a href="branding.php" class="button button_blue button_big"><?php _e('Change Logo','cftp_admin'); ?></a>
										<p><?php _e("Upload your company logo. It will appear in every client's page.", 'cftp_admin'); ?></p>
									</div>
								</li>
						<?php
							}
						?>
					</ul>
				</div>


				<div class="span4">	
					<div class="widget">
						<h4><?php _e('Recent activites','cftp_admin'); ?></h4>
						<div class="widget_int">
							<ul class="activities_log">
								<?php
									$sql_log = $database->query("SELECT * FROM tbl_actions_log ORDER BY id DESC LIMIT 10");
									$log_count = mysql_num_rows($sql_log);
									if ($log_count > 0) {
										while($log = mysql_fetch_array($sql_log)) {
										?>
											<li>
												<?php
													echo render_log_action(
																		array(
																			'action' => $log['action'],
																			'timestamp' => $log['timestamp'],
																			'owner_id' => $log['owner_id'],
																			'owner_user' => $log['owner_user'],
																			'affected_file' => $log['affected_file'],
																			'affected_file_name' => $log['affected_file_name'],
																			'affected_account' => $log['affected_account'],
																			'affected_account_name' => $log['affected_account_name']
																		)
													);
												?>
											</li>
										<?php
										}
									}
								?>
							</ul>
						</div>
					</div>
				</div>

				<div class="span4">	
					<div class="widget">
						<h4><?php _e('System information','cftp_admin'); ?></h4>
						<div class="widget_int">
							<div id="sys_info" style="height:300px;width:400px; "></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
</div>


<script type="text/javascript">
	$(document).ready(function(){
		$.plot(
			$("#sys_info"), [{
				data: [
					[1, <?php echo $total_files; ?>],
					[2, <?php echo $total_clients; ?>],
					[3, <?php echo $total_groups; ?>],
					[4, <?php echo $total_users; ?>]
				]
			}
			], {
				series:{
					bars:{show: true},
				},
				bars:{
					  barWidth:.5,
					  align: 'center'
				},
				legend: {
					show: true
				},
				grid:{
					hoverable: true,
					borderWidth: 0,
					backgroundColor: {
						colors: ["#fff", "#f9f9f9"]
					}
				},
				xaxis: {
					ticks: [
						[1, '<?php _e('Files','cftp_admin'); ?>'],
						[2, '<?php _e('Clients','cftp_admin'); ?>'],
						[3, '<?php _e('Groups','cftp_admin'); ?>'],
						[4, '<?php _e('Users','cftp_admin'); ?>']
					]
				}
			}
		);
	});
</script>

<?php
$database->Close();
include('footer.php');
?>
<?php
/**
 * Home page for logged in system users.
 *
 * @package		ProjectSend
 *
 */
$allowed_levels = array(9,8,7,0);
require_once('sys.includes.php');
$page_title = __('Welcome to ProjectSend', 'cftp_admin');
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
				<div class="span12">
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
													$count = mysql_num_rows($sql);
													echo $count;
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
										<a href="upload-by-ftp.php" class="button button_blue button_big"><?php _e('Import from FTP','cftp_admin'); ?></a>
										<div class="message message_info">
											<p><?php _e('Total files:','cftp_admin'); ?>
												<strong>
												<?php
													$sql = $database->query("SELECT distinct id FROM tbl_files");
													$count = mysql_num_rows($sql);
													echo $count;
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
													$count = mysql_num_rows($sql);
													echo $count;
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
													$count = mysql_num_rows($sql);
													echo $count;
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
			</div>
		</div>
	</div>
	
</div>
<?php
$database->Close();
include('footer.php');
?>
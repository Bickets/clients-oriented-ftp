<?php
/**
 * ProjectSend (previously cFTP) is a free, clients-oriented, private file
 * sharing web application.
 * Clients are created and assigned a username and a password. Then you can
 * upload as much files as you want under each account, and optionally add
 * a name and description to them. 
 *
 * ProjectSend is hosted on Google Code.
 * Feel free to participate!
 *
 * @link		http://code.google.com/p/clients-oriented-ftp/
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU GPL version 2
 * @package		ProjectSend
 *
 */
$allowed_levels = array(9,8,7,0);
require_once('sys.includes.php');

$page_title = __('Log in','cftp_admin');

include('header-unlogged.php');
	
	/** The form was submitted */
	if ($_POST) {
		$sysuser_username = mysql_real_escape_string($_POST['login_form_user']);
		$sysuser_password = mysql_real_escape_string(md5($_POST['login_form_pass']));
	
		/** Look up the system users table to see if the entered username exists */
		$sql_user = $database->query("SELECT * FROM tbl_users WHERE user='$sysuser_username'");
		$count_user = mysql_num_rows($sql_user);
		if ($count_user > 0){
			/** If the username was found on the users table */
			while($row = mysql_fetch_array($sql_user)) {
				$db_pass = $row['password'];
				$user_level = $row["level"];
				$active_status = $row['active'];
				$logged_id = $row['id'];
			}
			if ($db_pass == $sysuser_password) {
				if ($active_status != '0') {
					/** Set SESSION values */
					$_SESSION['loggedin'] = $sysuser_username;
					$_SESSION['userlevel'] = $user_level;

					if ($user_level != '0') {
						$access_string = 'admin';
						$_SESSION['access'] = $access_string;
					}
					else {
						$access_string = $sysuser_username;
						$_SESSION['access'] = $sysuser_username;
					}

					/** If "remember me" checkbox is on, set the cookie */
					if (!empty($_POST['login_form_remember'])) {
						setcookie("loggedin",$sysuser_username,time()+COOKIE_EXP_TIME);
						setcookie("password",$sysuser_password,time()+COOKIE_EXP_TIME);
						setcookie("access",$access_string,time()+COOKIE_EXP_TIME);
						setcookie("userlevel",$user_level,time()+COOKIE_EXP_TIME);
					}
					
					/** Record the action log */
					$new_log_action = new LogActions();
					$log_action_args = array(
											'action' => 1,
											'owner_id' => $logged_id
										);
					$new_record_action = $new_log_action->log_action_save($log_action_args);

					if ($user_level == '0') {
						header("location:".BASE_URI."my_files/");
					}
					else {
						header("location:home.php");
					}
					exit;
				}
				else {
					$errorstate = 'inactive_client';
				}
			}
			else {
				$errorstate = 'wrong_password';
			}
		}
		else {
			$errorstate = 'wrong_username';
		}
	
	}
	?>
		
		<div class="whiteform whitebox" id="loginform">
			<?php
				/**
				 * Show login errors
				 */
				if (isset($errorstate)) {
					switch ($errorstate) {
						case 'wrong_username':
							$login_err_message = __("The supplied username doesn't exist.",'cftp_admin');
							break;
						case 'wrong_password':
							$login_err_message = __("The supplied password is incorrect.",'cftp_admin');
							break;
						case 'inactive_client':
							$login_err_message = __("This account is not active. If you just registered, please wait until a system administrator approves your account.",'cftp_admin');
							break;
					}
	
					echo system_message('error',$login_err_message,'login_error');
				}
			?>
		
			<script type="text/javascript">
				$(document).ready(function() {
					$("form").submit(function() {
						clean_form(this);
		
						is_complete(this.login_form_user,'<?php _e('Username was not completed','cftp_admin'); ?>');
						is_complete(this.login_form_pass,'<?php _e('Password was not completed','cftp_admin'); ?>');
		
						// show the errors or continue if everything is ok
						if (show_form_errors() == false) { return false; }
					});
				});
			</script>
		
			<form action="index.php" method="post" name="login_admin">
				<input type="hidden" name="sent_admin" id="sent_admin">
				<ul class="form_fields">
					<li>
						<label for="login_form_user"><?php _e('Username','cftp_admin'); ?></label>
						<input type="text" name="login_form_user" id="login_form_user" value="<?php if (isset($sysuser_username)) { echo $sysuser_username; } ?>" class="field" />
					</li>
					<li>
						<label for="login_form_pass"><?php _e('Password','cftp_admin'); ?></label>
						<input type="password" name="login_form_pass" id="login_form_pass" class="field" />
					</li>
					<li>
						<label for="login_form_remember"><?php _e('Remember me','cftp_admin'); ?></label>
						<input type="checkbox" name="login_form_remember" id="login_form_remember" value="on" />
					</li>
					<li class="form_submit_li">
						<input type="submit" name="Submit" value="<?php _e('Continue to log in','cftp_admin'); ?>" id="button_login" class="button button_blue button_submit" />
					</li>
				</ul>
			</form>

			<div class="login_form_links">
				<?php
					if (CLIENTS_CAN_REGISTER == '1') {
				?>
						<p><?php _e("Don't have an account yet?",'cftp_admin'); ?> <a href="<?php echo BASE_URI; ?>register.php"><?php _e('Register as a new client.','cftp_admin'); ?></a></p>
				<?php
					} else {
				?>
						<p><?php _e("This server does not allow self registrations.",'cftp_admin'); ?></p>
						<p><?php _e("If you need an account, please contact a server administrator.",'cftp_admin'); ?></p>
				<?php
					}
				?>
			</div>
	
		</div>
	
	</div> <!-- main -->

	<?php default_footer_info(); ?>

</body>
</html>
<?php
	$database->Close();
	ob_end_flush();
?>
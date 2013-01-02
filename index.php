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
session_start();
ob_start();
/**
 * Define the level required to access the following function, where
 * all system users are included, and clients (level 0) are not.
 */
$allowed_enter = array(9,8,7);
require_once('sys.includes.php');

/**
 * Check if the ProjectSend is installed. Done only on the log in form
 * page since all other are inaccessible if no valid session or cookie
 * is set.
 */
if (!is_projectsend_installed()) {
	header("Location:install/index.php");
	exit;
}

/** If logged as a system user, go directly to the back-end homepage */
if (in_session_or_cookies($allowed_enter)) {
	header("Location:".BASE_URI."home.php");
}

/** If client is logged in, redirect to the files list. */
check_for_client();

$database->MySQLDB();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php _e('Log in','cftp_admin'); ?> | <?php echo SYSTEM_NAME; ?></title>
	<link rel="shortcut icon" href="<?php echo BASE_URI; ?>favicon.ico" />
	<link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_URI; ?>styles/shared.css" />
	<link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_URI; ?>styles/base.css" />
	<link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_URI; ?>styles/font-sansation.css" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js" type="text/javascript" ></script>
	<script src="<?php echo BASE_URI; ?>includes/js/jquery.validations.js" type="text/javascript"></script>
</head>

<body>

	<div id="header">
		<div id="lonely_logo">
			<h1><?php echo THIS_INSTALL_SET_TITLE; ?></h1>
			<p><?php _e('Provided by', 'cftp_admin'); ?> <?php echo SYSTEM_NAME; ?></p>
		</div>
	</div>
	<div id="login_header_low">
	</div>

	<div id="main">
	
	<?php
	/** The form was submitted */
	if ($_POST) {
		$sysuser_username = mysql_real_escape_string($_POST['login_form_user']);
		$sysuser_password = mysql_real_escape_string(md5($_POST['login_form_pass']));
	
		/** Look up the system users table to see if the entered username exists */
		$sql_user = $database->query("SELECT * FROM tbl_users WHERE user='$sysuser_username'");
		$count_user = mysql_num_rows($sql_user);
		if ($count_user > 0){
			$try_login = 'user';
		} else {
		/** If not, search for the same username on the clients table */
			$sql_client = $database->query("SELECT * FROM tbl_clients WHERE client_user='$sysuser_username'");
			$count_client = mysql_num_rows($sql_client);
			if ($count_client > 0){
				$try_login = 'client';
			}
		}
		/** If the username was found on any table, try to log in */
		if(!empty($try_login)) {
			switch($try_login) {
				/** If the username was found on the system users table */
				case 'user':
					while($row = mysql_fetch_array($sql_user)) {
						$db_pass = $row['password'];
						$user_level = $row["level"];
					}
					if ($db_pass == $sysuser_password) {
						$_SESSION['loggedin'] = $sysuser_username;
						$_SESSION['access'] = 'admin';
						$_SESSION['userlevel'] = $user_level;
						/** If "remember me" checkbox is on, set the cookie */
						if ($_POST['login_form_remember']=='on') {
							setcookie("loggedin",$sysuser_username,time()+COOKIE_EXP_TIME);
							setcookie("password",$sysuser_password,time()+COOKIE_EXP_TIME);
							setcookie("access","admin",time()+COOKIE_EXP_TIME);
							setcookie("userlevel",$user_level,time()+COOKIE_EXP_TIME);
						}
						header("location:home.php");
						exit;
					}
					else {
						$errorstate = 'wrong_password';
					}
				break;

				/** If the username was found on the clients table */
				case 'client';
					while($row = mysql_fetch_array($sql_client)) {
						$db_pass = $row['password'];
					}
					if ($db_pass == $sysuser_password) {
						$_SESSION['loggedin'] = $sysuser_username;
						$_SESSION['access'] = $sysuser_username;
						$_SESSION['userlevel'] = '0';
						/** If "remember me" checkbox is on, set the cookie */
						if ($_POST['login_form_remember']=='on') {
							setcookie("loggedin",$sysuser_username,time()+COOKIE_EXP_TIME);
							setcookie("password",$sysuser_password,time()+COOKIE_EXP_TIME);
							setcookie("access",$sysuser_username,time()+COOKIE_EXP_TIME);
							setcookie("userlevel","0",time()+COOKIE_EXP_TIME);
						}
						/** Send the client directly to the files list */
						header("location:".BASE_URI."upload/$sysuser_username/");
						exit;
					}
					else {
						$errorstate = 'wrong_password';
					}
				break;
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
						<input type="checkbox" name="login_form_remember" id="login_form_remember" />
					</li>
					<li class="form_submit_li">
						<input type="submit" name="Submit" value="<?php _e('Continue to log in','cftp_admin'); ?>" id="button_login" class="button button_blue button_submit" />
					</li>
				</ul>
			</form>
	
		</div>
	
	</div> <!-- main -->

	<?php default_footer_info(); ?>

</body>
</html>
<?php
	$database->Close();
	ob_end_flush();
?>
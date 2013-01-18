<?php
/**
 * Contains the form and the processes used to install ProjectSend.
 *
 * @package		ProjectSend
 * @subpackage	Install
 */
ob_start();
session_start();
header("Cache-control: private");

require_once('../sys.includes.php');

$database->MySQLDB();

/**
 * Function that takes an array of SQL queries and executes them in order.
 */
function try_query($query)
{
	if (empty($error_str)) {
		global $error_str;
	}
	foreach ($query as $i => $value) {
		$result = mysql_query($query[$i]);
		if (mysql_error()) {
			$error_str .= mysql_error().'<br />';
		}
	}
	return $result;
}

/** Collect data from form */
if($_POST) {
	$this_install_title = mysql_real_escape_string($_POST['this_install_title']);
	$base_uri = mysql_real_escape_string($_POST['base_uri']);
	$got_admin_name = mysql_real_escape_string($_POST['install_user_fullname']);
	$got_admin_username = mysql_real_escape_string($_POST['install_user_username']);
	$got_admin_email = mysql_real_escape_string($_POST['install_user_mail']);
	$got_admin_pass = mysql_real_escape_string(md5($_POST['install_user_pass']));
	$got_admin_pass2 = mysql_real_escape_string(md5($_POST['install_user_repeat']));
}

/** Define the installation text stirngs */
$page_title_install = __('Install','cftp_admin');
$install_no_sitename = __('Sitename was not completed.','cftp_admin');
$install_no_baseuri = __('ProjectSend URI was not completed.','cftp_admin');

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $page_title_install; ?> | <?php echo SYSTEM_NAME; ?></title>
	<link rel="shortcut icon" href="../favicon.ico" />
	<link rel="stylesheet" media="all" type="text/css" href="../styles/shared.css" />
	<link rel="stylesheet" media="all" type="text/css" href="../styles/base.css" />

	<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
	<script src="../includes/js/jquery.validations.js" type="text/javascript"></script>

	<link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_URI; ?>styles/bootstrap.min.css" />
	<script type="text/javascript" src="<?php echo BASE_URI; ?>includes/js/bootstrap/modernizr-2.6.2-respond-1.1.0.min.js"></script>

	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,300' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Abel' rel='stylesheet' type='text/css'>
</head>

<body>

	<div id="header">
		<div id="lonely_logo">
			<h1><?php echo SYSTEM_NAME.' '; _e('setup','cftp_admin'); ?></h1>
		</div>
	</div>
	<div id="login_header_low">
	</div>
	
	<div id="main">
	
		<div class="options_box whitebox" id="install_form">

			<?php
			if (is_projectsend_installed()) {
			?>
				<h3><?php _e('Already installed','cftp_admin'); ?></h3>
				<p><?php _e('It seems that ProjectSend is already installed here.','cftp_admin'); ?></p>
				<p><?php _e('If you want to reinstall, please delete the system tables from the database and come back to the installation form.','cftp_admin'); ?></p>
			<?php
			}
			else {

				if ($_POST) {
					
					/**
					 * The URI must end with a /, so add it if it wasn't posted.
					 */
					if ($base_uri{(strlen($base_uri) - 1)} != '/') { $base_uri .= '/'; }
					/** Begin form validation */
					$valid_me->validate('completed',$this_install_title,$install_no_sitename);
					$valid_me->validate('completed',$base_uri,$install_no_baseuri);
					$valid_me->validate('completed',$got_admin_name,$validation_no_name);
					$valid_me->validate('completed',$got_admin_email,$validation_no_email);
					/** Username validation */
					$valid_me->validate('completed',$got_admin_username,$validation_no_user);
					$valid_me->validate('length',$got_admin_username,$validation_length_user,MIN_USER_CHARS,MAX_USER_CHARS);
					$valid_me->validate('alpha',$got_admin_username,$validation_alpha_user);
					/** Password fields validation */
					$valid_me->validate('completed',$_POST['install_user_pass'],$validation_no_pass);
					$valid_me->validate('completed',$_POST['install_user_repeat'],$validation_no_pass2);
					$valid_me->validate('email',$got_admin_email,$validation_invalid_mail);
					$valid_me->validate('length',$_POST['install_user_pass'],$validation_length_pass,MIN_USER_CHARS,MAX_USER_CHARS);
					$valid_me->validate('password',$_POST['install_user_pass'],$validation_alpha_pass);
					$valid_me->validate('pass_match','',$validation_match_pass,'','',$_POST['install_user_pass'],$_POST['install_user_repeat']);
				
					if ($valid_me->return_val) {
						/**
						 * Call the file that creates the tables and fill it with the data we got previously
						 */
						include_once(ROOT_DIR.'/install/database.php');
						/**
						 * Try to execute each query individually
						 */
						try_query(array($q1,$q2,$q3,$q4,$q5,$q6,$q7));
						/**
						 * Continue based on the value returned from the above function
						 */
						if (!empty($error_str)) {
							$query_state = 'err';
						}
						else {
							$query_state = 'ok';
						}
					}
				
				}
				?>
			
				<?php
					if(isset($valid_me)) {
						/** If the form was submited with errors, show them here */
						$valid_me->list_errors();
					}
				
					if (isset($query_state)) {
						switch ($query_state) {
							case 'ok':
								/** Chmod the upload folder to 755 to avoid errors later */
								$uploads_folder = ROOT_DIR.'/upload';
								chmod($uploads_folder, 0755);
	
								$msg = __('Congratulations! Everything is up and running.','cftp_admin');
								echo system_message('ok',$msg);
								?>
									<p><?php _e('You may proceed to','cftp_admin'); ?> <a href="<?php echo $base_uri; ?>" target="_self"><?php _e('log in','cftp_admin'); ?></a> <?php _e('with your newely created username and password.','cftp_admin'); ?></p>
								<?php
								break;
							case 'err':
								$msg = __('There seems to be an error. Please try again.','cftp_admin');
								$msg .= '<p>';
								$msg .= $error_str;
								$msg .= '</p>';
								echo system_message('error',$msg);
								break;
						}
					}
				
					else {
					?>
				
						<script type="text/javascript">
							$(document).ready(function() {
								$("form").submit(function() {
									clean_form(this);
				
									is_complete(this.this_install_title,'<?php echo $install_no_sitename; ?>');
									is_complete(this.base_uri,'<?php echo $install_no_baseuri; ?>');
									is_complete(this.install_user_fullname,'<?php echo $validation_no_name; ?>');
									is_complete(this.install_user_mail,'<?php echo $validation_no_email; ?>');
									// username
									is_complete(this.install_user_username,'<?php echo $validation_no_user; ?>');
									is_length(this.install_user_username,<?php echo MIN_USER_CHARS; ?>,<?php echo MAX_USER_CHARS; ?>,'<?php echo $validation_length_user; ?>');
									is_alpha(this.install_user_username,'<?php echo $validation_alpha_user; ?>');
									// password fields
									is_complete(this.install_user_pass,'<?php echo $validation_no_pass; ?>');
									is_complete(this.install_user_repeat,'<?php echo $validation_no_pass2; ?>');
									is_email(this.install_user_mail,'<?php echo $validation_invalid_mail; ?>');
									is_length(this.install_user_pass,<?php echo MIN_USER_CHARS; ?>,<?php echo MAX_USER_CHARS; ?>,'<?php echo $validation_length_pass; ?>');
									is_password(this.install_user_pass,'<?php $chars = addslashes($validation_valid_chars); echo $validation_valid_pass." ".$chars; ?>');
									is_match(this.install_user_pass,this.install_user_repeat,'<?php echo $validation_match_pass; ?>');
				
									// show the errors or continue if everything is ok
									if (show_form_errors() == false) { return false; }
								});
							});
						</script>
					
						<form action="index.php" name="installform" method="post">
				
							<ul class="form_fields">
								<li>
									<h3><?php _e('Basic system options','cftp_admin'); ?></h3>
									<p><?php _e("You need to provide this data for a correct system installation. The site name will be visible along the system panel, and the client's lists.<br />Don't forget to edit <em>/includes/sys.config.php</em> with your database settings before installing. If the file doesn't exist, you can create it by renanming the dummy file sys.config.sample.php.",'cftp_admin'); ?></p>
								</li>
								<li>
									<label for="this_install_title"><?php _e('Site name','cftp_admin'); ?></label>
									<input name="this_install_title" id="this_install_title" class="required" value="<?php echo (isset($this_install_title) ? $this_install_title : ''); ?>" />
								</li>
								<li>
									<label for="base_uri"><?php _e('ProjectSend URI (address)','cftp_admin'); ?></label>
									<input name="base_uri" id="base_uri" class="required" value="<?php echo (isset($base_uri) ? $base_uri : get_current_url()); ?>" />
								</li>
				
								<li class="options_divide"></li>
				
								<li>
									<h3><?php _e('Default system administrator options','cftp_admin'); ?></h3>
									<p><?php _e("This info will be used to create a default system user, which can't be deleted afterwards. Password should be between <strong>6 and 12 characters long</strong>.",'cftp_admin'); ?></p>
								</li>
								<li>
									<label for="install_user_fullname"><?php _e('Full name','cftp_admin'); ?></label>
									<input name="install_user_fullname" id="install_user_fullname" class="required" value="<?php echo (isset($got_admin_name) ? $got_admin_name : ''); ?>" />
								</li>
								<li>
									<label for="install_user_mail"><?php _e('E-mail address','cftp_admin'); ?></label>
									<input name="install_user_mail" id="install_user_mail" class="required" value="<?php echo (isset($got_admin_email) ? $got_admin_email : ''); ?>" />
								</li>
								<li>
									<label for="install_user_username"><?php _e('Log in username','cftp_admin'); ?></label>
									<input name="install_user_username" id="install_user_username" class="required" maxlength="<?php echo MAX_USER_CHARS; ?>" value="<?php echo (isset($got_admin_username) ? $got_admin_username : ''); ?>" />
								</li>
								<li>
									<label for="install_user_pass"><?php _e('Password','cftp_admin'); ?></label>
									<input type="password" name="install_user_pass" id="install_user_pass" class="required" maxlength="12" />
								</li>
								<li>
									<label for="install_user_repeat"><?php _e('Repeat','cftp_admin'); ?></label>
									<input type="password" name="install_user_repeat" id="install_user_repeat" class="required" maxlength="12" />
								</li>
								<li class="form_submit_li">
									<input type="submit" name="Submit" value="<?php _e('Install','cftp_admin'); ?>" class="button button_blue button_submit" />
								</li>
							</ul>
				
							<div id="install_extra">
								<p><?php _e('After installing the system, you can go to the options page to set your timezone, prefered date display format and thubmnails parameters, besides being able to change the site options provided here.','cftp_admin'); ?></p>
							</div>
				
						</form>
			
			<?php
				}
			}
			?>

		</div>

	</div> <!--main-->

	<?php default_footer_info(); ?>

</body>
</html>
<?php
	$database->Close();
	ob_end_flush();
?>
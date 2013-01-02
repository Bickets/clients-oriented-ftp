<?php
/**
 * This file generates the header for the back-end and also for the default
 * template.
 *
 * Other checks for user level are performed later to generate the different
 * menu items, and the content of the page that called this file.
 *
 * @package ProjectSend
 * @see check_for_session
 * @see check_for_admin
 * @see can_see_content
 */
/** $is_template is defined on /templates/common.php */
if (!isset($is_template)) {
	session_start();
	ob_start();
	header("Cache-control: private");
}

/** Check for an active session or cookie */
check_for_session();

/** Check if the active account belongs to a system user or a client. */
//check_for_admin();

/** If no page title is defined, revert to a default one */
if (!isset($page_title)) { $page_title = __('System Administration','cftp_admin'); }

/**
 * Call the database update file to see if any change is needed,
 * but only if logged in as a system user.
 */
$core_update_allowed = array(9,8,7);
if (in_session_or_cookies($core_update_allowed)) {
	require_once(ROOT_DIR.'/includes/core.update.php');
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo THIS_INSTALL_SET_TITLE; ?> &raquo; <?php echo $page_title; ?> | <?php echo SYSTEM_NAME; ?></title>
	<link rel="shortcut icon" href="<?php echo BASE_URI; ?>/favicon.ico" />
	<link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_URI; ?>styles/shared.css" />
	<?php
		/**
		 * Load a different css file when called from the admin, or
		 * the default template.
		 */
		if (!isset($this_template_css)) {
			/** Back-end */
	?>
			<link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_URI; ?>styles/base.css" />
	<?php
		}
		else {
			/** Template */
	?>
			<link rel="stylesheet" media="all" type="text/css" href="<?php echo $this_template_css; ?>" />
	<?php
		}
	?>
	<link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_URI; ?>styles/font-sansation.css" />
	
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
	<script src="<?php echo BASE_URI; ?>includes/js/superfish.js" type="text/javascript"></script>
	<script src="<?php echo BASE_URI; ?>includes/js/jquery.validations.js" type="text/javascript"></script>
	<script src="<?php echo BASE_URI; ?>includes/js/jquery.easytabs.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE_URI; ?>includes/js/jquery.hashchange.min.js" type="text/javascript"></script>

	<?php if (isset($tablesorter)) { ?>
		<script src="<?php echo BASE_URI; ?>includes/js/jquery.tablesorter.min.js" type="text/javascript"></script>
		<script src="<?php echo BASE_URI; ?>includes/js/jquery.tablesorter.pager.js" type="text/javascript"></script>
	<?php } ?>

	<?php if (isset($textboxlist)) { ?>
		<script src="<?php echo BASE_URI; ?>includes/js/GrowingInput.js" type="text/javascript"></script>
		<script src="<?php echo BASE_URI; ?>includes/js/TextboxList.js" type="text/javascript"></script>
	<?php } ?>

	<?php if (isset($plupload)) { ?>
		<link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_URI; ?>includes/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css" />
		<script type="text/javascript" src="<?php echo BASE_URI; ?>includes/js/browserplus-min.js"></script>
		<script type="text/javascript" src="<?php echo BASE_URI; ?>includes/plupload/js/plupload.full.js"></script>
		<script type="text/javascript" src="<?php echo BASE_URI; ?>includes/plupload/js/jquery.plupload.queue/jquery.plupload.queue.js"></script>
	<?php } ?>

</head>

<body>

	<div id="header">
		<div id="header_info">
			<h1><?php echo THIS_INSTALL_SET_TITLE; ?></h1>
			<p><?php _e('Provided by', 'cftp_admin'); ?> <?php echo SYSTEM_NAME; ?> <?php _e('version', 'cftp_admin'); ?> <?php echo CURRENT_VERSION; ?></p>
		</div>
		<a href="<?php echo BASE_URI; ?>process.php?do=logout" target="_self" id="logout" class="button button_blue"><?php _e('Logout', 'cftp_admin'); ?></a>
	</div>

	<?php
		/**
		 * If any update was made to the database structure, show the message
		 */
		if(isset($updates_made) && $updates_made > 0) {
	?>
			<div id="system_msg">
				<p><strong><?php _e('System Notice:', 'cftp_admin');?></strong> <?php _e('The database was updated to support this version of the software.', 'cftp_admin'); ?></p>
			</div>
	<?php
		}
	?>

	<script type="text/javascript">
		$(document).ready(function() {
			$("ul.sf-menu").superfish();
		});
	</script>

	<div id="top_menu">
		<ul class="sf-menu">
			<li><a href="<?php echo BASE_URI; ?>home.php"><?php _e('Home', 'cftp_admin'); ?></a></li>
			<li>
				<a href="<?php echo BASE_URI; ?>upload-from-computer.php"><?php _e('Upload files', 'cftp_admin'); ?></a>
					<?php
						/**
						 * Hide the subitems from clients, since their upload form
						 * link is the same that was defined on the above item.
						 */
						$clients_allowed = array(9,8,7);
						if (in_session_or_cookies($clients_allowed)) {
					?>
						<ul>
							<li><a href="<?php echo BASE_URI; ?>upload-from-computer.php"><?php _e('Upload from computer', 'cftp_admin'); ?></a></li>
							<li><a href="<?php echo BASE_URI; ?>upload-by-ftp.php"><?php _e('Import from FTP', 'cftp_admin'); ?></a></li>
						</ul>
				<?php } ?>
			</li>
	
			<?php
				/**
				 * Show the CLIENTS menu only to
				 * System administrators and Account managers
				 */
				$clients_allowed = array(9,8);
				if (in_session_or_cookies($clients_allowed)) {
			?>
					<li>
						<a href="<?php echo BASE_URI; ?>clients.php"><?php _e('Clients', 'cftp_admin'); ?></a>
						<ul>
							<li><a href="<?php echo BASE_URI; ?>clients-add.php"><?php _e('Add new', 'cftp_admin'); ?></a></li>
							<li><a href="<?php echo BASE_URI; ?>clients.php"><?php _e('Manage clients', 'cftp_admin'); ?></a></li>
						</ul>
					</li>
			<?php } ?>
	
			<?php
				/**
				 * Show the USERS menu only to
				 * System administrators
				 */
				$users_allowed = array(9);
				if (in_session_or_cookies($users_allowed)) {
			?>
					<li>
						<a href="<?php echo BASE_URI; ?>users.php"><?php _e('Users', 'cftp_admin'); ?></a>
						<ul>
							<li><a href="<?php echo BASE_URI; ?>users-add.php"><?php _e('Add new', 'cftp_admin'); ?></a></li>
							<li><a href="<?php echo BASE_URI; ?>users.php"><?php _e('Manage users', 'cftp_admin'); ?></a></li>
						</ul>
					</li>
			<?php } ?>
	
			<?php
				/**
				 * Show the OPTIONS menu only to
				 * System administrators
				 */
				$options_allowed = array(9);
				if (in_session_or_cookies($options_allowed)) {
			?>
					<li>
						<a href="<?php echo BASE_URI; ?>options.php"><?php _e('Options', 'cftp_admin'); ?></a>
						<ul>
							<li><a href="<?php echo BASE_URI; ?>options.php"><?php _e('General options', 'cftp_admin'); ?></a></li>
							<li><a href="<?php echo BASE_URI; ?>branding.php"><?php _e('Branding', 'cftp_admin'); ?></a></li>
						</ul>
					</li>
			<?php } ?>

			<?php
				/**
				 * Show the MY FILES menu only to clients.
				 */
				$clients_allowed = array(0);
				if (in_session_or_cookies($clients_allowed)) {
					$my_username = get_current_user_username();
					/** Define "MY FILES LIST" link to use here and on the home widget */
					$my_files_link = BASE_URI.'upload/'.$my_username.'/';
			?>
					<li><a href="<?php echo $my_files_link; ?>"><?php _e('View my files', 'cftp_admin'); ?></a></li>
			<?php
				}
			?>
	
		</ul>
		<div class="clear"></div>
	</div>

<?php
	/**
	 * Check if the current user has permission to view this page.
	 * If not, an error message is generated instead of the actual content.
	 * The allowed levels are defined on each individual page before the
	 * inclusion of this file.
	 */
	can_see_content($allowed_levels);
?>
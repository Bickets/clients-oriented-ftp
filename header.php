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
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title><?php echo $page_title; ?> &raquo; <?php echo THIS_INSTALL_SET_TITLE; ?></title>
	<link rel="shortcut icon" href="<?php echo BASE_URI; ?>/favicon.ico" />
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>

	<link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_URI; ?>css/bootstrap.min.css" />
	<link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_URI; ?>css/bootstrap-responsive.min.css" />
	<script type="text/javascript" src="<?php echo BASE_URI; ?>includes/js/bootstrap/bootstrap.min.js"></script>
	<script type="text/javascript" src="<?php echo BASE_URI; ?>includes/js/bootstrap/modernizr-2.6.2-respond-1.1.0.min.js"></script>
	
	<link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_URI; ?>css/shared.css" />

	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,300' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Abel' rel='stylesheet' type='text/css'>

	<?php
		/**
		 * Load a different css file when called from the admin, or
		 * the default template.
		 */
		if (!isset($this_template_css)) {
			/** Back-end */
	?>
			<link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_URI; ?>css/base.css" />
	<?php
		}
		else {
			/** Template */
	?>
			<link rel="stylesheet" media="all" type="text/css" href="<?php echo $this_template_css; ?>" />
	<?php
		}
	?>
	
	<script src="<?php echo BASE_URI; ?>includes/js/jquery.validations.js" type="text/javascript"></script>
	<script src="<?php echo BASE_URI; ?>includes/js/jquery.psendmodal.js" type="text/javascript"></script>

	<?php if (isset($easytabs)) { ?>
		<script src="<?php echo BASE_URI; ?>includes/js/jquery.easytabs.min.js" type="text/javascript"></script>
		<script src="<?php echo BASE_URI; ?>includes/js/jquery.hashchange.min.js" type="text/javascript"></script>
	<?php } ?>

	<?php if (isset($tablesorter)) { ?>
		<script src="<?php echo BASE_URI; ?>includes/js/jquery.tablesorter.min.js" type="text/javascript"></script>
		<script src="<?php echo BASE_URI; ?>includes/js/jquery.tablesorter.pager.js" type="text/javascript"></script>
	<?php } ?>

	<?php if (isset($textboxlist)) { ?>
		<script src="<?php echo BASE_URI; ?>includes/js/GrowingInput.js" type="text/javascript"></script>
		<script src="<?php echo BASE_URI; ?>includes/js/TextboxList.js" type="text/javascript"></script>
	<?php } ?>
	
	<?php if (isset($multiselect)) { ?>
		<link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_URI; ?>css/multi-select.css" />
		<script type="text/javascript" src="<?php echo BASE_URI; ?>includes/js/jquery.multi-select.js"></script>
	<?php } ?>
	

	<?php if (isset($plupload)) { ?>
		<link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_URI; ?>includes/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css" />
		<script type="text/javascript" src="<?php echo BASE_URI; ?>includes/js/browserplus-min.js"></script>
		<script type="text/javascript" src="<?php echo BASE_URI; ?>includes/plupload/js/plupload.full.js"></script>
		<script type="text/javascript" src="<?php echo BASE_URI; ?>includes/plupload/js/jquery.plupload.queue/jquery.plupload.queue.js"></script>
	<?php } ?>

	<?php if (isset($flot)) { ?>
		<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="<?php echo BASE_URI; ?>includes/flot/excanvas.js"></script><![endif]-->
		<script language="javascript" type="text/javascript" src="<?php echo BASE_URI; ?>includes/flot/jquery.flot.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo BASE_URI; ?>includes/flot/jquery.flot.resize.js"></script>
	<?php } ?>
</head>

<body>

	<header>
		<div id="header">
			<div class="container-fluid">
				<div class="row-fluid">
					<div class="span6">
						<h1><?php echo THIS_INSTALL_SET_TITLE; ?></h1>
					</div>
					<div class="span6">
						<div id="account">
							<span><?php _e('Welcome', 'cftp_admin'); ?>, <?php echo $global_name; ?></span>
							<?php
								if (CURRENT_USER_LEVEL == 0) {
									$my_account_link = 'clients-edit.php';
								}
								else {
									$my_account_link = 'users-edit.php';
								}
								$my_account_link .= '?id='.CURRENT_USER_ID;
							?>
							<a href="<?php echo BASE_URI.$my_account_link; ?>" class="my_account"><?php _e('My Account', 'cftp_admin'); ?></a>
							<a href="<?php echo BASE_URI; ?>process.php?do=logout" ><?php _e('Logout', 'cftp_admin'); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	
		<script type="text/javascript">
			$(document).ready(function() {
				$('.button').click(function() {
					$(this).blur();
				});

				$('#top_menu .icon').click(function() {
					if ($('#top_menu').hasClass('nav_active')) {
						$('#top_menu').removeClass('nav_active');
					}
					else {
						$('#top_menu').addClass('nav_active');
					}
				});
			});
		</script>
	
		<nav id="top_menu" class="nav">
			<div class="icon">
				<div class="nav_line"></div>
				<div class="nav_line"></div>
				<div class="nav_line"></div>
			</div>
			<ul class="main_menu">
				<?php
					/**
					 * Show the HOME menu item only to
					 * system users.
					 */
					$groups_allowed = array(9,8,7);
					if (in_session_or_cookies($groups_allowed)) {
				?>
						<li class="no_arrow"><a href="<?php echo BASE_URI; ?>home.php"><?php _e('Dashboard', 'cftp_admin'); ?></a></li>
						<li>
							<a href="<?php echo BASE_URI; ?>upload-from-computer.php"><?php _e('Files', 'cftp_admin'); ?></a>
								<ul>
									<li><a href="<?php echo BASE_URI; ?>upload-from-computer.php"><?php _e('Upload from device', 'cftp_admin'); ?></a></li>
									<li><a href="<?php echo BASE_URI; ?>manage-files.php"><?php _e('Manage files', 'cftp_admin'); ?></a></li>
									<li><a href="<?php echo BASE_URI; ?>upload-import-orphans.php"><?php _e('Find orphan files', 'cftp_admin'); ?></a></li>
								</ul>
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
							<a href="<?php echo BASE_URI; ?>clients.php">
								<?php _e('Clients', 'cftp_admin'); ?>
								<?php
									$sql_inactive = $database->query("SELECT DISTINCT user FROM tbl_users WHERE active = '0' AND level = '0'");
									$count_inactive = mysql_num_rows($sql_inactive);
									if ($count_inactive > 0) {
								?>
										<span class="mnu_inactive_msg">
											<?php echo $count_inactive; ?>
										</span>
								<?php
									}
								?>
							</a>
							<ul>
								<li><a href="<?php echo BASE_URI; ?>clients-add.php"><?php _e('Add new', 'cftp_admin'); ?></a></li>
								<li><a href="<?php echo BASE_URI; ?>clients.php"><?php _e('Manage clients', 'cftp_admin'); ?></a></li>
							</ul>
						</li>
				<?php } ?>
	
				<?php
					/**
					 * Show the GROUPS menu only to
					 * System administrators and Account managers
					 */
					$groups_allowed = array(9,8);
					if (in_session_or_cookies($groups_allowed)) {
				?>
						<li>
							<a href="<?php echo BASE_URI; ?>groups.php"><?php _e('Clients groups', 'cftp_admin'); ?></a>
							<ul>
								<li><a href="<?php echo BASE_URI; ?>groups-add.php"><?php _e('Add new', 'cftp_admin'); ?></a></li>
								<li><a href="<?php echo BASE_URI; ?>groups.php"><?php _e('Manage groups', 'cftp_admin'); ?></a></li>
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
							<a href="<?php echo BASE_URI; ?>users.php">
								<?php _e('System Users', 'cftp_admin'); ?>
								<?php
									$sql_inactive = $database->query("SELECT DISTINCT user FROM tbl_users WHERE active = '0' AND level != '0'");
									$count_inactive = mysql_num_rows($sql_inactive);
									if ($count_inactive > 0) {
								?>
										<span class="mnu_inactive_msg">
											<?php echo $count_inactive; ?>
										</span>
								<?php
									}
								?>
							</a>
							<ul>
								<li><a href="<?php echo BASE_URI; ?>users-add.php"><?php _e('Add new', 'cftp_admin'); ?></a></li>
								<li><a href="<?php echo BASE_URI; ?>users.php"><?php _e('Manage system users', 'cftp_admin'); ?></a></li>
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
			<?php
					}
				}
				/** Generate the CLIENTS menu */
				else {
			?>
					<li class="no_arrow"><a href="<?php echo BASE_URI; ?>upload-from-computer.php"><?php _e('Upload from device', 'cftp_admin'); ?></a></li>
					<li class="no_arrow"><a href="<?php echo BASE_URI; ?>manage-files.php"><?php _e('Manage files', 'cftp_admin'); ?></a></li>
					<li class="no_arrow"><a href="<?php echo BASE_URI.'my_files/'; ?>"><?php _e('View my files', 'cftp_admin'); ?></a></li>
			<?php
				}
			?>
			</ul>
		</nav>

		<?php
			/**
			 * Gets the mark up abd values for the System Updated and
			 * errors messages.
			 */
			include(ROOT_DIR.'/includes/updates.messages.php');
		?>
	</header>

<?php
	/**
	 * Check if the current user has permission to view this page.
	 * If not, an error message is generated instead of the actual content.
	 * The allowed levels are defined on each individual page before the
	 * inclusion of this file.
	 */
	can_see_content($allowed_levels);
?>
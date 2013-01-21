<?php
/**
 * This file generates the header for pages shown to unlogged users and
 * clients (log in form and, if allowed, self registration form).
 *
 * @package ProjectSend
 */
session_start();
ob_start();
header("Cache-control: private");

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
if (in_session_or_cookies($allowed_levels)) {
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
	<title><?php echo $page_title; ?> | <?php echo SYSTEM_NAME; ?></title>
	<link rel="shortcut icon" href="<?php echo BASE_URI; ?>favicon.ico" />
	<link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_URI; ?>styles/shared.css" />
	<link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_URI; ?>styles/base.css" />
	<script src="<?php echo BASE_URI; ?>includes/js/jquery.validations.js" type="text/javascript"></script>

	<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>

	<link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_URI; ?>styles/bootstrap.min.css" />
	<script type="text/javascript" src="<?php echo BASE_URI; ?>includes/js/bootstrap/modernizr-2.6.2-respond-1.1.0.min.js"></script>
	
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,300' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Abel' rel='stylesheet' type='text/css'>

	<script type="text/javascript">
		$(document).ready(function() {
			$('.button').click(function() {
				$(this).blur();
			});
		});
	</script>
</head>

<body>

	<header>
		<div id="header">
			<div id="lonely_logo">
				<h1><?php echo THIS_INSTALL_SET_TITLE; ?></h1>
				<p><?php _e('Provided by', 'cftp_admin'); ?> <?php echo SYSTEM_NAME; ?></p>
			</div>
		</div>
		<div id="login_header_low">
		</div>
	</header>

	<div id="main">
<?php
/**
 * Define the information about the current logged in user or client
 * used on the different validations across the system.
 *
 * @package		ProjectSend
 * @subpackage	Session
 */
session_start();
ob_start();
header("Cache-control: private");

/**
 * Global information on the current account to use accross the system.
 */
$global_user = get_current_user_username();
$global_level = get_current_user_level();

/**
 * Get the user information from the database
 */
if ($global_level != 0) {
	$global_account = get_user_by_username($global_user);
}
else {
	$global_account = get_client_by_username($global_user);
}

/**
 * Automatic log out if account is deactivated while session is on.
 */
if ($global_account['active'] == '0') {
	/** Prevent an infinite loop */
	if (!isset($_SESSION['logout'])) {
		$_SESSION['logout'] = '1';
	}
	else {
		unset($_SESSION['logout']);
		header("location:".BASE_URI.'process.php?do=logout');
		exit;
	}
}

/**
 * Save all the data on different constants
 */
define('CURRENT_USER_ID',$global_account['id']);
define('CURRENT_USER_USERNAME',$global_account['username']);
define('CURRENT_USER_NAME',$global_account['name']);
define('CURRENT_USER_EMAIL',$global_account['email']);
define('CURRENT_USER_LEVEL',$global_account['level']);

$global_id = $global_account['id'];
$global_name = $global_account['name'];
?>
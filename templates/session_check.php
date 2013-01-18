<?php
/**
 * Check for access permission to the files list.
 *
 * @package		ProjectSend
 * @subpackage	Templates
 */

/** This value comes from the index.php file located on the "my_files" folder. */
$client_username = $this_user;

/**
 * Check the "access" session var or cookie that are set on the index.php file
 * when you log in correctly.
 */
if (isset($_SESSION['access']) && $_SESSION['access'] == 'admin') { $grant_access = 1; }
if (isset($_SESSION['access']) && $_SESSION['access'] == $client_username) { $grant_access = 1; $is_client = 1; }
if (isset($_COOKIE['access']) && $_COOKIE['access'] == 'admin') { $grant_access = 1; }
if (isset($_COOKIE['access']) && $_COOKIE['access'] == $client_username) { $grant_access = 1; $is_client = 1; }

/** In case a client has a session or cookie but is deactivated */
if (isset($is_client)) {
	$sql_client = $database->query("SELECT active FROM tbl_users WHERE user='$client_username'");
	$row = mysql_fetch_array($sql_client);
	if ($row['active'] == '0') {
		header("location:".BASE_URI.'process.php?do=logout');
		exit;
	}
}

/** If the info is not found, redirect to the log in page. */
if (!isset($grant_access)) {
	header("location:".BASE_URI);
	exit;
}
?>
<?php
	ob_start();
	session_start();
	header("Cache-control: private");
	require_once('../sys.includes.php');
	$this_user = get_current_user_username();
	if (!empty($_GET['client'])) {
		$this_user = $_GET['client'];
	}
	include_once(TEMPLATE_PATH);
?>
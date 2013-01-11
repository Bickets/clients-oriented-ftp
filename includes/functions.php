<?php
/**
 * Define the common functions that can be accessed from anywhere.
 *
 * @package		ProjectSend
 * @subpackage	Functions
 */


/**
 * Check if ProjectSend is installed by looping over the main tables.
 * All tables must exist to verify the installation.
 * If any table is missing, the installation is considered corrupt.
 */
function is_projectsend_installed() {
	global $database;
	global $current_tables;

	$database->MySQLDB();
	$tables_missing = 0;
	/**
	 * This table list is defined on sys.vars.php
	 */
	foreach ($current_tables as $table) {
		$this_table = $database->query("SHOW TABLES LIKE '$table'");
		if (mysql_num_rows($this_table) == 0) {
			$tables_missing++;
		}
	}
	if ($tables_missing > 0) {
		return false;
	}
	else {
		return true;
	}
}

/**
 * Check if a client id exists on the database.
 * Used on the Edit client page.
 *
 * @return bool
 */
function client_exists_id($id)
{
	global $database;
	$id_exists = $database->query("SELECT * FROM tbl_clients WHERE id='$id'");
	$count_clients = mysql_num_rows($id_exists);
	if($count_clients > 0){
		return true;
	}
	else {
		return false;
	}
}

/**
 * Check if a user id exists on the database.
 * Used on the Edit user page.
 *
 * @return bool
 */
function user_exists_id($id)
{
	global $database;
	$id_exists = $database->query("SELECT * FROM tbl_users WHERE id='$id'");
	$count_users = mysql_num_rows($id_exists);
	if($count_users > 0){
		return true;
	}
	else {
		return false;
	}
}

/**
 * Get all the client information knowing only the id
 * Used on the Manage files page.
 *
 * @return array
 */
function get_client_by_id($client)
{
	global $database;
	$get_client_info = $database->query("SELECT * FROM tbl_clients WHERE id='$client'");
	while ($row = mysql_fetch_assoc($get_client_info)) {
		$information = array(
							'id' => $row['id'],
							'name' => $row['name'],
							'username' => $row['client_user'],
							'address' => $row['address'],
							'phone' => $row['phone'],
							'email' => $row['email'],
							'notify' => $row['notify'],
							'contact' => $row['contact'],
							'created_date' => $row['timestamp'],
							'created_by' => $row['created_by']
						);
		if(!empty($information)) {
			return $information;
		}
		else {
			return false;
		}
	}
}


/**
 * Get all the client information knowing only the log in username
 *
 * @return array
 */
function get_client_by_username($client)
{
	global $database;
	$get_client_info = $database->query("SELECT * FROM tbl_clients WHERE client_user='$client'");
	while ($row = mysql_fetch_assoc($get_client_info)) {
		$information = array(
							'id' => $row['id'],
							'name' => $row['name'],
							'username' => $row['client_user'],
							'address' => $row['address'],
							'phone' => $row['phone'],
							'email' => $row['email'],
							'notify' => $row['notify'],
							'contact' => $row['contact'],
							'created_date' => $row['timestamp'],
							'created_by' => $row['created_by']
						);
		if(!empty($information)) {
			return $information;
		}
		else {
			return false;
		}
	}
}


/**
 * Used on the file uploading process to determine if the client
 * needs to be notified by e-mail.
 */
function check_if_notify_client($client)
{
	global $database;
	$get_notify = $database->query("SELECT notify, email FROM tbl_clients WHERE client_user='$client'");
	while ($row = mysql_fetch_assoc($get_notify)) {
		if($row['notify'] === '1') {
			return $row['email'];
		}
		else {
			return false;
		}
	}
}


/**
 * Get all the user information knowing only the log in username
 *
 * @return array
 */
function get_user_by_username($user)
{
	global $database;
	$get_client_info = $database->query("SELECT * FROM tbl_users WHERE user='$user'");
	while ($row = mysql_fetch_assoc($get_client_info)) {
		$information = array(
							'id' => $row['id'],
							'username' => $row['user'],
							'name' => $row['name'],
							'email' => $row['email'],
							'level' => $row['level'],
							'created_date' => $row['timestamp']
						);
		if(!empty($information)) {
			return $information;
		}
		else {
			return false;
		}
	}
}


/**
 * Standard footer mark up and information generated on this function to
 * prevent code repetition.
 * Used on the default template, log in page, install page and the back-end
 * footer file.
 */
function default_footer_info()
{
?>
	<div id="footer">
		<span><?php _e('ProjectSend Free software (GPL2) | 2007 - ', 'cftp_admin'); ?> <?php echo date("Y") ?> | <a href="<?php echo SYSTEM_URI; ?>" target="_blank"><?php echo SYSTEM_URI_LABEL; ?></a></span>
	</div>
<?php
}


/**
 * Standard "There are no clients" message mark up and information
 * generated on this function to prevent code repetition.
 *
 * Used on the upload pages and the clients list.
 */
function message_no_clients()
{
?>
	<div class="whitebox whiteform whitebox_text">
		<p><?php _e('There are no clients at the moment', 'cftp_admin'); ?></p>
		<p><a href="clients-add.php" target="_self"><?php _e('Create a new one', 'cftp_admin'); ?></a> <?php _e('to be able to upload files for that account.', 'cftp_admin'); ?></p>
	</div>
<?php
}


/**
 * Generate a system text message.
 *
 * Current CSS available message classes:
 * - message_ok
 * - message_error
 * - message_info
 *
 */	
function system_message($type,$message,$div_id = '')
{
	$return = '<div class="message message_'.$type.'"';
	if (isset($div_id) && $div_id != '') {
		$return .= ' id="'.$div_id.'"';
	}
	$return .= '>'.$message.'</div>';
	return $return;
}


/**
 * Function used accross the system to determine if the current logged in
 * account has permission to do something.
 * 
 */
function in_session_or_cookies($levels)
{
	if (isset($_SESSION['userlevel']) && (in_array($_SESSION['userlevel'],$levels))) {
		return true;
	}
	else if (isset($_COOKIE['userlevel']) && (in_array($_COOKIE['userlevel'],$levels))) {
		return true;
	}
	else {
		return false;
	}
}


/**
 * Returns the current logged in account level either from the active
 * session or the cookies.
 *
 * @todo Validate the returned value against the one stored on the database
 */
function get_current_user_level()
{
	if (isset($_SESSION['userlevel'])) {
		$level = $_SESSION['userlevel'];
	}
	elseif (isset($_COOKIE['userlevel'])) {
		$level = $_COOKIE['userlevel'];
	}
	return $level;
}


/**
 * Returns the current logged in account username either from the active
 * session or the cookies.
 *
 * @todo Validate the returned value against the one stored on the database
 */
function get_current_user_username()
{
	if (isset($_COOKIE['loggedin'])) {
		$user = $_COOKIE['loggedin'];
	}
	elseif (isset($_SESSION['loggedin'])) {
		$user = $_SESSION['loggedin'];
	}
	return $user;
}


/**
 * @author		brian dot folts at gmail dot com
 * @copyright	06-Sep-2006
 * @link		http://php.net/manual/es/function.mysql-real-escape-string.php
 */
function mysql_real_escape_array($array)
{
    return array_map("mysql_real_escape_string",$array);
}


/**
 * Solution by Philippe Flipflip. Fixes an error that would not convert special
 * characters when saving to the database.
 */
function encode_html($str) {
	$str = htmlentities($str, ENT_QUOTES);
	$str = mysql_real_escape_string($str);
	$str = nl2br($str);
	return $str;
}


/**
 * Based on a script found on webcheatsheet. Fixed an issue from the original code.
 * Used on the installation form to fill the URI field automatically.
 *
 * @author		http://webcheatsheet.com
 * @link		http://www.webcheatsheet.com/php/get_current_page_url.php
 */
function get_current_url()
{
	$pageURL = 'http';
	if (!empty($_SERVER['HTTPS'])) {
		if($_SERVER['HTTPS'] == 'on'){
			$pageURL .= "s";
		}
	}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}

	/**
	 * Check if we are accesing the install folder or the index.php file directly
	 */
	$extension = substr($pageURL,-4);
	if ($extension=='.php') {
		$pageURL = substr($pageURL,0,-17);
		return $pageURL;
	}
	else {
		$pageURL = substr($pageURL,0,-8);
		return $pageURL;
	}
}

/**
 * Receives the size of a file in bytes, and formats it for readability.
 * Used on files listings (templates and the files manager).
 */
function format_file_size($size)
{
	if ($size < 1024) {
		$format_size = $size . " bytes";
	}
	else if ($size < 1024*1000) {
		$divide_by = 1024;
		$format_size = round(($size / $divide_by), 1) . " kB";
	}
	else if ($size < 1024*1000*1000) {
		$divide_by = 1024*1000;
		$format_size = round(($size / $divide_by), 1) . " MB";
	}
	else {
		$divide_by = 1024*1000*1000;
		$format_size = round(($size / $divide_by), 1) . " GB";
	}
	return $format_size;
}

/**
 * Delete just one file.
 * Used on the files managment page.
 */
function delete_file($filename)
{
	chmod($filename, 0777);
	unlink($filename);
}

/**
 * Deletes all files and sub-folders of the selected directory.
 * Used when deleting a client.
 */
function delete_recursive($dir)
{
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false ) {
				if( $file != "." && $file != ".." ) {
					if( is_dir( $dir . $file ) ) {
						delete_recursive( $dir . $file . "/" );
						rmdir( $dir . $file );
					}
					else {
						chmod($dir.$file, 0777);
						unlink($dir.$file);
					}
				}
		   }
		   closedir($dh);
		   rmdir($dir);
	   }
	}
}

/**
 * Generates a random string to be used on the automatically
 * created zip files.
 */
function generateRandomString($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $rnd_result = '';
    for ($i = 0; $i < $length; $i++) {
        $rnd_result .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $rnd_result;
}
?>
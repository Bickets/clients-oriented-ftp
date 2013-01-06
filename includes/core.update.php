<?php
/**
 * This file is called on header.php and checks the database to see
 * if it up to date with the current software version.
 *
 * In case you are updating from an old one, the new values, columns
 * and rows will be created, and a message will appear under the menu
 * one time only.
 *
 * @package ProjectSend
 * @subpackage Core
 */

$allowed_update = array(9,8,7);
if (in_session_or_cookies($allowed_update)) {
	
	$updates_made = 0;
	
	/**
	 * r92 updates
	 * The logo file name is now stored on the database.
	 * If the row doesn't exist, create it and add the default value.
	 */
	$new_database_values = array(
									'logo_filename' => 'logo.png'
								);
	
	foreach($new_database_values as $row => $value) {
		$q = "SELECT * FROM tbl_options WHERE name = '$row'";
		$sql = $database->query($q);

		if(!mysql_num_rows($sql)) {
			$updates_made++;
			$qi = "INSERT INTO tbl_options (name, value) VALUES ('$row', '$value')";
			$sqli = $database->query($qi);
		}
		unset($q);
	}

	/**
	 * r94 updates
	 * A new column was added on the clients table, to store the value of the
	 * user that created it.
	 * If the column doesn't exist, create it.
	 */
	$q = $database->query("SELECT created_by FROM tbl_clients");
	if (!$q) {
		mysql_query("ALTER TABLE tbl_clients ADD created_by VARCHAR(".MAX_USER_CHARS.") NOT NULL");
		$updates_made++;
	}

	/**
	 * r102 updates
	 * A function was added to hide or show uploaded files from the clients lists.
	 * If the "hidden" column on the files table doesn't exist, create it.
	 */
	$q = $database->query("SELECT hidden FROM tbl_files");
	if (!$q) {
		mysql_query("ALTER TABLE tbl_files ADD hidden INT(1) NOT NULL");
		$updates_made++;
	}


	/**
	 * r135 updates
	 * The e-mail address used for notifications to new users, clients and files
	 * can now be defined on the options page. When installing or updating, it
	 * will default to the primary admin user's e-mail.
	 */
	$sql = $database->query('SELECT * FROM tbl_users WHERE id="1"');
	while($row = mysql_fetch_array($sql)) {
		$set_admin_email = $row['email'];
	}

	$new_database_values = array(
									'admin_email_address' => $set_admin_email
								);
	
	foreach($new_database_values as $row => $value) {
		$q = "SELECT * FROM tbl_options WHERE name = '$row'";
		$sql = $database->query($q);

		if(!mysql_num_rows($sql)) {
			$updates_made++;
			$qi = "INSERT INTO tbl_options (name, value) VALUES ('$row', '$value')";
			$sqli = $database->query($qi);
		}
		unset($q);
	}

	/**
	 * r183 updates
	 * A new column was added on the clients table, to store the value of the
	 * account active status.
	 * If the column doesn't exist, create it. Also, mark every existing
	 * client as active (1).
	 */
	$q = $database->query("SELECT active FROM tbl_clients");
	if (!$q) {
		mysql_query("ALTER TABLE tbl_clients ADD active tinyint(1) NOT NULL");
		$sql = $database->query('SELECT * FROM tbl_clients');
		while($row = mysql_fetch_array($sql)) {
			$database->query('UPDATE tbl_clients SET active = 1');
		}
		$updates_made++;

		/**
		 * Add the "users can register" value to the options table.
		 * Defaults to 0, since this is a new feature.
		 * */
		$new_database_values = array(
										'clients_can_register' => '0'
									);
		foreach($new_database_values as $row => $value) {
			$q = "SELECT * FROM tbl_options WHERE name = '$row'";
			$sql = $database->query($q);
	
			if(!mysql_num_rows($sql)) {
				$updates_made++;
				$qi = "INSERT INTO tbl_options (name, value) VALUES ('$row', '$value')";
				$sqli = $database->query($qi);
			}
			unset($q);
		}
	}
}
?>
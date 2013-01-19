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
	$current_version = substr(CURRENT_VERSION, 1);
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
	
	/**
	 * r189 updates
	 * Move every uploaded file to a neutral location
	 */
	$work_folder = ROOT_DIR.'/upload/';
	$folders = glob($work_folder."*", GLOB_NOSORT);

	foreach ($folders as $folder) {
		if(is_dir($folder) && !stristr($folder,'temp') && !stristr($folder,'assigned_files')) {
			$files = glob($folder.'/*', GLOB_NOSORT);
			foreach ($files as $file) {
				if(is_file($file) && !stristr($file,'index.php')) {
					$filename = basename($file);
					$mark_for_moving[$filename] = $file;
				}
			}
		}
	}
	
	$work_folder = UPLOADED_FILES_FOLDER;
	if (!empty($mark_for_moving)) {
		foreach ($mark_for_moving as $filename => $path) {
			$new = UPLOADED_FILES_FOLDER.'/'.$filename;
			$try_moving = rename($path, $new);
			chmod($new, 0644);
		}
	}

	/**
	 * r202 updates
	 * Combine clients and users on the same table.
	 */
	$q = $database->query("SELECT created_by FROM tbl_users");
	if (!$q) {
		/* Mark existing users as active */
		$database->query("ALTER TABLE tbl_users ADD address TEXT NOT NULL, ADD phone varchar(32) NOT NULL, ADD notify TINYINT(1) NOT NULL, ADD contact TEXT NOT NULL, ADD created_by varchar(32) NOT NULL, ADD active TINYINT(1) NOT NULL ");
		$database->query("INSERT INTO tbl_users"
								." (user, password, name, email, timestamp, address, phone, notify, contact, created_by, active, level)"
								." SELECT client_user, password, name, email, timestamp, address, phone, notify, contact, created_by, active, '0' FROM tbl_clients");
		$database->query("UPDATE tbl_users SET active = 1");
		$updates_made++;
	}


	/**
	 * r210 updates
	 * A new database table was added, that allows the creation of clients groups.
	 */
	$q = $database->query("SELECT id FROM tbl_groups");
	if (!$q) {
		/** Create the GROUPS table */
		$q1 = '
		CREATE TABLE IF NOT EXISTS `tbl_groups` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `timestamp` int(15) NOT NULL,
		  `created_by` varchar(32) NOT NULL,
		  `name` varchar(32) NOT NULL,
		  `description` text NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=62 ;
		';
		$database->query($q1);
		/** Create the MEMBERS table */
		$q2 = '
		CREATE TABLE IF NOT EXISTS `tbl_members` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `timestamp` int(15) NOT NULL,
		  `added_by` varchar(32) NOT NULL,
		  `client_id` int(11) NOT NULL,
		  `group_id` int(11) NOT NULL,
		  PRIMARY KEY (`id`),
		  FOREIGN KEY (`client_id`) REFERENCES tbl_users(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
		  FOREIGN KEY (`group_id`) REFERENCES tbl_groups(`id`) ON DELETE CASCADE ON UPDATE CASCADE
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=62 ;
		';
		$database->query($q2);
		$updates_made++;

		/**
		 * r215 updates
		 * Change the engine of every table to InnoDB, to use foreign keys on the 
		 * groups feature.
		 * Included inside the previous update since that is not an officially
		 * released version.
		 */
		foreach ($current_tables as $working_table) {
			$q = $database->query("ALTER TABLE $working_table ENGINE = InnoDB");
			$updates_made++;
		}
	}

	/**
	 * r217 updates
	 * A new database table was added, to facilitate the relation of files
	 * with clients and groups.
	 */
	$q = $database->query("SELECT id FROM tbl_files_relations");
	if (!$q) {
		$q1 = '
		CREATE TABLE IF NOT EXISTS `tbl_files_relations` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `timestamp` int(15) NOT NULL,
		  `file_id` int(11) NOT NULL,
		  `client_id` int(11) NOT NULL,
		  `group_id` int(11) NOT NULL,
		  FOREIGN KEY (`file_id`) REFERENCES tbl_files(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
		  FOREIGN KEY (`client_id`) REFERENCES tbl_users(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
		  FOREIGN KEY (`group_id`) REFERENCES tbl_groups(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=62 ;
		';
		$database->query($q1);
		$updates_made++;
	}

	/**
	 * r2xx updates
	 * A new database table was added, that stores users and clients actions.
	 */
	/*
	$q = $database->query("SELECT id FROM tbl_log");
	if (!$q) {
		$q1 = '
		CREATE TABLE IF NOT EXISTS `tbl_log` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `timestamp` int(15) NOT NULL,
		  `action` int(2) NOT NULL,
		  `owner` int(2) NOT NULL,
		  `affected` int(2) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=62 ;
		';
	}
	*/
}
?>
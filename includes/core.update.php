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

	/** Remove "r" from version */
	$current_version = substr(CURRENT_VERSION, 1);
	$updates_made = 0;
	$updates_errors = 0;

	/**
	 * r264 updates
	 * Save the value of the last update on the database, to prevent
	 * running all this queries everytime a page is loaded.
	 * Done on top for convenience.
	 */
	$version_query = "SELECT value FROM tbl_options WHERE name = 'last_update'";
	$version_sql = $database->query($version_query);

	if(!mysql_num_rows($version_sql)) {
		$updates_made++;
		$qv = "INSERT INTO tbl_options (name, value) VALUES ('last_update', '264')";
		$sqlv = $database->query($qv);
		$updates_made++;
	}
	else {
		while($vres = mysql_fetch_array($version_sql)) {
			$last_update = $vres['value'];
		}
	}
	
	if ($last_update < $current_version || !isset($last_update)) {

		/**
		 * r92 updates
		 * The logo file name is now stored on the database.
		 * If the row doesn't exist, create it and add the default value.
		 */
		if ($last_update < 92) {
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
		}

		/**
		 * r94 updates
		 * A new column was added on the clients table, to store the value of the
		 * user that created it.
		 * If the column doesn't exist, create it.
		 */
		if ($last_update < 94) {
			$q = $database->query("SELECT created_by FROM tbl_clients");
			if (!$q) {
				mysql_query("ALTER TABLE tbl_clients ADD created_by VARCHAR(".MAX_USER_CHARS.") NOT NULL");
				$updates_made++;
			}
		}

		/**
		 * DEPRECATED
		 * r102 updates
		 * A function was added to hide or show uploaded files from the clients lists.
		 * If the "hidden" column on the files table doesn't exist, create it.
		 */
		/*
		$q = $database->query("SELECT hidden FROM tbl_files");
		if (!$q) {
			mysql_query("ALTER TABLE tbl_files ADD hidden INT(1) NOT NULL");
			$updates_made++;
		}
		*/

		/**
		 * r135 updates
		 * The e-mail address used for notifications to new users, clients and files
		 * can now be defined on the options page. When installing or updating, it
		 * will default to the primary admin user's e-mail.
		 */
		if ($last_update < 135) {
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
		}

		/**
		 * r183 updates
		 * A new column was added on the clients table, to store the value of the
		 * account active status.
		 * If the column doesn't exist, create it. Also, mark every existing
		 * client as active (1).
		 */
		if ($last_update < 183) {
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

		/**
		 * r189 updates
		 * Move every uploaded file to a neutral location
		 */
		if ($last_update < 189) {
			$work_folder = ROOT_DIR.'/upload/';
			$folders = glob($work_folder."*", GLOB_NOSORT);
		
			foreach ($folders as $folder) {
				if(is_dir($folder) && !stristr($folder,'temp') && !stristr($folder,'files')) {
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
		}

		/**
		 * r202 updates
		 * Combine clients and users on the same table.
		 */
		if ($last_update < 202) {
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
		}

		/**
		 * r210 updates
		 * A new database table was added, that allows the creation of clients groups.
		 */
		if ($last_update < 210) {
			$q = $database->query("SELECT id FROM tbl_groups");
			if (!$q) {
				/** Create the GROUPS table */
				$q1 = '
				CREATE TABLE IF NOT EXISTS `tbl_groups` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
				  `created_by` varchar(32) NOT NULL,
				  `name` varchar(32) NOT NULL,
				  `description` text NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
				';
				$database->query($q1);
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
		}

		/**
		 * r219 updates
		 * A new database table was added.
		 * Folders are related to clients or groups.
		 */
		if ($last_update < 219) {
			$q = $database->query("SELECT id FROM tbl_folders");
			if (!$q) {
				$q1 = '
				CREATE TABLE IF NOT EXISTS `tbl_folders` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `parent` int(11) DEFAULT NULL,
				  `name` varchar(32) NOT NULL,
				  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
				  `client_id` int(11) DEFAULT NULL,
				  `group_id` int(11) DEFAULT NULL,
				  FOREIGN KEY (`parent`) REFERENCES tbl_folders(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  FOREIGN KEY (`client_id`) REFERENCES tbl_users(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  FOREIGN KEY (`group_id`) REFERENCES tbl_groups(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
				';
				$database->query($q1);
				$updates_made++;
			}
		}

		/**
		 * r217 updates (after previous so the folder column can be created)
		 * A new database table was added, to facilitate the relation of files
		 * with clients and groups.
		 */
		if ($last_update < 217) {
			$q = $database->query("SELECT id FROM tbl_files_relations");
			if (!$q) {
				$q1 = '
				CREATE TABLE IF NOT EXISTS `tbl_files_relations` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
				  `file_id` int(11) NOT NULL,
				  `client_id` int(11) DEFAULT NULL,
				  `group_id` int(11) DEFAULT NULL,
				  `folder_id` int(11) DEFAULT NULL,
				  `hidden` int(1) NOT NULL,
				  `download_count` int(16) NOT NULL,
				  FOREIGN KEY (`file_id`) REFERENCES tbl_files(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  FOREIGN KEY (`client_id`) REFERENCES tbl_users(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  FOREIGN KEY (`group_id`) REFERENCES tbl_groups(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  FOREIGN KEY (`folder_id`) REFERENCES tbl_folders(`id`) ON UPDATE CASCADE,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
				';
				$database->query($q1);
				$updates_made++;
			}
		}

		/**
		 * r241 updates
		 * A new database table was added, that stores users and clients actions.
		 */
		if ($last_update < 241) {
			$q = $database->query("SELECT id FROM tbl_actions_log");
			if (!$q) {
				$q1 = '
				CREATE TABLE IF NOT EXISTS `tbl_actions_log` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
				  `action` int(2) NOT NULL,
				  `owner_id` int(11) NOT NULL,
				  `owner_user` text DEFAULT NULL,
				  `affected_file` int(11) DEFAULT NULL,
				  `affected_account` int(11) DEFAULT NULL,
				  `affected_file_name` text DEFAULT NULL,
				  `affected_account_name` text DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
				';
				$database->query($q1);
				$updates_made++;
			}
		}
		
		/**
		 * r266 updates
		 * Set timestamp columns as real timestamp data, instead of INT
		 */
		if ($last_update < 266) {
			$q1 = "ALTER TABLE `tbl_users` ADD COLUMN `timestamp2` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()";
			$q2 = "UPDATE `tbl_users` SET `timestamp2` = FROM_UNIXTIME(`timestamp`)";
			$q3 = "ALTER TABLE `tbl_users` DROP COLUMN `timestamp`";
			$q4 = "ALTER TABLE `tbl_users` CHANGE `timestamp2` `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()";
			$database->query($q1);
			$database->query($q2);
			$database->query($q3);
			$database->query($q4);
			$updates_made++;
		}

		/**
		 * r275 updates
		 * A new database table was added.
		 * It stores the new files-to clients relations to be
		 * used on notifications.
		 */
		if ($last_update < 275) {
			$q = $database->query("SELECT id FROM tbl_notifications");
			if (!$q) {
				$q1 = '
				CREATE TABLE IF NOT EXISTS `tbl_notifications` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
				  `file_id` int(11) NOT NULL,
				  `client_id` int(11) NOT NULL,
				  `upload_type` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
				';
				$database->query($q1);
				$updates_made++;
			}
		}

		/**
		 * r278 updates
		 * Set timestamp columns as real timestamp data, instead of INT
		 */
		if ($last_update < 278) {
			$q1 = "ALTER TABLE `tbl_files` ADD COLUMN `timestamp2` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()";
			$q2 = "UPDATE `tbl_files` SET `timestamp2` = FROM_UNIXTIME(`timestamp`)";
			$q3 = "ALTER TABLE `tbl_files` DROP COLUMN `timestamp`";
			$q4 = "ALTER TABLE `tbl_files` CHANGE `timestamp2` `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()";
			$database->query($q1);
			$database->query($q2);
			$database->query($q3);
			$database->query($q4);
			$updates_made++;
		}


		/**
		 * r282 updates
		 * Add new options to select the handler for sending emails.
		 */
		if ($last_update < 282) {
			$new_database_values = array(
											'mail_system_use' => 'mail',
											'mail_smtp_host' => '',
											'mail_smtp_port' => '',
											'mail_smtp_user' => '',
											'mail_smtp_pass' => '',
											'mail_from_name' => THIS_INSTALL_SET_TITLE
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
		 * r338 updates
		 * The Members table wasn't being created on existing installations.
		 */
		if ($last_update < 338) {
			$q = $database->query("SELECT id FROM tbl_members");
			if (!$q) {
				/** Create the MEMBERS table */
				$q2 = '
				CREATE TABLE IF NOT EXISTS `tbl_members` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
				  `added_by` varchar(32) NOT NULL,
				  `client_id` int(11) NOT NULL,
				  `group_id` int(11) NOT NULL,
				  PRIMARY KEY (`id`),
				  FOREIGN KEY (`client_id`) REFERENCES tbl_users(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  FOREIGN KEY (`group_id`) REFERENCES tbl_groups(`id`) ON DELETE CASCADE ON UPDATE CASCADE
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
				';
				$database->query($q2);
				$updates_made++;
			}
		}


		/**
		 * r346 updates
		 * chmod the cache folder and main files of timthumb to 775
		 */
		if ($last_update < 346) {
			$chmods = 0;
			$timthumb_file = ROOT_DIR.'/includes/timthumb/timthumb.php';
			$cache_folder = ROOT_DIR.'/includes/timthumb/cache';
			$index_file = ROOT_DIR.'/includes/timthumb/cache/index.html';
			$touch_file = ROOT_DIR.'/includes/timthumb/cache/timthumb_cacheLastCleanTime.touch';
			if (@chmod($timthumb_file, 0755)) { $chmods++; }
			if (@chmod($cache_folder, 0755)) { $chmods++; }
			if (@chmod($index_file, 0755)) { $chmods++; }
			if (@chmod($touch_file, 0755)) { $chmods++; }

			if ($chmods > 0) {
				$updates_made++;
			}
			
			/** This message is mandatory */
			$updates_errors++;
			if ($updates_errors > 0) {
				$updates_error_str = __("If images thumbnails aren't working for you on your client's files lists (even your company logo there and on the branding page) please chmod the includes/timthumb/cache folder to 777 -try both in that order- and then do the same with the 'index.html' and 'timthumb_cacheLastCleanTime.touch' files inside that folder. Then try lowering each file to 644 and see if everything is still working.", 'cftp_admin');
			}
		}

		/** Update the database */
		$database->query("UPDATE tbl_options SET value ='$current_version' WHERE name='last_update'");

		/** Record the action log */
		$new_log_action = new LogActions();
		$log_action_args = array(
								'action' => 30,
								'owner_id' => $global_id,
								'affected_account_name' => $current_version
							);
		$new_record_action = $new_log_action->log_action_save($log_action_args);
	}
}
?>
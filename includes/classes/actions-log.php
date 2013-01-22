<?php
/**
 * Class that handles all the actions that are logged on the database.
 *
 * @package		ProjectSend
 * @subpackage	Classes
 *
 * Reference of actions list by number:
 *
 * 0-	ProjecSend has been installed correctly
 * 1-	Account logs in through the form
 * 2-	A user creates a new user account
 * 3-	A user creates a new client account
 * 4-	A client registers an account for himself
 * 5-	A file is uploaded by an user
 * 6-	A file is uploaded by a client
 * 7-	A file is downloaded by a user (on "Client view" mode)
 * 8-	A file is downloaded by a client
 * 9-	A file has been unassigned from a client.
 * 10-	A file has been unassigned from a group.
 * 11-	A file has been deleted.
 * 12-	A user was edited.
 * 13-	A client was edited.
 * 14-	A group was edited.
 *
 * More to be added soon.
 */

class LogActions
{

	var $action = '';

	/**
	 * Create a new client.
	 */
	function log_action_save($arguments)
	{
		global $database;
		$this->state = array();

		/** Define the account information */
		$this->action = $arguments['action'];
		$this->owner_id = $arguments['owner_id'];
		$this->affected_file = (!empty($arguments['affected_file'])) ? $arguments['affected_file'] : '';
		$this->affected_account = (!empty($arguments['affected_account'])) ? $arguments['affected_account'] : '';

		/** Insert the client information into the database */
		$this->timestamp = time();
		$lq = "INSERT INTO tbl_actions_log (action,owner_id";
		
			if (!empty($this->affected_file)) { $lq .= ",affected_file"; }
			if (!empty($this->affected_account)) { $lq .= ",affected_account"; }
		
		$lq .= ",timestamp) VALUES ('$this->action', '$this->owner_id'";
		
			if (!empty($this->affected_file)) { $lq .= ",$this->affected_file"; }
			if (!empty($this->affected_account)) { $lq .= ",$this->affected_account"; }

		$lq .= ", '$this->timestamp')";
		$this->sql_query = $database->query($lq);
	}

}

?>
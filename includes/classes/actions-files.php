<?php
/**
 * Class that handles all the actions and functions that can be applied to
 * the already uploaded files.
 *
 * @package		ProjectSend
 * @subpackage	Classes
 */

class FilesActions
{

	var $files = array();
	
	/**
	 * This function is used to get all the information of a file on a
	 * single function, to avoid repetition of code when doing other
	 * actions.
	 *
	 * @return array
	 */
	function get_file_data_by_id($file_id)
	{
		global $database;
		/**
		 * Query 1
		 * Get the file name that was generated on upload (row url) and
		 * the client that the file belongs to.
		 */
		$this->sql1 = $database->query('SELECT url,client_user FROM tbl_files WHERE id="' . $file_id .'"');
		$this->file_data = mysql_fetch_assoc($this->sql1);
		$this->file_information = array(
										'url' => $this->file_data['url'],
										'client_user' => $this->file_data['client_user']
									);
		/**
		 * Query 2
		 * Get the id of the the client that the file is assigned to.
		 */
		$this->sql2 = $database->query('SELECT id FROM tbl_clients WHERE client_user="' . $this->file_information['client_user'] .'"');
		$this->client_data = mysql_fetch_row($this->sql2);
		$this->file_information['client_id'] = $this->client_data[0];

		return $this->file_information;
	}

	function delete_files($file_id)
	{
		global $database;
		$this->check_level = array(9,8);
		if (isset($file_id)) {
			/**
			 * Get all the relevant file information using the id parameter
			 *
			 * @see get_file_data_by_id
			 */
			$this->file_information = $this->get_file_data_by_id($file_id);
			$this->file_url = $this->file_information['url'];
			$this->client_user = $this->file_information['client_user'];
			$this->client_id = $this->file_information['client_id'];
			/** Do a permissions check */
			if (isset($this->check_level) && in_session_or_cookies($this->check_level)) {
				/** Delete the reference to the file on the database */
				$this->sql = $database->query('DELETE FROM tbl_files WHERE client_user="' . $this->client_user .'" AND id="' . $file_id . '"');
				/**
				 * Use the client and uri information to delete the file
				 * and the thumbnail (if it was created).
				 *
				 * @see delete_file
				 */
				$this->original = UPLOADED_FILES_FOLDER . $this->file_url;
				$this->thumb = UPLOADED_FILES_FOLDER . $this->file_url;
				delete_file($this->original);
				if (file_exists($this->thumb)) {
					delete_file($this->thumb);
				}
			}
		}
	}
	
	function change_files_hide_status($file_id,$change_to)
	{
		global $database;
		$this->check_level = array(9,8,7);
		if (isset($file_id)) {
			$this->file_information = $this->get_file_data_by_id($file_id);
			$this->client_id = $this->file_information['client_id'];
			/** Do a permissions check */
			if (isset($this->check_level) && in_session_or_cookies($this->check_level)) {
				$this->sql = $database->query('UPDATE tbl_files SET hidden='.$change_to.' WHERE id="' . $file_id . '"');
			}
		}
	}

}

?>
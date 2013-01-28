<?php
/**
 * Class that handles the log out and file download actions.
 *
 * @package		ProjectSend
 */
$allowed_levels = array(9,8,7,0);
require_once('sys.includes.php');
require_once('header.php');

class process {
	function process() {
		$this->database = new MySQLDB;
		switch ($_GET['do']) {
			case 'download':
				$this->download_file();
			break;
			case 'zip_download':
				$this->download_zip();
			break;
			case 'logout':
				$this->logout();
			break;
			default:
				header('Location: '.BASE_URI);
			break;
		}
		$this->database->Close();
	}
	
	function download_file() {
		$this->check_level = array(9,8,7,0);
		if (isset($_GET['url']) && isset($_GET['client'])) {
			/** Do a permissions check for logged in user */
			if (isset($this->check_level) && in_session_or_cookies($this->check_level)) {
				$current_level = get_current_user_level();

				$this->sum_sql = 'UPDATE tbl_files_relations SET download_count=download_count+1 WHERE file_id="' . $_GET['id'] .'"';
				if ($_GET['origin'] == 'group') {
					if (!empty($_GET['group_id'])) {
						$this->group_id = $_GET['group_id'];
						$this->sum_sql .= " AND group_id = '$this->group_id'";
					}
				} else {
					$this->client_id = $_GET['client_id'];
					$this->sum_sql .= " AND client_id = '$this->client_id'";
				}

				$this->sql = $this->database->query($this->sum_sql);
				
				/**
				 * The owner ID is generated here to prevent false results
				 * from a modified GET url.
				 */
				if ($current_level == 0) {
					$log_action = 8;
					$log_action_owner_id = $_GET['client_id'];
				}
				else {
					$log_action = 7;
					$global_user = get_current_user_username();
					$global_id = get_logged_account_id($global_user);
					$log_action_owner_id = $global_id;
				}

				/** Record the action log */
				$new_log_action = new LogActions();
				$log_action_args = array(
										'action' => $log_action,
										'owner_id' => $log_action_owner_id,
										'affected_file' => $_GET['id'],
										'affected_file_name' => $_GET['url'],
										'affected_account' => $_GET['client_id'],
										'affected_account_name' => $_GET['client'],
										'get_user_real_name' => true,
										'get_file_real_name' => true
									);
				$new_record_action = $new_log_action->log_action_save($log_action_args);

				$file = UPLOADED_FILES_FOLDER.$_GET['url'];
				if (file_exists($file)) {
					header('Content-Description: File Transfer');
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename='.basename($file));
					header('Content-Transfer-Encoding: binary');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . filesize($file));
					ob_clean();
					flush();
					readfile($file);
					exit;
				}
			}
		}
	}

	function download_zip() {
		$this->check_level = array(9,8,7,0);
		if (isset($_GET['files']) && isset($_GET['client'])) {
			// do a permissions check for logged in user
			if (isset($this->check_level) && in_session_or_cookies($this->check_level)) {
				foreach($_GET['files'] as $file_id) {
					$this->sql = $this->database->query('SELECT * FROM tbl_files WHERE id="' . $file_id .'"');
					$this->row = mysql_fetch_array($this->sql);
					$this->url = $this->row['url'];
					$file = UPLOADED_FILES_FOLDER.$this->url;
					if (file_exists($file)) {
						$file_list .= $this->url.',';
					}
				}
				ob_clean();
				flush();
				echo $file_list;
			}
		}
	}

	function logout() {
		header("Cache-control: private");
		unset($_SESSION['loggedin']);
		unset($_SESSION['access']);
		unset($_SESSION['userlevel']);
		session_destroy();
		// if there is a cookie, unset it
		setcookie("loggedin","",time()-COOKIE_EXP_TIME);
		setcookie("password","",time()-COOKIE_EXP_TIME);
		setcookie("access","",time()-COOKIE_EXP_TIME);
		setcookie("userlevel","",time()-COOKIE_EXP_TIME);
		header("location:index.php");
	}
}

$process = new process;
?>
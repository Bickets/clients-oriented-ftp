<?php
/**
 * Common information used on all clients templates.
 * Avoids the need to define all of this when creating a new template.
 *
 * @package		ProjectSend
 * @subpackage	Templates
 */

/**
 * Since the header.php file is shared between the back-end and the
 * templates, it's necessary to define the allowed levels, or else
 * the files list will not be available.
 */
$allowed_levels = array(9,8,7,0);

/**
 * Define a variable that will tell header.php if session_start()
 * needs to be called or not (since it is also called from
 * session_check.php
 */
$is_template = true;

/**
 * Loads a language file from the current template folder based on
 * the system options.
 */
$lang = SITE_LANG;
if(!isset($ld)) { $ld = 'cftp_admin'; }
require_once(ROOT_DIR.'/includes/classes/i18n.php');
I18n::LoadDomain(ROOT_DIR."/templates/".TEMPLATE_USE."/lang/{$lang}.mo", $ld);

$this_template = BASE_URI.'templates/'.TEMPLATE_USE.'/';

include_once(ROOT_DIR.'/templates/session_check.php');

/**
 * URI to the default template CSS file.
 */
$this_template_css = BASE_URI.'templates/'.TEMPLATE_USE.'/main.css';

$database->MySQLDB();

/**
 * Get all the client's information
 */
$client_info = get_client_by_username($this_user);

/**
 * Get the list of different groups the client belongs to.
 */
$sql_groups = $database->query("SELECT DISTINCT group_id FROM tbl_members WHERE client_id='".$client_info['id']."'");
$count_groups = mysql_num_rows($sql_groups);
if ($count_groups > 0) {
	while($row_groups = mysql_fetch_array($sql_groups)) {
		$groups_ids[] = $row_groups["group_id"];
	}
	$found_groups = implode(',',$groups_ids);
}

/** Get the client's own files */
$files_own_query = "SELECT id, file_id FROM tbl_files_relations WHERE client_id = '".$client_info['id']."' AND hidden = '0'";
$files_own = $database->query($files_own_query);
while($row_own_files = mysql_fetch_array($files_own)) {
	$found_own_files_temp[] = $row_own_files['file_id'];
	$found_own_files_ids = implode(',',array_unique($found_own_files_temp));
}
/** Get files from groups where client is member */
if (!empty($found_groups)) {
	$files_groups_query = "SELECT id, file_id, group_id FROM tbl_files_relations WHERE group_id IN ($found_groups) AND hidden = '0'";
	$files_groups = $database->query($files_groups_query);
	while($row_groups_files = mysql_fetch_array($files_groups)) {
		$found_groups_files_ids[] = array(
										'file_id' => $row_groups_files['file_id'],
										'group_id' => $row_groups_files['group_id']
									);
	}
}

$my_files = array();

if (!empty($found_own_files_ids)) {
	$q1a = "SELECT * FROM tbl_files WHERE id IN ($found_own_files_ids)";

	/** Add the search terms */	
	if(isset($_POST['search']) && !empty($_POST['search'])) {
		$search_terms = $_POST['search'];
		$q1a .= " AND (filename LIKE '%$search_terms%' OR description LIKE '%$search_terms%')";
		$no_results_error = 'search';
	}
	
	$q1 = $database->query($q1a);
	while($data_own = mysql_fetch_array($q1)) {
		$my_files[] = array(
							'origin' => 'own',
							'id' => $data_own['id'],
							'url' => $data_own['url'],
							'name' => $data_own['filename'],
							'description' => $data_own['description'],
							'timestamp' => $data_own['timestamp']
						);
	}
	
}

if (!empty($found_groups_files_ids)) {
	foreach ($found_groups_files_ids as $search_in_group) {
		$find_this_file_id = $search_in_group['file_id'];
		$q2a = "SELECT * FROM tbl_files WHERE id = $find_this_file_id";
		if(isset($_POST['search']) && !empty($_POST['search'])) {
			$search_terms = $_POST['search'];
			$q2a .= " AND (filename LIKE '%$search_terms%' OR description LIKE '%$search_terms%')";
			$no_results_error = 'search';
		}
		$q2 = $database->query($q2a);
		while($data_groups = mysql_fetch_array($q2)) {
			$my_files[] = array(
								'origin' => 'group',
								'group_id' => $search_in_group['group_id'],
								'id' => $data_groups['id'],
								'url' => $data_groups['url'],
								'name' => $data_groups['filename'],
								'description' => $data_groups['description'],
								'timestamp' => $data_groups['timestamp']
							);
		}
	}
}
?>
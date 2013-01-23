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

/**
 * Gets the files list on a default query that can be used on the template.
 * Only files that are not marked as hidden are retrieved.
 */
$fq = "SELECT id, file_id, client_id, group_id FROM tbl_files_relations WHERE ";
if (!empty($found_groups)) {
	$fq .= "(client_id='".$client_info['id']."' OR group_id IN ($found_groups)) AND hidden = '0'";
}
else {
	$fq .= "client_id='".$client_info['id']."' AND hidden = '0'";
}
$files_sql = $database->query($fq);
$count_files = mysql_num_rows($files_sql);
while($row_files = mysql_fetch_array($files_sql)) {
	$found_files_ids[] = $row_files['file_id'];
}
$found_files = implode(',',array_unique($found_files_ids));

$files_query = "SELECT * FROM tbl_files WHERE id IN ($found_files)";

/** Add the search terms */	
if(isset($_POST['search']) && !empty($_POST['search'])) {
	$search_terms = $_POST['search'];
	$files_query .= " AND (filename LIKE '%$search_terms%' OR description LIKE '%$search_terms%')";
	$no_results_error = 'search';
}

$template_files_sql = $database->query($files_query);
?>
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
 * Gets the files list on a default query that can be used on the template.
 * Only files that are not marked as hidden are retrieved.
 */
$files_query = 'SELECT * FROM tbl_files WHERE hidden=0';
$template_files_sql = $database->query($files_query);

/** Used to get the full name of the current client */
$current_fullname_sql = $database->query('SELECT * FROM tbl_users WHERE user="' . $this_user .'"');
while ($name_sql_row = mysql_fetch_array($current_fullname_sql)) {
	$user_full_name = $name_sql_row['name'];
}
?>
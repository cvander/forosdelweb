<?php
/*======================================================================*\
|| #################################################################### ||
|| # Social Share Manager - Foros del Web                             # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('ADMINCP_SCRIPT', 'fdwsocial');

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array();
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_template.php');

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminthreads'))
{
	print_cp_no_permission();
}

// ############################# LOG ACTION ###############################
log_admin_action();

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

print_cp_header($vbphrase['fdwsocial_title']);

if (empty($_REQUEST['do']))
{
	if (!empty($_REQUEST['fdwsocialid']))
	{
		$_REQUEST['do'] = 'edit';
	}
	else
	{
		$_REQUEST['do'] = 'modify';
	}
}

// #############################################################################

if ($_POST['do'] == 'updateactive')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'active' => TYPE_ARRAY_UINT,
	));
	
	if (count($vbulletin->GPC['active']) > 0)
	{
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "fdwsocial
			SET active = IF(FIELD(fdwsocialid, " . implode(', ', array_keys($vbulletin->GPC['active'])) . ") > 0, 1, 0)
		");
	}
	else
	{
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "fdwsocial SET active = 0");
	}
	
	build_fdwsocial();
	
	$_REQUEST['do'] = 'modify';
}

// #############################################################################

if ($_POST['do'] == 'remove')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'fdwsocialid' => TYPE_UINT
	));
	
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "fdwsocial WHERE fdwsocialid = {$vbulletin->GPC['fdwsocialid']}");
	
	build_fdwsocial();
	
	define('CP_REDIRECT', 'fdwsocial.php?do=modify');
	print_stop_message('deleted_fdwsocial_successfully');
}

// #############################################################################

if ($_POST['do'] == 'update')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'fdwsocialid' => TYPE_UINT,
		'title' => TYPE_NOHTML,
		'code' => TYPE_STR,
		'displayorder' => TYPE_UINT,
		'active' => TYPE_BOOL
	));
	
	if (empty($vbulletin->GPC['title']))
	{
		print_stop_message('invalid_title_specified');
	}
	
	// Validate code
	$vbulletin->GPC['parsed'] = compile_template($vbulletin->GPC['code']);
	$errors = check_template_errors($vbulletin->GPC['parsed']);
	if (!empty($errors))
	{
		print_stop_message('fdwsocial_invalid_code', $errors);
	}
	
	if ($vbulletin->GPC['fdwsocialid'])
	{
		$fdwsocialid =& $vbulletin->GPC['fdwsocialid'];
		
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "fdwsocial
			SET
				title = '" . $vbulletin->db->escape_string($vbulletin->GPC['title']) . "',
				code = '" . $vbulletin->db->escape_string($vbulletin->GPC['code']) . "',
				parsed = '" . $vbulletin->db->escape_string($vbulletin->GPC['parsed']) . "',
				displayorder = {$vbulletin->GPC['displayorder']},
				active = {$vbulletin->GPC['active']}
			WHERE fdwsocialid = $fdwsocialid
		");
	}
	else
	{
		$vbulletin->db->query_write("
			INSERT INTO " . TABLE_PREFIX . "fdwsocial
				(title, code, parsed, displayorder, active)
			VALUES (
				'" . $vbulletin->db->escape_string($vbulletin->GPC['title']) . "',
				'" . $vbulletin->db->escape_string($vbulletin->GPC['code']) . "',
				'" . $vbulletin->db->escape_string($vbulletin->GPC['parsed']) . "',
				{$vbulletin->GPC['displayorder']},
				{$vbulletin->GPC['active']}
			)
		");
	}
	
	build_fdwsocial();
	
	define('CP_REDIRECT', 'fdwsocial.php?do=modify');
	print_stop_message('saved_x_successfully', $vbulletin->GPC['title']);
}

// #############################################################################

if ($_REQUEST['do'] == 'modify')
{
	print_form_header('fdwsocial', 'updateactive');
	
	print_table_header($vbphrase['fdwsocial_manager'], 4);
	print_cells_row(
		array(
			$vbphrase['title'],
			$vbphrase['display_order'],
			$vbphrase['active'],
			$vbphrase['controls']
		),
		true
	);
	
	$fdwsocials = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "fdwsocial");
	
	while ($fdwsocial = $vbulletin->db->fetch_array($fdwsocials))
	{
		print_cells_row(array(
			$fdwsocial['title'],
			$fdwsocial['displayorder'],
			"<input type=\"checkbox\" name=\"active[$fdwsocial[fdwsocialid]]\" value=\"1\"" . ($fdwsocial['active'] ? ' checked="checked"' : '') . " />",
			construct_link_code($vbphrase['edit'], "fdwsocial.php?" . $vbulletin->session->vars['sessionurl'] . "do=edit&amp;fdwsocialid=$fdwsocial[fdwsocialid]") .
			construct_link_code($vbphrase['delete'], "fdwsocial.php?" . $vbulletin->session->vars['sessionurl'] . "do=delete&amp;fdwsocialid=$fdwsocial[fdwsocialid]")
		));
	}
	
	print_submit_row($vbphrase['fdwsocial_save_status'], false, 5);
	echo '<p align="center">' . construct_link_code($vbphrase['add_new_fdwsocial'], "fdwsocial.php?" . $vbulletin->session->vars['sessionurl'] . "do=add") . '</p>';
}

// #############################################################################

if ($_REQUEST['do'] == 'add' OR $_REQUEST['do'] == 'edit')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'fdwsocialid' => TYPE_UINT
	));
	
	$table_title = $vbphrase['add'];
	
	$fdwsocial = array(
		'fdwsocialid' => 0,
		'active' => true,
	);
	
	if ($vbulletin->GPC['fdwsocialid'])
	{
		$fdwsocial = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "fdwsocial WHERE fdwsocialid = {$vbulletin->GPC['fdwsocialid']}");
		$table_title = $vbphrase['fdwsocial_edit'] . " <span class=\"normal\">$fdwsocial[title]</span>";
	}
	else
	{
		$maxorder = $vbulletin->db->query_first("SELECT MAX(displayorder) AS maxorder FROM " . TABLE_PREFIX . "fdwsocial");
		$fdwsocial['displayorder'] = $maxorder['maxorder'] + 10;
	}
	
	print_form_header('fdwsocial', 'update');
	construct_hidden_code('fdwsocialid', $vbulletin->GPC['fdwsocialid']);

	print_table_header($table_title);

	print_input_row($vbphrase['title'], 'title', $fdwsocial['title'], 0, 60);
	print_textarea_row($vbphrase['code'], 'code', $fdwsocial['code'], 8, 60, true, false);
	print_input_row($vbphrase['display_order'], 'displayorder', $fdwsocial['displayorder'], 0, 20);
	
	print_yes_no_row($vbphrase['active'], 'active', $fdwsocial['active']);
	
	print_submit_row();
}

// #############################################################################

if ($_REQUEST['do'] == 'delete')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'fdwsocialid' => TYPE_UINT
	));

	print_delete_confirmation('fdwsocial', $vbulletin->GPC['fdwsocialid'], 'fdwsocial', 'remove', 'fdwsocial');
}

print_cp_footer();


// ################### SCRIPT FUNCTIONS ######################################
function build_fdwsocial()
{
	global $vbulletin;
	
	$fdwsociallist = array();
	$fdwsocials = $vbulletin->db->query_read("SELECT fdwsocialid, parsed FROM " . TABLE_PREFIX . "fdwsocial WHERE active = 1 ORDER BY displayorder");
	while ($fdwsocial = $vbulletin->db->fetch_array($fdwsocials))
	{
		$fdwsociallist["$fdwsocial[fdwsocialid]"] = $fdwsocial['parsed'];
	}
	build_datastore('fdwsocial', serialize($fdwsociallist), 1);
}
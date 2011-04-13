<?php
/*======================================================================*\
|| #################################################################### ||
|| # Forum Customization Manager - Foros del Web                      # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('ADMINCP_SCRIPT', 'fdwforum');

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array();
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminforums'))
{
	print_cp_no_permission();
}

// ############################# LOG ACTION ###############################
log_admin_action();

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

print_cp_header($vbphrase['fdwforum_title']);

if (empty($_REQUEST['do']))
{
	if (!empty($_REQUEST['fdwforumcustomid']))
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
			UPDATE " . TABLE_PREFIX . "fdwforumcustom
			SET active = IF(FIELD(fdwforumcustomid, " . implode(', ', array_keys($vbulletin->GPC['active'])) . ") > 0, 1, 0)
		");
	}
	else
	{
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "fdwforumcustom SET active = 0");
	}
	
	build_fdwforumcustom();
	
	$_REQUEST['do'] = 'modify';
}

// #############################################################################

if ($_POST['do'] == 'remove')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'fdwforumcustomid' => TYPE_UINT
	));
	
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "fdwforumcustom WHERE fdwforumcustomid = {$vbulletin->GPC['fdwforumcustomid']}");
	
	build_fdwforumcustom();
	
	define('CP_REDIRECT', 'fdwforum.php?do=modify');
	print_stop_message('deleted_fdwforum_successfully');
}

// #############################################################################

if ($_POST['do'] == 'update')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'fdwforumcustomid' => TYPE_UINT,
		'title' => TYPE_NOHTML,
		'styles' => TYPE_ARRAY_UINT,
		'styleid' => TYPE_UINT,
		'forumid' => TYPE_UINT,
		'active' => TYPE_BOOL
	));
	
	if (empty($vbulletin->GPC['title']))
	{
		print_stop_message('invalid_title_specified');
	}
	
	if ($vbulletin->GPC['fdwforumcustomid'])
	{
		$fdwforumcustomid =& $vbulletin->GPC['fdwforumcustomid'];
		
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "fdwforumcustom
			SET
				title = '" . $vbulletin->db->escape_string($vbulletin->GPC['title']) . "',
				styles = '" . implode(',', $vbulletin->GPC['styles']) . "',
				styleid = {$vbulletin->GPC['styleid']},
				forumid = {$vbulletin->GPC['forumid']},
				active = {$vbulletin->GPC['active']}
			WHERE fdwforumcustomid = $fdwforumcustomid
		");
	}
	else
	{
		$vbulletin->db->query_write("
			INSERT INTO " . TABLE_PREFIX . "fdwforumcustom
				(title, styles, styleid, forumid, active)
			VALUES (
				'" . $vbulletin->db->escape_string($vbulletin->GPC['title']) . "',
				'" . implode(',', $vbulletin->GPC['styles']) . "',
				{$vbulletin->GPC['forumid']},
				{$vbulletin->GPC['styleid']},
				{$vbulletin->GPC['active']}
			)
		");
	}
	
	build_fdwforumcustom();
	
	define('CP_REDIRECT', 'fdwforum.php?do=modify');
	print_stop_message('saved_x_successfully', $vbulletin->GPC['title']);
}

// #############################################################################

if ($_REQUEST['do'] == 'modify')
{
	print_form_header('fdwforum', 'updateactive');
	
	print_table_header($vbphrase['fdwforum_manager'], 4);
	print_cells_row(
		array(
			$vbphrase['title'],
			$vbphrase['active'],
			$vbphrase['controls']
		),
		true
	);
	
	$fdwforumcustoms = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "fdwforumcustom");
	
	while ($fdwforumcustom = $vbulletin->db->fetch_array($fdwforumcustoms))
	{
		print_cells_row(array(
			$fdwforumcustom['title'],
			"<input type=\"checkbox\" name=\"active[$fdwforumcustom[fdwforumcustomid]]\" value=\"1\"" . ($fdwforumcustom['active'] ? ' checked="checked"' : '') . " />",
			construct_link_code($vbphrase['edit'], "fdwforum.php?" . $vbulletin->session->vars['sessionurl'] . "do=edit&amp;fdwforumcustomid=$fdwforumcustom[fdwforumcustomid]") .
			construct_link_code($vbphrase['delete'], "fdwforum.php?" . $vbulletin->session->vars['sessionurl'] . "do=delete&amp;fdwforumcustomid=$fdwforumcustom[fdwforumcustomid]")
		));
	}
	
	print_submit_row($vbphrase['fdwforum_save_status'], false, 5);
	echo '<p align="center">' . construct_link_code($vbphrase['add_new_fdwforum'], "fdwforum.php?" . $vbulletin->session->vars['sessionurl'] . "do=add") . '</p>';
}

// #############################################################################

if ($_REQUEST['do'] == 'add' OR $_REQUEST['do'] == 'edit')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'fdwforumcustomid' => TYPE_UINT
	));
	
	$table_title = $vbphrase['add'];
	
	$fdwforumcustom = array(
		'fdwforumcustomid' => 0,
		'active' => true
	);
	
	if ($vbulletin->GPC['fdwforumcustomid'])
	{
		$fdwforumcustom = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "fdwforumcustom WHERE fdwforumcustomid = {$vbulletin->GPC['fdwforumcustomid']}");
		$table_title = $vbphrase['fdwforum_edit'] . " <span class=\"normal\">$fdwforumcustom[title]</span>";
	}
	
	print_form_header('fdwforum', 'update');
	construct_hidden_code('fdwforumcustomid', $vbulletin->GPC['fdwforumcustomid']);

	print_table_header($table_title);

	print_input_row($vbphrase['title'], 'title', $fdwforumcustom['title'], 0, 60);
	print_select_row($vbphrase['forum'], 'forumid', construct_forum_chooser_options(), $fdwforumcustom['forumid']);
	
	// Style selector
	require_once(DIR . '/includes/adminfunctions_template.php');
	cache_styles();
	$style_options = array();
	foreach($GLOBALS['stylecache'] AS $style)
	{
		$style_options["$style[styleid]"] = construct_depth_mark($style['depth'], '--') . ' ' . $style['title'];
	}
	print_select_row($vbphrase['style'], 'styles[]', $style_options, explode(',', $fdwforumcustom['styles']), false, 5, true);
	print_select_row($vbphrase['style'], 'styleid', $style_options, $fdwforumcustom['styleid']);
	
	print_yes_no_row($vbphrase['active'], 'active', $fdwforumcustom['active']);
	
	print_submit_row();
}

// #############################################################################

if ($_REQUEST['do'] == 'delete')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'fdwforumcustomid' => TYPE_UINT
	));

	print_delete_confirmation('fdwforumcustom', $vbulletin->GPC['fdwforumcustomid'], 'fdwforum', 'remove', 'fdwforumcustom');
}

print_cp_footer();


// ################### SCRIPT FUNCTIONS ######################################
function build_fdwforumcustom()
{
	global $vbulletin;
	
	$fdwforumcustomlist = array();
	$fdwforumcustoms = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "fdwforumcustom WHERE active = 1");
	while ($fdwforumcustom = $vbulletin->db->fetch_array($fdwforumcustoms))
	{
		$fdwforumcustomlist["$fdwforumcustom[forumid]"] = array(
			'styles' => explode(',', $fdwforumcustom['styles']),
			'styleid' => $fdwforumcustom['styleid']
		);
	}
	build_datastore('fdwforumcustom', serialize($fdwforumcustomlist), 1);
}
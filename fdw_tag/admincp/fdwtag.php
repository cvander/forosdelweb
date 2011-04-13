<?php
/*======================================================================*\
|| #################################################################### ||
|| # Recomended threads manager - Foros del Web                       # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('ADMINCP_SCRIPT', 'fdwtag');

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('fdwtagtool', 'cprofilefield');
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_fdwtag.php');

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

print_cp_header($vbphrase['fdwtagtitle']);

if (empty($_REQUEST['do']))
{
	if (!empty($_REQUEST['fdwtagid']))
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
			UPDATE " . TABLE_PREFIX . "fdwtag
			SET active = IF(FIELD(fdwtagid, " . implode(', ', array_keys($vbulletin->GPC['active'])) . ") > 0, 1, 0)
		");
	}
	else
	{
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "fdwtag SET active = 0");
	}
	
	build_fdwtags();
	
	$_REQUEST['do'] = 'modify';
}

// #############################################################################

if ($_POST['do'] == 'remove')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'fdwtagid' => TYPE_UINT
	));
	
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "fdwtag WHERE fdwtagid = {$vbulletin->GPC['fdwtagid']}");
	
	build_fdwtags();
	
	define('CP_REDIRECT', 'fdwtag.php?do=modify');
	print_stop_message('deleted_recomendation_successfully');
}

// #############################################################################

if ($_POST['do'] == 'update')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'fdwtagid' => TYPE_UINT,
		'title' => TYPE_NOHTML,
		'type' => TYPE_STR,
		'rec_popular' => TYPE_STR,
		'rec_tag' => TYPE_STR,
		'rec_field' => TYPE_STR,
		'boxtitle' => TYPE_NOHTML,
		'groupids' => TYPE_ARRAY_UINT,
		'forumid' => TYPE_UINT,
		'master' => TYPE_UINT,
		'sort' => TYPE_STR,
		'maxthreads' => TYPE_UINT,
		'unreaded' => TYPE_BOOL,
		'days' => TYPE_UINT,
		'active' => TYPE_BOOL
	));
	
	if (empty($vbulletin->GPC['title']))
	{
		print_stop_message('invalid_title_specified');
	}
	
	if (!in_array($vbulletin->GPC['type'], array('popular', 'tag', 'field')))
	{
		print_stop_message('invalid_x_specified', $vbphrase['type']);
	}
	
	if (!in_array($vbulletin->GPC['sort'], array('dateline', 'lastpost', 'fdwrank')))
	{
		$vbulletin->GPC['sort'] = 'lastpost';
	}
	
	$vbulletin->GPC['recomendation'] = $vbulletin->GPC["rec_{$vbulletin->GPC['type']}"];
	
	if ($vbulletin->GPC['type'] != 'popular' AND empty($vbulletin->GPC['recomendation']))
	{
		print_stop_message('invalid_x_specified', $vbphrase['recomendation']);
	}
	
	if (!$vbulletin->forumcache["{$vbulletin->GPC['forumid']}"])
	{
		print_stop_message('invalid_x_specified', $vbphrase['forum']);
	}
	
	if ($vbulletin->GPC['fdwtagid'])
	{
		$fdwtagid =& $vbulletin->GPC['fdwtagid'];
		
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "fdwtag
			SET
				title = '" . $vbulletin->db->escape_string($vbulletin->GPC['title']) . "',
				type = '{$vbulletin->GPC['type']}',
				recomendation = '" . $vbulletin->db->escape_string($vbulletin->GPC['recomendation']) . "',
				boxtitle = '" . $vbulletin->db->escape_string($vbulletin->GPC['boxtitle']) . "',
				groupids = '" . implode(',', $vbulletin->GPC['groupids']) . "',
				forumid = {$vbulletin->GPC['forumid']},
				master = {$vbulletin->GPC['master']},
				sort = '{$vbulletin->GPC['sort']}',
				unreaded = {$vbulletin->GPC['unreaded']},
				maxthreads = {$vbulletin->GPC['maxthreads']},
				days = {$vbulletin->GPC['days']},
				active = {$vbulletin->GPC['active']}
			WHERE fdwtagid = $fdwtagid
		");
	}
	else
	{
		$vbulletin->db->query_write("
			INSERT INTO " . TABLE_PREFIX . "fdwtag
				(title, type, recomendation, boxtitle, groupids, forumid, master, sort, unreaded, maxthreads, days, active)
			VALUES (
				'" . $vbulletin->db->escape_string($vbulletin->GPC['title']) . "',
				'{$vbulletin->GPC['type']}',
				'" . $vbulletin->db->escape_string($vbulletin->GPC['recomendation']) . "',
				'" . $vbulletin->db->escape_string($vbulletin->GPC['boxtitle']) . "',
				'" . implode(',', $vbulletin->GPC['groupids']) . "',
				{$vbulletin->GPC['forumid']},
				{$vbulletin->GPC['master']},
				'{$vbulletin->GPC['sort']}',
				{$vbulletin->GPC['unreaded']},
				{$vbulletin->GPC['maxthreads']},
				{$vbulletin->GPC['days']},
				{$vbulletin->GPC['active']}
			)
		");
	}
	
	build_fdwtags();
	
	define('CP_REDIRECT', 'fdwtag.php?do=modify');
	print_stop_message('saved_x_successfully', $vbulletin->GPC['title']);
}

// #############################################################################

if ($_REQUEST['do'] == 'modify')
{
	print_form_header('fdwtag', 'updateactive');
	
	print_table_header($vbphrase['fdwtagmanager'], 4);
	print_cells_row(
		array(
			$vbphrase['title'],
			$vbphrase['usergroups'],
			$vbphrase['active'],
			$vbphrase['controls']
		),
		true
	);
	
	$fdwtags = $vbulletin->db->query_read("SELECT * FROM " . TABLE_PREFIX . "fdwtag");
	
	while ($fdwtag = $vbulletin->db->fetch_array($fdwtags))
	{
		print_cells_row(array(
			$fdwtag['title'],
			$fdwtag['groupids'],
			"<input type=\"checkbox\" name=\"active[$fdwtag[fdwtagid]]\" value=\"1\"" . ($fdwtag['active'] ? ' checked="checked"' : '') . " />",
			construct_link_code($vbphrase['edit'], "fdwtag.php?" . $vbulletin->session->vars['sessionurl'] . "do=edit&amp;fdwtagid=$fdwtag[fdwtagid]") .
			construct_link_code($vbphrase['delete'], "fdwtag.php?" . $vbulletin->session->vars['sessionurl'] . "do=delete&amp;fdwtagid=$fdwtag[fdwtagid]")
		));
	}
	
	print_submit_row($vbphrase['save_active_status'], false, 5);
	echo '<p align="center">' . construct_link_code($vbphrase['add_new'], "fdwtag.php?" . $vbulletin->session->vars['sessionurl'] . "do=add") . '</p>';
}

// #############################################################################

if ($_REQUEST['do'] == 'add' OR $_REQUEST['do'] == 'edit')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'fdwtagid' => TYPE_UINT
	));
	
	$table_title = $vbphrase['add_new'];
	
	$fdwtag = array(
		'fdwtagid' => 0,
		'active' => true,
		'type' => 'popular',
		'maxthreads' => 0,
		'days' => 0
	);
	
	if ($vbulletin->GPC['fdwtagid'])
	{
		$fdwtag = $vbulletin->db->query_first("SELECT * FROM " . TABLE_PREFIX . "fdwtag WHERE fdwtagid = {$vbulletin->GPC['fdwtagid']}");
		$table_title = $vbphrase['edit_fdwtag'] . " <span class=\"normal\">$fdwtag[title]</span>";
	}
	
	print_form_header('fdwtag', 'update');
	construct_hidden_code('fdwtagid', $vbulletin->GPC['fdwtagid']);

	print_table_header($table_title);

	print_input_row($vbphrase['title'] . '<dfn>' . $vbphrase['title_description'] . '</dfn>', 'title', $fdwtag['title'], 0, 60);
	print_select_row($vbphrase['type'], 'type', array('popular' => $vbphrase['rec_popular'], 'tag' => $vbphrase['rec_tag'], 'field' => $vbphrase['rec_field']), $fdwtag['type']);
	print_input_row($vbphrase['rec_tag'] . '<dfn>' . $vbphrase['rec_tag_desc'] . '</dfn>', 'rec_tag', ($fdwtag['type'] == 'tag' ? $fdwtag['recomendation'] : ''), 0, 60);
	
	// Construct profile fields list
	$fieldslist = array();
	$profilefields = $vbulletin->db->query_read("SELECT profilefieldid FROM " . TABLE_PREFIX . "profilefield");
	while ($profilefield = $vbulletin->db->fetch_array($profilefields))
	{
		$fieldslist["field$profilefield[profilefieldid]"] = htmlspecialchars_uni($vbphrase['field' . $profilefield['profilefieldid'] . '_title']);
	}
	print_select_row($vbphrase['rec_field'] . '<dfn>' . $vbphrase['rec_field_desc'] . '</dfn>', 'rec_field', $fieldslist, $fdwtag['recomendation']);
	
	print_input_row($vbphrase['boxtitle'] . '<dfn>' . $vbphrase['boxtitle_desc'] . '</dfn>', 'boxtitle', $fdwtag['boxtitle'], 0, 60);
	
	// Usergroup list
	$usergrouplist = array();
	foreach ($vbulletin->usergroupcache AS $usergroup)
	{
		$usergrouplist["$usergroup[usergroupid]"] = $usergroup['title'];
	}
	print_select_row($vbphrase['usergroups'], 'groupids[]', $usergrouplist , explode(',', $fdwtag['groupids']), false, 9, true);
	
	print_select_row($vbphrase['forum'], 'forumid', construct_forum_chooser_options(), $fdwtag['forumid']);
	
	// Recomendations list
	$fdwtagslist = array();
	$fdwtags = $vbulletin->db->query_read("SELECT fdwtagid, title FROM " . TABLE_PREFIX . "fdwtag WHERE fdwtagid <> $fdwtag[fdwtagid]");
	while ($cfdwtag = $vbulletin->db->fetch_array($fdwtags))
	{
		$fdwtaglist["$cfdwtag[fdwtagid]"] = $cfdwtag['title'];
	}
	if (count($fdwtagslist))
	{
		print_select_row($vbphrase['master'] . '<dfn>' . $vbphrase['master_desc'] . '</dfn>', 'master', $fdwtagslist, $fdwtag['master']);
	}
	
	$sortarray = array('lastpost' => $vbphrase['sortby_lastpost'], 'dateline' => $vbphrase['sortby_dateline'], 'fdwrank' => $vbphrase['sortby_rank']);
	print_select_row($vbphrase['sortby'] . '<dfn>' . $vbphrase['sortby_desc'] . '</dfn>', 'sort', $sortarray, $fdwtag['sort']);
	
	print_input_row($vbphrase['maxthreads'] . '<dfn>' . $vbphrase['maxthreads_desc'] . '</dfn>', 'maxthreads', $fdwtag['maxthreads'], 0, 20);
	print_input_row($vbphrase['days'] . '<dfn>' . $vbphrase['days_desc'] . '</dfn>', 'days', $fdwtag['days'], 0, 20);
	print_yes_no_row($vbphrase['unreaded'] . '<dfn>' . $vbphrase['unreaded_desc'] . '</dfn>', 'unreaded', $fdwtag['unreaded']);
	print_yes_no_row($vbphrase['active'], 'active', $fdwtag['active']);
	
	print_fdwtag_script();
	print_submit_row();
}

// #############################################################################

if ($_REQUEST['do'] == 'delete')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'fdwtagid' => TYPE_UINT
	));

	print_delete_confirmation('fdwtag', $vbulletin->GPC['fdwtagid'], 'fdwtag', 'remove', 'fdwtag');
}

print_cp_footer();
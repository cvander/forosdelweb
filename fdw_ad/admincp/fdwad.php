<?php
/*======================================================================*\
|| #################################################################### ||
|| # Ad Manager - Foros del Web                                       # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('ADMINCP_SCRIPT', 'fdwad');

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('fdwad', 'style');
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_fdwad.php');

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

print_cp_header($vbphrase['fdwadtitle']);

if (empty($_REQUEST['do']))
{
	if (!empty($_REQUEST['adid']))
	{
		$_REQUEST['do'] = 'edit';
	}
	else
	{
		$_REQUEST['do'] = 'modify';
	}
}

if (in_array($_REQUEST['do'], array('update', 'add', 'edit')))
{
	$criteriatypes = criteria_types();
	$ad_cache = cache_ads();
}

// #############################################################################

if ($_POST['do'] == 'updateactive')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'active' => TYPE_ARRAY_UINT,
	));
	
	if (count($vbulletin->GPC['active']) > 0)
	{
		$db->query_write("
			UPDATE " . TABLE_PREFIX . "fdwad
			SET active = IF(FIELD(adid, " . implode(', ', array_keys($vbulletin->GPC['active'])) . ") > 0, 1, 0)
		");
	}
	else
	{
		$db->query_write("UPDATE " . TABLE_PREFIX . "fdwad SET active = 0");
	}
	
	$criteriatypes = criteria_types();
	$ad_cache = cache_ads();
	rebuild_ad_templates();
	
	$_REQUEST['do'] = 'modify';
}

// #############################################################################

if ($_POST['do'] == 'remove')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'adid' => TYPE_UINT
	));
	
	$db->query_write("
		DELETE FROM " . TABLE_PREFIX . "fdwadcriteria
		WHERE adid = {$vbulletin->GPC['adid']}
	");
	
	$db->query_write("DELETE FROM " . TABLE_PREFIX . "fdwad WHERE adid = {$vbulletin->GPC['adid']}");
	
	$criteriatypes = criteria_types();
	$ad_cache = cache_ads();
	rebuild_ad_templates();
	
	define('CP_REDIRECT', 'fdwad.php?do=modify');
	print_stop_message('deleted_ad_successfully');
}

// #############################################################################

if ($_POST['do'] == 'update')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'adid' => TYPE_UINT,
		'title' => TYPE_NOHTML,
		'location' => TYPE_STR,
		'display' => TYPE_UINT,
		'active' => TYPE_BOOL,
		'code' => TYPE_STR,
		'wrapper' => TYPE_BOOL,
		'criteria' => TYPE_ARRAY,
		'activecriteria' => TYPE_ARRAY
	));
	
	if (empty($vbulletin->GPC['title']))
	{
		print_stop_message('invalid_title_specified');
	}
	
	if (empty($vbulletin->GPC['code']))
	{
		print_stop_message('invalid_x_specified', $vbphrase['code']);
	}
	
	if (!in_array($vbulletin->GPC['location'], array('right', 'left')))
	{
		print_stop_message('invalid_x_specified', $vbphrase['location']);
	}
	
	$criteriavalues = array();
	foreach($vbulletin->GPC['criteria'] AS $criterianame => $values)
	{
		if (!isset($criteriatypes["$criterianame"]) OR !isset($vbulletin->GPC['activecriteria']["$criterianame"]))
		{
			continue;
		}
		
		$criteria =& $criteriatypes["$criterianame"];
		if (!criteria_validate($criteria, $values))
		{
			print_stop_message("{$criterianame}_error");
		}
		
		$criteriavalues["$criteria[typeid]"] = serialize($values);
	}
	
	$errors = check_template_errors(compile_template($vbulletin->GPC['code']));
	if (!empty($errors))
	{
		print_stop_message('fdwad_invalid_code', $errors);
	}
	
	if ($vbulletin->GPC['adid'])
	{
		$adid =& $vbulletin->GPC['adid'];
		
		$db->query_write("
			UPDATE " . TABLE_PREFIX . "fdwad
			SET
				title = '" . $db->escape_string($vbulletin->GPC['title']) . "',
				code = '" . $db->escape_string($vbulletin->GPC['code']) . "',
				location = '{$vbulletin->GPC['location']}',
				wrapper = {$vbulletin->GPC['wrapper']},
				active = {$vbulletin->GPC['active']},
				display = {$vbulletin->GPC['display']}
			WHERE adid = $adid
		");
		
		$db->query_write("
			DELETE FROM " . TABLE_PREFIX . "fdwadcriteria
			WHERE adid = $adid
		");
	}
	else
	{
		$db->query_write("
			INSERT INTO " . TABLE_PREFIX . "fdwad
				(title, code, location, wrapper, active, display)
			VALUES (
				'" . $db->escape_string($vbulletin->GPC['title']) . "',
				'" . $db->escape_string($vbulletin->GPC['code']) . "',
				'{$vbulletin->GPC['location']}',
				{$vbulletin->GPC['wrapper']},
				{$vbulletin->GPC['active']},
				{$vbulletin->GPC['display']}
			)
		");
		
		$adid = $db->insert_id();
	}
	
	$ad_cache["$adid"] = array(
		'adid' => $adid,
		'title' => $vbulletin->GPC['title'],
		'code' => $vbulletin->GPC['code'],
		'location' => $vbulletin->GPC['location'],
		'wrapper' => $vbulletin->GPC['wrapper'],
		'active' => $vbulletin->GPC['active'],
		'display' => $vbulletin->GPC['display']
	);
	
	if (count($criteriavalues))
	{
		$criteria_sql = array();
		foreach ($criteriavalues AS $typeid => $criteriavalue)
		{
			$criteria_sql[] = "($adid, $typeid, '" . $db->escape_string($criteriavalue) . "')";
		}
		
		$db->query_write("
			INSERT INTO " . TABLE_PREFIX . "fdwadcriteria
				(adid, typeid, value)
			VALUES " . implode(', ', $criteria_sql)
		);
	}
	
	rebuild_ad_templates();
	print_rebuild_style(-1, '', 0, 0, 0, 0);
	
	define('CP_REDIRECT', 'fdwad.php?do=modify');
	print_stop_message('saved_x_successfully', $vbulletin->GPC['title']);
}

// #############################################################################

if ($_REQUEST['do'] == 'modify')
{
	print_form_header('fdwad', 'updateactive');
	
	print_table_header($vbphrase['admanager'], 5);
	print_cells_row(
		array(
			$vbphrase['title'],
			$vbphrase['location'],
			$vbphrase['order'],
			$vbphrase['active'],
			$vbphrase['controls']
		),
		true
	);
	
	$ads = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "fdwad ORDER BY display");
	
	while ($ad = $db->fetch_array($ads))
	{
		$title = htmlspecialchars_uni($ad['title']);
		print_cells_row(array(
			$title,
			$ad['location'],
			$ad['display'],
			"<input type=\"checkbox\" name=\"active[$ad[adid]]\" value=\"1\"" . ($ad['active'] ? ' checked="checked"' : '') . " />",
			construct_link_code($vbphrase['edit'], "fdwad.php?" . $vbulletin->session->vars['sessionurl'] . "do=edit&amp;adid=$ad[adid]") .
			construct_link_code($vbphrase['delete'], "fdwad.php?" . $vbulletin->session->vars['sessionurl'] . "do=delete&amp;adid=$ad[adid]")
		));
	}
	
	print_submit_row($vbphrase['save_active_status'], false, 5);
	echo '<p align="center">' . construct_link_code($vbphrase['add_new'], "fdwad.php?" . $vbulletin->session->vars['sessionurl'] . "do=add") . '</p>';
}

// #############################################################################

if ($_REQUEST['do'] == 'add' OR $_REQUEST['do'] == 'edit')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'adid' => TYPE_UINT
	));
	
	$table_title = $vbphrase['add_new'];
	$criteriavalues = array();
	
	$ad = array(
		'active' => true,
		'wrapper' => true,
		'location' => 'right'
	);
	
	if ($vbulletin->GPC['adid'])
	{
		$ad = $ad_cache[$vbulletin->GPC['adid']];
		
		$criteriavalues = criteria_values($vbulletin->GPC['adid']);
	}
	
	if (!$ad['display'])
	{
		$maxorder = $db->query_first("SELECT MAX(display) AS maxorder FROM " . TABLE_PREFIX . "fdwad");
		$ad['display'] = $maxorder['maxorder'] + 10;
	}
	else
	{
		$table_title = $vbphrase['edit_ad'] . " <span class=\"normal\">$ad[title]</span>";
	}
	
	print_form_header('fdwad', 'update');
	construct_hidden_code('adid', $vbulletin->GPC['adid']);

	print_table_header($table_title);

	print_input_row($vbphrase['title'] . '<dfn>' . $vbphrase['ad_title_description'] . '</dfn>', 'title', $ad['title'], 0, 60);
	print_select_row($vbphrase['location'], 'location', array('left' => $vbphrase['left'], 'right' => $vbphrase['right']), $ad['location']);
	print_textarea_row($vbphrase['code'] . '<dfn>' . $vbphrase['ad_code_description'] . '</dfn>', 'code', $ad['code'], 8, 60, true, false);

	print_input_row($vbphrase['display_order'], 'display', $ad['display'], 0, 10);
	print_yes_no_row($vbphrase['wrapper'] . '<dfn>' . $vbphrase['ad_wrapper_description'] . '</dfn>', 'wrapper', $ad['wrapper']);
	print_yes_no_row($vbphrase['active'], 'active', $ad['active']);
	
	print_description_row('<strong>' . $vbphrase['display_if_'] . '</strong>', false, 2, 'tcat', '', 'criteria');
	
	foreach($criteriatypes AS $criterianame => $criteria)
	{
		if (isset($criteriavalues[$criterianame]))
		{
			print_criteria($criteria, $criteriavalues[$criterianame]);
		}
		else
		{
			print_criteria($criteria, null);
		}
	}
	
	print_submit_row();
}

// #############################################################################

if ($_REQUEST['do'] == 'delete')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'adid' => TYPE_UINT
	));

	print_delete_confirmation('fdwad', $vbulletin->GPC['adid'], 'fdwad', 'remove', 'ad');
}

print_cp_footer();
<?php
/*======================================================================*\
|| #################################################################### ||
|| # Ad Manager - Foros del Web                                       # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('cphome', 'fdwstats');
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');

// ############################# LOG ACTION ###############################
log_admin_action();

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

print_cp_header($vbphrase['fdw_stats_cpgroup']);
print_form_header('fdwstats', 'stats');
construct_hidden_code('do', 'stats');
print_table_header($vbphrase['fdw_stats_cpgroup'], 4);

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'index';
}

if ($_REQUEST['do'] == 'index')
{
	print_time_row($vbphrase['start_date'], 'startdate', strtotime('-1 month', TIMENOW), false);
	print_time_row($vbphrase['end_date'], 'enddate', TIMENOW, false);
	print_select_row($vbphrase['show_by'], 'showby', array($vbphrase['user'], $vbphrase['usergroup'], $vbphrase['date']));
	print_select_row($vbphrase['metric_by'], 'metricby', array($vbphrase['forumvisits'], $vbphrase['threadvisits']));
	print_table_footer(2, '<input type="submit" value="' . $vbphrase['view'] . '" class="button" />');
}

if ($_POST['do'] == 'stats')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'startdate' => TYPE_UNIXTIME,
		'enddate' => TYPE_UNIXTIME,
		'showby' => TYPE_UINT,
		'metricby' => TYPE_UINT,
		'limitstart' => TYPE_UINT,
		'limitnumber' => TYPE_UINT
	));
	
	$vbulletin->GPC['limitnumber'] = 25;
	$metricfield = ($vbulletin->GPC['metricby']) ? 'threadviews' : 'views';
	
	$tablename = 'fdwstats' . TIMENOW;
	
	switch ($vbulletin->GPC['showby'])
	{
		case 0:
			$db->query_write("
				CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "$tablename (
					userid INT UNSIGNED NOT NULL,
					forumid INT UNSIGNED NOT NULL,
					views INT UNSIGNED NOT NULL,
					threadviews INT UNSIGNED NOT NULL,
					UNIQUE (userid, forumid)
				) ENGINE = MEMORY
			");
			$db->query_write("
				INSERT INTO " . TABLE_PREFIX . "$tablename
					(userid, forumid, views, threadviews)
				SELECT userid, forumid, SUM(views), SUM(threadviews)
				FROM " . TABLE_PREFIX . "fdwuserstats
				WHERE stattime BETWEEN FROM_UNIXTIME(" . $vbulletin->GPC['startdate'] . ") AND FROM_UNIXTIME(" . $vbulletin->GPC['enddate'] . ")
				GROUP BY userid, forumid
			");
			$countresult = $db->query_first("SELECT COUNT(DISTINCT userid) AS datacount FROM " . TABLE_PREFIX . "$tablename");
			$result = $db->query_read("
				SELECT user.username AS datafield, forum.title, fdwstats.views, fdwstats.threadviews
				FROM " . TABLE_PREFIX . "$tablename AS fdwstats
				INNER JOIN " . TABLE_PREFIX . "user AS user USING (userid)
				INNER JOIN " . TABLE_PREFIX . "forum AS forum USING (forumid)
				WHERE fdwstats.$metricfield = (
					SELECT MAX($metricfield)
					FROM " . TABLE_PREFIX . "$tablename AS fdwstats2
					WHERE fdwstats2.userid = fdwstats.userid
				)
				LIMIT {$vbulletin->GPC['limitstart']}, {$vbulletin->GPC['limitnumber']}
			");
			$phrase = $vbphrase['user'];
			break;
		case 1:
			$db->query_write("
				CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "$tablename (
					usergroupid INT UNSIGNED NOT NULL,
					forumid INT UNSIGNED NOT NULL,
					views INT UNSIGNED NOT NULL,
					threadviews INT UNSIGNED NOT NULL,
					UNIQUE (usergroupid, forumid)
				) ENGINE = MEMORY
			");
			$db->query_write("
				INSERT INTO " . TABLE_PREFIX . "$tablename
					(usergroupid, forumid, views, threadviews)
				SELECT user.usergroupid, forumid, SUM(views), SUM(threadviews)
				FROM " . TABLE_PREFIX . "fdwuserstats
				INNER JOIN " . TABLE_PREFIX . "user AS user USING (userid)
				WHERE stattime BETWEEN FROM_UNIXTIME(" . $vbulletin->GPC['startdate'] . ") AND FROM_UNIXTIME(" . $vbulletin->GPC['enddate'] . ")
				GROUP BY user.usergroupid, forumid
			");
			$countresult = $db->query_first("SELECT COUNT(DISTINCT usergroupid) AS datacount FROM " . TABLE_PREFIX . "$tablename");
			$result = $db->query_read("
				SELECT usergroup.title AS datafield, forum.title, fdwstats.views, fdwstats.threadviews
				FROM " . TABLE_PREFIX . "$tablename AS fdwstats
				INNER JOIN " . TABLE_PREFIX . "usergroup AS usergroup USING (usergroupid)
				INNER JOIN " . TABLE_PREFIX . "forum AS forum USING (forumid)
				WHERE fdwstats.$metricfield = (
					SELECT MAX($metricfield)
					FROM " . TABLE_PREFIX . "$tablename AS fdwstats2
					WHERE fdwstats2.usergroupid = fdwstats.usergroupid
				)
			");
			$phrase = $vbphrase['usergroup'];
			break;
		case 2:
			$db->query_write("
				CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "$tablename (
					stattime DATE NOT NULL,
					forumid INT UNSIGNED NOT NULL,
					views INT UNSIGNED NOT NULL,
					threadviews INT UNSIGNED NOT NULL,
					UNIQUE (stattime, forumid)
				) ENGINE = MEMORY
			");
			$db->query_write("
				INSERT INTO " . TABLE_PREFIX . "$tablename
					(stattime, forumid, views, threadviews)
				SELECT stattime, forumid, SUM(views), SUM(threadviews)
				FROM " . TABLE_PREFIX . "fdwuserstats
				WHERE stattime BETWEEN FROM_UNIXTIME(" . $vbulletin->GPC['startdate'] . ") AND FROM_UNIXTIME(" . $vbulletin->GPC['enddate'] . ")
				GROUP BY stattime, forumid
			");
			$countresult = $db->query_first("SELECT COUNT(DISTINCT stattime) AS datacount FROM " . TABLE_PREFIX . "$tablename");
			$result = $db->query_read("
				SELECT fdwstats.stattime AS datafield, forum.title, fdwstats.views, fdwstats.threadviews
				FROM " . TABLE_PREFIX . "$tablename AS fdwstats
				INNER JOIN " . TABLE_PREFIX . "forum AS forum USING (forumid)
				WHERE fdwstats.$metricfield = (
					SELECT MAX($metricfield)
					FROM " . TABLE_PREFIX . "$tablename AS fdwstats2
					WHERE fdwstats2.stattime = fdwstats.stattime
				)
				LIMIT {$vbulletin->GPC['limitstart']}, {$vbulletin->GPC['limitnumber']}
			");
			$phrase = $vbphrase['date'];
	}
	
	construct_hidden_code('startdate', $vbulletin->GPC['startdate']);
	construct_hidden_code('enddate', $vbulletin->GPC['enddate']);
	construct_hidden_code('showby', $vbulletin->GPC['showby']);
	construct_hidden_code('metricby', $vbulletin->GPC['metricby']);
	construct_hidden_code('limitnumber', $vbulletin->GPC['limitnumber']);
	
	print_cells_row(array(
		$phrase,
		$vbphrase['forummost'],
		$vbphrase['forumvisits'],
		$vbphrase['threadvisits']
	), true);
	
	while ($row = $db->fetch_array($result))
	{
		print_cells_row(array($row['datafield'], $row['title'], $row['views'], $row['threadviews']));
	}
	
	$db->free_result($result);
	$db->query_write("DROP TABLE IF EXISTS " . TABLE_PREFIX . "$tablename");
	
	$limitfinish = $vbulletin->GPC['limitstart'] + $vbulletin->GPC['limitnumber'];
	if ($vbulletin->GPC['limitstart'] == 0 AND $countresult['datafield'] > $vbulletin->GPC['limitnumber'])
	{
		construct_hidden_code('limitstart', $vbulletin->GPC['limitstart'] + $vbulletin->GPC['limitnumber'] + 1);
		print_submit_row($vbphrase['next_page'], 0, 4);
	}
	else if ($limitfinish < $countresult['datafield'])
	{
		construct_hidden_code('limitstart', $limitfinish + 1);
		print_submit_row($vbphrase['next_page'], 0, 4, $vbphrase['prev_page'], '', true);
	}
	else if ($vbulletin->GPC['limitstart'] > 0 AND $limitfinish >= $countresult['datafield'])
	{
		print_submit_row($vbphrase['first_page'], 0, 4, $vbphrase['prev_page'], '', true);
	}
	else
	{
		print_table_footer();
	}
}

print_cp_footer();
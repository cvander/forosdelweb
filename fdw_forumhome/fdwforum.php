<?php
/*======================================================================*\
|| #################################################################### ||
|| # Custom Forum Home - Foros del Web                                # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'fdwforum');
define('CSRF_PROTECTION', true);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('forumdisplay', 'inlinemod', 'prefix', 'cpoption');

// get special data templates from the datastore
$specialtemplates = array(
	'iconcache',
	'mailqueue',
	'prefixcache'
);

// pre-cache templates used by all actions
$globaltemplates = array(
	'FORUMDISPLAY_fdw',
	'threadbit',
	'threadbit_votefdw',
	'optgroup',
	'threadadmin_imod_menu_thread',
);

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_forumdisplay.php');
require_once(DIR . '/includes/functions_prefix.php');
require_once(DIR . '/includes/functions_bigthree.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

if (
	!$vbulletin->options['fdw_forumcustom_enable']
	OR !$vbulletin->userinfo['userid']
	OR !is_member_of($vbulletin->userinfo, unserialize($vbulletin->options['fdw_forumcustom_groups']))
)
{
	print_no_permission();
}

cache_ordered_forums(1, 1);

// Fetch user preferences
$fdwordercache = array();
if ($fdwsessioncache = $vbulletin->db->query_first("
	SELECT * FROM " . TABLE_PREFIX . "fdwsession
	WHERE sessionhash = '" . $vbulletin->db->escape_string($vbulletin->session->vars['dbsessionhash']) . "'"
))
{
	if ($fdwsessioncache['userid'] == $vbulletin->userinfo['userid'])
	{
		$fdwordercache = unserialize($fdwsessioncache['fdwforum']);
	}
	unset($fdwsessioncache);
}
if (!$fdwordercache)
{
	$fdwlastcache = $vbulletin->db->query_first("
		SELECT 1 FROM " . TABLE_PREFIX . "fdwuser
		WHERE userid = {$vbulletin->userinfo['userid']} AND forumcache > " . (TIMENOW - (60 * 60 * $vbulletin->options['fdw_forumhome_cache'])) . "
	");
	require_once(DIR . '/includes/functions_fdwforum.php');
	if (!$fdwlastcache)
	{
		build_fdwforum_stats($vbulletin->userinfo['userid'], null, $vbulletin->options['fdw_forumhome_lastdays']);
	}
	$fdwordercache = build_fdwforum_usercache($vbulletin->session->vars['dbsessionhash'], $vbulletin->userinfo['userid']);
}
$forumids =& $fdwordercache['most'];

// verify forum permissions
$totalrelevance = 0;
foreach ($forumids AS $forumid => $foruminfo)
{
	$fperms =& $vbulletin->userinfo['forumpermissions']["$forumid"];
	if (
		!($fperms & $vbulletin->bf_ugp_forumpermissions['canview'])
		OR !verify_forum_password($forumid, $forum['password'], false)
		OR !$vbulletin->forumcache["$forumid"]['options'] & $vbulletin->bf_misc_forumoptions['cancontainthreads']
	)
	{
		unset($forumids["$forumid"]);
	}
	else
	{
		$totalrelevance += $foruminfo['relevance'];
	}
}

if (empty($forumids))
{
	print_no_permission();
}

//fetch display options
$vbulletin->options['fdw_forumcustom_display'] = unserialize($vbulletin->options['fdw_forumcustom_display']);

$displaymode = $vbulletin->input->clean_gpc('r', 'mode', TYPE_UINT);
$displayfilter = $vbulletin->input->clean_gpc('r', 'order', TYPE_UINT);
$perpage = $vbulletin->input->clean_gpc('r', 'perpage', TYPE_UINT);

if (!$displaymode OR $displaymode > 3)
{
	$displaymode = $vbulletin->options['fdw_forumcustom_display']['mode'];
}
$modeopt = array($displaymode => ' selected="selected"');
if (!$displayfilter OR $displayfilter > 2)
{
	$displayfilter = $vbulletin->options['fdw_forumcustom_display']['show'];
}
$showopt = array($displayfilter => ' selected="selected"');
if ($perpage == 0 OR $perpage > $vbulletin->options['fdw_forumcustom_maxpp'])
{
	$perpage = $vbulletin->options['fdw_forumcustom_display']['pp'];
}
$ppopt = array($perpage => ' selected="selected"');


// prepare query
$previewforums = $voteforums = $fdwvoteforums = '';
$forumquery = $forumcanmoderate = $lastread = array();
$globalignore = ($Coventry = fetch_coventry('string', true)) ? "AND postuserid NOT IN ($Coventry)" : '';
$first = true;
$sortfield = 'lastpost DESC';

if (!$vbulletin->options['threadmarking'] AND $displayfilter == 2)
{
	// cookie-based mark thread as readed
	if (!isset($bb_cache_thread_lastview))
	{
		$cookie =& $vbulletin->input->clean_gpc('c', COOKIE_PREFIX . 'thread_lastview', TYPE_STR);
		if (!empty($cookie))
		{
			$bb_cache_thread_lastview = @unserialize(convert_bbarray_cookie($cookie));
		}
	}
	$threadsmarked = $threadsmarkeddate = '';
	if (isset($bb_cache_thread_lastview))
	{
		$threadsmarked = implode(',', array_map('intval', array_keys($bb_cache_thread_lastview)));
		$threadsmarkeddate = implode(',', array_map('intval', array_values($bb_cache_thread_lastview)));
	}
}

if ($displaymode == 2 AND $vbulletin->options['fdw_forumcustom_cache']) // relevant threads
{
	$forumthreads = array();
	
	$threadscache = $vbulletin->db->query_read("
		SELECT forumid, threadids FROM " . TABLE_PREFIX . "fdwthread
		WHERE forumid IN (" . implode(',', array_keys($forumids)) . ") AND forumcache > " . (TIMENOW - (60 * 60 * $vbulletin->options['fdw_forumcustom_cache'])) . "
	");
	
	while ($threadcache = $vbulletin->db->fetch_array($threadscache))
	{
		$forumthreads["$threadcache[forumid]"] = $threadcache['threadids'];
	}
}

foreach ($forumids AS $forumid => $foruminfo)
{
	if ($vbulletin->options['threadmarking'])
	{
		$lastread["$forumid"] = max($vbulletin->forumcache["$forumid"]['forumread'], (TIMENOW - ($vbulletin->options['markinglimit'] * 86400)));
	}
	else
	{
		$forumview = intval(fetch_bbarray_cookie('forum_view', $forumid));

		//use which one produces the highest value, most likely cookie
		$lastread["$forumid"] = ($forumview > $vbulletin->userinfo['lastvisit'] ? $forumview : $vbulletin->userinfo['lastvisit']);
	}

	$fperms =& $vbulletin->userinfo['forumpermissions']["$forumid"];

	if (can_moderate($forumid, 'canmanagethreads'))
	{
		$forumcanmoderate["$forumid"] = $forumid;
		$show['movethread'] = true;
	}

	if (can_moderate($forumid, 'candeleteposts') OR can_moderate($forumid, 'canremoveposts'))
	{
		$forumcanmoderate["$forumid"] = $forumid;
		$show['deletethread'] = true;
	}

	if (can_moderate($forumid, 'canmoderateposts'))
	{
		$forumcanmoderate["$forumid"] = $forumid;
		$show['approvethread'] = true;
	}

	if (can_moderate($forumid, 'canopenclose'))
	{
		$forumcanmoderate["$forumid"] = $forumid;
		$show['openthread'] = true;
	}

	if ($vbulletin->forumcache["$forumid"]['options'] & $vbulletin->bf_misc_forumoptions['allowicons'])
	{
		$show['threadicons'] = true;
	}
	
	if ($vbulletin->options['threadpreview'] > 0 AND ($fperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']))
	{
		$previewforums .= ",$forumid";
	}
	if ($vbulletin->forumcache["$forumid"]['options'] & $vbulletin->bf_misc_forumoptions['allowratings'])
	{
		$show['threadratings'] = true;
		$voteforums .= ",$forumid";
	}
	if ($vbulletin->options['fdw_forumcustom_vote'])
	{
		if ($vbulletin->options['fdw_vote_enable'] AND $vbulletin->forumcache["$forumid"]['votefdw'])
		{
			$show['threadvotefdw'] = true;
			$fdwvoteforums .= ",$forumid";
		}
	}
	
	$limitothers = '';
	if (!($fperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']))
	{
		$limitothers = "AND postuserid = " . $vbulletin->userinfo['userid'] . " AND " . $vbulletin->userinfo['userid'] . " <> 0";
	}
	
	$displaycondition = $markingcondition = $markingjoin = '';
	if ($displaymode == 3)
	{
		$displaycondition = "AND replycount = 0";
	}
	else if ($displaymode == 2)
	{
		if (isset($forumthreads["$forumid"]))
		{
			$threadids = $forumthreads["$forumid"];
		}
		else
		{
			require_once(DIR . '/includes/functions_fdwforum.php');
			$threadids = fdw_fetch_relevant_threads($forumid);
		}
		$displaycondition = "AND thread.threadid IN ($threadids)";
		$sortfield = "FIELD($threadids)";
	}
	
	if ($displayfilter == 2)
	{
		$markingcondition = "AND thread.lastpost > {$lastread["$forumid"]}";
		if ($vbulletin->options['threadmarking'])
		{
			$markingcondition .= " AND thread.lastpost > IFNULL(threadread.readtime, 0)";
			$markingjoin = "LEFT JOIN " . TABLE_PREFIX . "threadread AS threadread ON (threadread.threadid = thread.threadid AND threadread.userid = " . $vbulletin->userinfo['userid'] . ")";
		}
		else
		{
			if (!empty($threadsmarked))
			{
				$markingcondition .= " AND thread.lastpost > IFNULL(ELT(FIELD(thread.threadid, $threadsmarked), $threadsmarkeddate), 0)";
			}
		}
	}
	
	
	$limit = $first ? $perpage : intval(($foruminfo['relevance'] * $perpage) / $totalrelevance);
	$first = false;
	
	if ($limit > 0 AND ($displaymode != 2 OR !empty($threadids)))
	{
		$forumquery[] = "
			(
				SELECT thread.threadid, thread.forumid
				FROM " . TABLE_PREFIX . "thread AS thread
					$markingjoin
				WHERE
					forumid = $forumid AND thread.visible = 1 AND open <> 10
					$displaycondition
					$limitothers
					$globalignore
					$markingcondition
				ORDER BY $sortfield
				LIMIT $limit
			)
		";
	}
}

$threadids = '';
if (count($forumquery) > 0)
{
	$forumquery = array_reverse($forumquery); // most relevant forum at end to cut threads properly
	$threads = $vbulletin->db->query_read_slave("
		" . implode(" UNION ALL ", $forumquery) . "
		LIMIT $perpage
	");

	while ($thread = $vbulletin->db->fetch_array($threads))
	{
		$threadids .= ",$thread[threadid]";
	}
}

if (!empty($threadids))
{
	$previewfield = $previewjoin = $votequery = $fdwvotequery = '';
	if (!empty($previewforums))
	{
		$previewfield = "IF(forumid IN (0$previewforums), post.pagetext, '') AS preview,";
		$previewjoin = "LEFT JOIN " . TABLE_PREFIX . "post AS post ON(post.postid = thread.firstpostid)";
	}
	if (!empty($voteforums))
	{
		$vbulletin->options['showvotes'] = intval($vbulletin->options['showvotes']);
		$votequery = "
			IF(votenum >= " . $vbulletin->options['showvotes'] . " AND forumid IN (0$voteforums), votenum, 0) AS votenum,
			IF(votenum >= " . $vbulletin->options['showvotes'] . " AND forumid IN (0$voteforums) AND votenum > 0, votetotal / votenum, 0) AS voteavg,
		";
	}
	if (!empty($fdwvoteforums))
	{
		if ($vbulletin->options['fdw_vote_canneg'])
		{
			$fdwvotequery = "
				IF(forumid IN (0$fdwvoteforums), thread.votepos, 0) AS votepos,
				IF(forumid IN (0$fdwvoteforums), thread.voteneg, 0) AS voteneg,
				IF(forumid IN (0$fdwvoteforums), thread.votepos - thread.voteneg, 0) AS fdwvote,
			";
		}
		else
		{
			$fdwvotequery = "
				IF(forumid IN (0$fdwvoteforums), thread.votepos, 0) AS votepos,
				0 AS voteneg,
				IF(forumid IN (0$fdwvoteforums), thread.votepos, 0) AS fdwvote,
			";
		}
	}
	
	$threads = $vbulletin->db->query_read_slave("
		SELECT $fdwvotequery $votequery $previewfield
			thread.threadid, thread.title AS threadtitle, thread.forumid, pollid, open, postusername, postuserid, thread.iconid AS threadiconid,
			thread.lastpost, thread.lastposter, thread.lastpostid, thread.replycount, IF(thread.views<=thread.replycount, thread.replycount+1, thread.views) AS views,
			thread.dateline, notes, thread.visible, sticky, votetotal, thread.attach,
			thread.prefixid, thread.taglist, hiddencount, deletedcount
			" . (($vbulletin->options['threadsubscribed']) ? ", NOT ISNULL(subscribethread.subscribethreadid) AS issubscribed" : "") . "
			" . (($vbulletin->options['threadmarking']) ? ", threadread.readtime AS threadread" : "") . "
		FROM " . TABLE_PREFIX . "thread AS thread
			" . (($vbulletin->options['threadsubscribed']) ?  " LEFT JOIN " . TABLE_PREFIX . "subscribethread AS subscribethread ON(subscribethread.threadid = thread.threadid AND subscribethread.userid = " . $vbulletin->userinfo['userid'] . " AND canview = 1)" : "") . "
			" . (($vbulletin->options['threadmarking']) ? " LEFT JOIN " . TABLE_PREFIX . "threadread AS threadread ON (threadread.threadid = thread.threadid AND threadread.userid = " . $vbulletin->userinfo['userid'] . ")" : "") . "
			$previewjoin
		WHERE thread.threadid IN (0$threadids)
		ORDER BY lastpost DESC
	");
	
	$dotthreads = fetch_dot_threads_array($threadids);
	if ($vbulletin->options['showdots'])
	{
		$show['dotthreads'] = true;
	}
	else
	{
		$show['dotthreads'] = false;
	}
	
	if ($show['movethread'] OR $show['deletethread'] OR $show['approvethread'] OR $show['openthread'])
	{
		$show['inlinemod'] = true;
		$show['spamctrls'] = $show['deletethread'];
		$url = SCRIPTPATH;
		if ($show['popups'])
		{
			eval('$threadadmin_imod_menu_thread = "' . fetch_template('threadadmin_imod_menu_thread') . '";');
		}
	}
	else
	{
		$show['inlinemod'] = false;
		$url = '';
	}

	$show['forumlink'] = $vbulletin->options['fdw_forumcustom_forumlink'];
	
	if (!empty($previewforums) AND $vbulletin->userinfo['ignorelist'])
	{
		// Get Buddy List
		$buddy = array();
		if (trim($vbulletin->userinfo['buddylist']))
		{
			$buddylist = preg_split('/( )+/', trim($vbulletin->userinfo['buddylist']), -1, PREG_SPLIT_NO_EMPTY);
			foreach ($buddylist AS $buddyuserid)
			{
				$buddy["$buddyuserid"] = 1;
			}
		}
		DEVDEBUG('buddies: ' . implode(', ', array_keys($buddy)));
		// Get Ignore Users
		$ignore = array();
		if (trim($vbulletin->userinfo['ignorelist']))
		{
			$ignorelist = preg_split('/( )+/', trim($vbulletin->userinfo['ignorelist']), -1, PREG_SPLIT_NO_EMPTY);
			foreach ($ignorelist AS $ignoreuserid)
			{
				if (!$buddy["$ignoreuserid"])
				{
					$ignore["$ignoreuserid"] = 1;
				}
			}
		}
		DEVDEBUG('ignored users: ' . implode(', ', array_keys($ignore)));
	}
	
	if ($vbulletin->options['fdw_forumcustom_vote'])
	{
		$vbulletin->options['fdw_vote_show'][] = 'fdwforum';
	}
	
	$threadbits = '';
	while ($thread = $vbulletin->db->fetch_array($threads))
	{
		$foruminfo = fetch_foruminfo($thread['forumid']);
		$show['disabled'] = !in_array($thread['forumid'], $forumcanmoderate);
		$thread = process_thread_array($thread, $lastread["$thread[forumid]"], $foruminfo['allowicons']);
		
		eval('$threadbits .= "' . fetch_template('threadbit') . '";');
	}
	
	$show['threads'] = true;
}

//construct pp options
$ppselectoptions = '';
$ppoptjump = $ppoptval = intval($vbulletin->options['fdw_forumcustom_maxpp'] / $vbulletin->options['fdw_forumcustom_maxjump']);
while ($ppoptval <= $vbulletin->options['fdw_forumcustom_maxpp'])
{
	$selected = (isset($ppopt["$ppoptval"])) ? $ppopt["$ppoptval"] : '';
	$ppselectoptions .= "<option value=\"$ppoptval\"$selected>$ppoptval</option>";
	$ppoptval += $ppoptjump;
}

construct_forum_jump();

$show['bottomcolspan'] = 5;
if ($show['threadicons'])
{
	$show['bottomcolspan']++;
}
if ($show['inlinemod'])
{
	$show['bottomcolspan']++;
}

$navbits = construct_navbits(array('' => $vbphrase['forumcustom_breadcrumb']));
eval('$navbar = "' . fetch_template('navbar') . '";');

eval('print_output("' . fetch_template('FORUMDISPLAY_fdw') . '");');
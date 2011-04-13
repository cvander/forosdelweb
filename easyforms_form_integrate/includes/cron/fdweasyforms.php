<?php
/*======================================================================*\
|| #################################################################### ||
|| # Easy Forms Integration - Foros del Web                           # ||
|| # @author      David Barrios <davidbarriosfdw@gmail.com            # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db))
{
	exit;
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

require_once(DIR . '/includes/class_mail.php');

// Fetch forms
$fdwforms = array();
$forms_query = $vbulletin->db->query_read("
	SELECT fdwforms.*, forms.forumid FROM " . TABLE_PREFIX . "fdwforms AS fdwforms
	INNER JOIN " . TABLE_PREFIX . "forms AS forms USING (fid)
	WHERE fdwforms.email = 1 OR closethread = 1
");
while ($currentform = $vbulletin->db->fetch_array($forms_query))
{
	if ($currentform['parsefield'])
	{
		$parsearray = array();
		$parseparts = preg_split('/\n|\r/', $currentform['parsefield'], -1, PREG_SPLIT_NO_EMPTY);
		foreach ($parseparts AS $parsepart)
		{
			$parsepart = explode('|', $parsepart);
			$parsearray["$parsepart[0]"] = $parsepart[1];
		}
		$currentform['parsefield'] = $parsearray;
	}
	$fdwforms["$currentform[fid]"] = $currentform;
}
$vbulletin->db->free_result($forms_query);

if (!count($fdwforms))
{
	exit;
}

// Process new threads
if ($lastid_query = $vbulletin->db->query_first("SELECT data FROM " . TABLE_PREFIX . "datastore WHERE title = 'fdweasyforms'"))
{
	$lastid = $lastid_query['data'];
}
else
{
	$lastid = 0;
}
$formids = array_keys($fdwforms);
$resultlist = $userids = $datelines = $forumids = array();
$formresults_query = $vbulletin->db->query_read("
	SELECT id, fid, userid, time, sdata FROM " . TABLE_PREFIX . "formresults
	WHERE fid IN (" . implode(',', $formids) . ") AND id > $lastid ORDER BY id
");
while ($formresult = $vbulletin->db->fetch_array($formresults_query))
{
	$lastid = $formresult['id'];
	
	$hash = "h_$formresult[userid]_$formresult[time]_" . $fdwforms["$formresult[fid]"]['forumid'];
	$userids[] = $formresult['userid'];
	$datelines[] = $formresult['time'];
	$forumids[] = $fdwforms["$formresult[fid]"]['forumid'];
	$formresult['sdata'] = unserialize($formresult['sdata']);
	$formresult['threadid'] = $formresult['status'] = $formresult['expiredays'] = 0;
	$resultlist["$hash"] = $formresult;
}
$vbulletin->db->free_result($formresults_query);
if (count($resultlist))
{
	build_datastore('fdweasyforms', $lastid, 0);
	
	$threadresults = $vbulletin->db->query_read("
		SELECT threadid, dateline, postuserid, forumid FROM " . TABLE_PREFIX . "thread AS thread
		WHERE
			forumid IN (" . implode(',', $forumids) . ")
			AND postuserid IN (" . implode(',', $userids) . ")
			AND dateline IN (" . implode(',', $datelines) . ")
	");
	
	while ($thread = $vbulletin->db->fetch_array($threadresults))
	{
		$hash = "h_$thread[postuserid]_$thread[dateline]_$thread[forumid]";
		if ($resultlist["$hash"])
		{
			$resultmatch =& $resultlist["$hash"];
			$resultmatch['threadid'] = $thread['threadid'];
			$resultmatch['status'] = 1; // matched
			
			$resultform =& $fdwforms["$resultmatch[fid]"];
			$expirevalue = $resultmatch['sdata']["$resultform[expirefield]"];
			if ($expirevalue)
			{
				if (is_array($resultform['parsefield']))
				{
					$resultmatch['expiredays'] = intval(array_search($expirevalue, $resultform['parsefield']));
				}
				else
				{
					$resultmatch['expiredays'] = intval($expirevalue);
				}
				if ($resultmatch['expiredays'] > 0)
				{
					$resultmatch['status'] = 2; // expire date set
				}
			}
		}
	}
	$vbulletin->db->free_result($threadresults);
	
	$resultquery = array();
	foreach ($resultlist AS $resultprocess)
	{
		$resultquery[] = "($resultprocess[id], $resultprocess[threadid], $resultprocess[expiredays], $resultprocess[status], 0)";
	}
	$vbulletin->db->query_write("
		REPLACE INTO " . TABLE_PREFIX . "fdwformresults
			(id, threadid, expiredays, status, email)
		VALUES
			" . implode(',', $resultquery) . "
	");
}

// Process expired threads
$expired_query = $vbulletin->db->query_read("
	SELECT fdwformresults.id, fdwformresults.threadid, formresults.fid FROM " . TABLE_PREFIX . "fdwformresults AS fdwformresults
	INNER JOIN " . TABLE_PREFIX . "formresults AS formresults USING (id)
	WHERE fdwformresults.status = 2
		AND " . TIMENOW . " > (formresults.time + (fdwformresults.expiredays * 24 * 60 * 60))
	ORDER BY fid
");
$expiredthreads = array();
$fetchthreads = array();
while ($expiredresult = $vbulletin->db->fetch_array($expired_query))
{
	$expiredresult['status'] = 3; // expired
	$expiredthreads["$expiredresult[id]"] = $expiredresult;
	$fetchthreads[] = $expiredresult['threadid'];
}
if (count($expiredthreads))
{
	// Update status
	$vbulletin->db->query_write("
		UPDATE " . TABLE_PREFIX . "fdwformresults
		SET status = 3
		WHERE id IN (" . implode(',', array_keys($expiredthreads)) . ")
	");
	
	$threads_query = $vbulletin->db->query_read("
		SELECT * FROM " . TABLE_PREFIX . "thread AS thread
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid = thread.postuserid)
		WHERE thread.threadid IN (" . implode(',', $fetchthreads) . ") AND thread.open <> 10
	");
	
	$threads = array();
	while ($thread = $vbulletin->db->fetch_array($threads_query))
	{
		$threads["$thread[threadid]"] = $thread;
	}
	$vbulletin->db->free_result($threads_query);
	
	$mailinginfo = array('current' => 0, 'usingsmtp' => true);
	foreach ($expiredthreads AS $expiredid => $expiredthread)
	{
		$forminfo = $fdwforms["$expiredthread[fid]"];
		$thread = $threads["$expiredthread[threadid]"];
		if (!$thread OR !$forminfo)
		{
			continue;
		}
		
		if ($forminfo['openthreads'] AND (!$thread['open'] OR !($thread['visible'] == 1)))
		{
			continue;
		}
		
		if ($thread['replycount'] < $forminfo['minreplyes'] OR (($thread['votepos'] - $thread['voteneg']) < $forminfo['minvotes']))
		{
			continue;
		}
		
		if ($forminfo['closethread'] AND $thread['open'])
		{
			$threaddata =& datamanager_init('Thread', $vbulletin, ERRTYPE_SILENT, 'threadpost');
			$threaddata->set_existing($thread);
			$threaddata->set('open', 0);
			$threaddata->save();
		}
		
		$emailstatus = 0;
		if ($forminfo['email'])
		{
			if ($forminfo['emailavoid'] AND (!($thread['options'] & $vbulletin->bf_misc_useroptions['adminemail'])))
			{
				$emailstatus = 1; // avoided
			}
			else
			{
				$emailstatus = 2; // processed
				if ($mailinginfo['current'] != $forminfo['fid'] AND $mailinginfo['usingsmtp'])
				{
					$prototype =& fetch_mailer(!$forminfo['smtp'], $forminfo['smtphost'], $forminfo['smtpuser'], $forminfo['smtppass'], $forminfo['smtpport'], $forminfo['smtpsecure']);
					$mailinginfo = array('current' => $forminfo['fid'], 'usingsmtp' => $forminfo['smtp']);
				}
				
				// Parse email body
				eval('$message = "' . $forminfo['emailbodyparsed'] . '";');
				
				$mail = (phpversion() < '5' ? $prototype : clone($prototype));
				$mail->quick_set($thread['email'], $forminfo['emailsubject'], $message, '', $forminfo['emailfrom']);
				$mail->send();
			}
		}
		
		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "fdwformresults
			SET status = 4, email = $emailstatus
			WHERE id = $expiredthread[id]
		");
	}
}

if (isset($vbulletin->options['fdw_use_smtp']))
{
	$vbulletin->options['use_smtp'] = $vbulletin->options['fdw_use_smtp'];
}

log_cron_action('', $nextitem, 1);

function &fetch_mailer($vbdefault, $smtphost, $smtpuser, $smtppass, $smtpport, $smtpsecure)
{
	global $vbulletin;
	if (!isset($vbulletin->options['fdw_use_smtp']))
	{
		$vbulletin->options['fdw_use_smtp'] = $vbulletin->options['use_smtp'];
	}
	
	if ($vbdefault)
	{
		$vbulletin->options['use_smtp'] = $vbulletin->options['fdw_use_smtp'];
	}
	else
	{
		$vbulletin->options['use_smtp'] = true;
	}
	
	if ($vbulletin->options['use_smtp'])
	{
		$prototype =& new vB_SmtpMail($vbulletin);
		if (!$vbdefault)
		{
			$prototype->secure = $smtpsecure;
			$prototype->smtpHost = $smtphost;
			$prototype->smtpPort = $smtpport;
			$prototype->smtpUser = $smtpuser;
			$prototype->smtpPass = $smtppass;
		}
	}
	else
	{
		$prototype =& new vB_Mail($vbulletin);
	}
	
	return $prototype;
}
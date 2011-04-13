<?php
/*======================================================================*\
|| #################################################################### ||
|| # Reddit ranking algorithm - Foros del Web                         # ||
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

if (!$vbulletin->options['fdw_rank_enable'])
{
	exit;
}

$datestart = $vbulletin->options['fdw_rank_date'];
$datecut = 0;
if ($lastrun = $vbulletin->db->query_first("SELECT data FROM " . TABLE_PREFIX . "datastore WHERE title = 'fdwrank'"))
{
	$datecut = $lastrun['data'];
}

// Get recently voted threads
$votedthreads = $vbulletin->db->query_read("
	SELECT DISTINCT threadid
	FROM " . TABLE_PREFIX . "fdwvote
	WHERE votedate > $datecut
");

$threadids = array();
while ($thread = $vbulletin->db->fetch_array($votedthreads))
{
	$threadids[] = $thread['threadid'];
}

// Update rank
if (count($threadids))
{
	$vbulletin->db->query_write("
		UPDATE " . TABLE_PREFIX . "thread
		SET fdwrank = ROUND(LOG10(GREATEST(ABS(votepos - voteneg), 1)) + SIGN(votepos - voteneg) * (CAST(dateline AS SIGNED) - $datestart) / 45000.0, 7)
		WHERE threadid IN (" . implode(', ', $threadids) . ")
	");
}

build_datastore('fdwrank', TIMENOW);

log_cron_action(implode(',', $threadids), $nextitem, 1);
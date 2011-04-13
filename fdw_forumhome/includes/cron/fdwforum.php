<?php
/*======================================================================*\
|| #################################################################### ||
|| # Custom Forum Home - Foros del Web                                # ||
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

require_once(DIR . '/includes/functions_fdwforum.php');

build_fdwforum_stats(0, $vbulletin->options['fdw_forumhome_absdays'], null, -1);
build_fdwforum_cache();

log_cron_action('', $nextitem, 1);
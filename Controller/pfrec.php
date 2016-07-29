#!/usr/local/bin/php
<?php
/* $pfre: pfrec.php,v 1.19 2016/07/27 04:16:03 soner Exp $ */

/*
 * Copyright (c) 2016 Soner Tari.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this
 *    software must display the following acknowledgement: This
 *    product includes software developed by Soner Tari
 *    and its contributors.
 * 4. Neither the name of Soner Tari nor the names of
 *    its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written
 *    permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/** @file
 * Proxy to run all shell commands.
 * This way we have only one entry in doas.conf file.
 * @todo Continually check for security issues.
 */

/// @todo Is there a better way?
$ROOT= dirname(dirname(__FILE__));
$VIEW_PATH= $ROOT.'/View';
$MODEL_PATH= $ROOT.'/Model';

require_once($ROOT.'/lib/defs.php');
require_once($ROOT.'/lib/setup.php');

// chdir is for PCRE, libraries
chdir(dirname(__FILE__));

/// This is a command line tool, should never be requested on the web interface.
if (filter_has_var(INPUT_SERVER, 'SERVER_ADDR')) {
	pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Requested on the wui, exiting...');
	header('Location: /index.php');
}

require_once($ROOT.'/lib/lib.php');
require_once('lib.php');

unset($ViewError);
$retval= 1;

require_once($MODEL_PATH.'/pf.php');
$Model= new Pf();
$Command= $argv[1];

if (method_exists($Model, $Command)) {
	$ArgV= array_slice($argv, 2);

	if (array_key_exists($Command, $Model->Commands)) {
		$run= FALSE;

		ComputeArgCounts($Model->Commands, $ArgV, $Command, $ActualArgC, $ExpectedArgC, $AcceptableArgC, $ArgCheckC);

		// Extra args are OK for now, will drop later
		if ($ActualArgC >= $AcceptableArgC) {
			if ($ArgCheckC === 0) {
				$run= TRUE;
			}
			else {
				// Check only the relevant args
				$run= ValidateArgs($Model->Commands, $Command, $ArgV, $ArgCheckC);
			}
		}
		else {
			$ErrorStr= "[$AcceptableArgC]: $ActualArgC";
			ViewError(_('Not enough args')." $ErrorStr");
			pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Not enough args $ErrorStr");
		}

		if ($run) {
			if ($ActualArgC > $ExpectedArgC) {
				$ErrorStr= "[$ExpectedArgC]: $ActualArgC: ".implode(', ', array_slice($ArgV, $ExpectedArgC));

				// Drop extra arguments before passing to the function
				$ArgV= array_slice($ArgV, 0, $ExpectedArgC);

				ViewError(_('Too many args, truncating')." $ErrorStr");
				pfrec_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "Too many args, truncating $ErrorStr");
			}

			if (($Output= call_user_func_array(array($Model, $Command), $ArgV)) !== FALSE) {
				if ($Output !== TRUE) {
					// If func retval is not boolean, it is data, return it
					echo $Output;
				}
				$retval= 0;
			}
		}
		else {
			ViewError(_('Not running command').": $Command");
			pfrec_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "Not running command: $Command");
		}
	}
	else {
		ViewError(_('Unsupported command').": $Command");
		pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Unsupported command: $Command");
	}
}
else {
	
	$ErrorStr= "Pf->$Command()";
	ViewError(_('Method does not exist').": $ErrorStr");
	pfrec_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "Method does not exist: $ErrorStr");
}

if ($retval === 1 && isset($ViewError)) {
	echo $ViewError;
}
exit($retval);
?>

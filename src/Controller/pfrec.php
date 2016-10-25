#!/usr/local/bin/php
<?php
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
 * 
 * This way we have only one entry in doas.conf.
 * 
 * @todo Continually check for security issues.
 */

/// @todo Is there a better way?
$ROOT= dirname(dirname(dirname(__FILE__)));
$SRC_ROOT= dirname(dirname(__FILE__));

require_once($SRC_ROOT . '/lib/defs.php');
require_once($SRC_ROOT . '/lib/setup.php');

// chdir is for PCRE, libraries
chdir(dirname(__FILE__));

require_once($MODEL_PATH . '/pf.php');

require_once('lib.php');

/// This is a command line tool, should never be requested on the web interface.
if (filter_has_var(INPUT_SERVER, 'SERVER_ADDR')) {
	/// @attention pfrec_syslog() is in the Model, use after including model
	pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Requested on the wui, exiting...');
	header('Location: /index.php');
	exit;
}

$ArgV= array_slice($argv, 1);

if ($ArgV[0] === '-t') {
	$ArgV= array_slice($ArgV, 1);

	$TEST_ROOT= dirname(dirname(dirname(__FILE__)));
	$TEST_DIR= '/tests/phpunit/root';
	$TEST_DIR_PATH= $TEST_ROOT . $TEST_DIR;
	$TEST_DIR_SRC= $TEST_DIR . '/var/www/htdocs/pfre';

	$INSTALL_USER= posix_getpwuid(posix_getuid())['name'];
}

$Model= new Pf();
$Command= $ArgV[0];

$retval= 1;

if (method_exists($Model, $Command)) {
	$ArgV= array_slice($ArgV, 1);

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
			Error(_('Not enough args')." $ErrorStr");
			pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Not enough args $ErrorStr");
		}

		if ($run) {
			if ($ActualArgC > $ExpectedArgC) {
				$ErrorStr= "[$ExpectedArgC]: $ActualArgC: ".implode(', ', array_slice($ArgV, $ExpectedArgC));

				// Drop extra arguments before passing to the function
				$ArgV= array_slice($ArgV, 0, $ExpectedArgC);

				Error(_('Too many args, truncating')." $ErrorStr");
				pfrec_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "Too many args, truncating $ErrorStr");
			}

			if (call_user_func_array(array($Model, $Command), $ArgV)) {
				$retval= 0;
			}
		}
		else {
			Error(_('Not running command').": $Command");
			pfrec_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "Not running command: $Command");
		}
	}
	else {
		Error(_('Unsupported command').": $Command");
		pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Unsupported command: $Command");
	}
}
else {
	
	$ErrorStr= "Pf->$Command()";
	Error(_('Method does not exist').": $ErrorStr");
	pfrec_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "Method does not exist: $ErrorStr");
}

/// @attention Always return errors, success or fail
// Return an encoded array, so that the caller can easily separate output and error messages
$msg= array($Output, $Error);
$encoded= json_encode($msg);

if ($encoded !== NULL) {
	echo $encoded;
} else {
	pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed encoding output and error: ' . print_r($msg, TRUE));
}

exit($retval);
?>

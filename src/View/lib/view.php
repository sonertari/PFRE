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
 * View base class.
 */

class View
{
	/**
	 * Calls the controller.
	 *
	 * Both command and arguments are passed as variable arguments.
	 *
	 * @param array $output Output of the command.
	 * @param mixed Variable_Args Elements of the command line in variable arguments.
	 * @return bool Return value of shell command (adjusted for PHP).
	 */
	function Controller(&$output)
	{
		global $SRC_ROOT;

		$return= FALSE;
		try {
			$pfrec= $SRC_ROOT . '/Controller/pfrec.php';

			$argv= func_get_args();
			// Arg 0 is $output, skip it
			$argv= array_slice($argv, 1);

			if ($this->EscapeArgs($argv, $cmdline)) {
				$locale= $_SESSION['Locale'];
				$cmdline= "/usr/bin/doas $pfrec $locale $cmdline";
				
				// Init command output
				$outputArray= array();
				/// @bug http://bugs.php.net/bug.php?id=49847, fixed/closed in SVN on 141009
				exec($cmdline, $outputArray, $retval);

				// (exit status 0 in shell) == (TRUE in php)
				if ($retval === 0) {
					$return= TRUE;
				}

				$output= array();
				$errorStr= '';

				$decoded= json_decode($outputArray[0]);
				if ($decoded !== NULL && is_array($decoded)) {
					$output= explode("\n", $decoded[0]);
					$errorStr= $decoded[1];
				} else {
					$msg= "Failed decoding output: $outputArray[0]";
					pfrewui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "$msg, ($cmdline)");
					PrintHelpWindow(_NOTICE('FAILED') . ":<br>$msg", 'auto', 'ERROR');
				}

				// Show error, if any
				if ($errorStr !== '') {
					$error= explode("\n", $errorStr);

					pfrewui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Shell command exit status: $retval: (" . implode(', ', $error) . "), ($cmdline)");
					PrintHelpWindow(_NOTICE('FAILED') . ':<br>' . implode('<br>', $error), 'auto', 'ERROR');
				}
			}
		}
		catch (Exception $e) {
			echo 'Exception: '.__FILE__.' '.__FUNCTION__.' ('.__LINE__.'): '.$e->getMessage()."\n";
			pfrewui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Exception: '.$e->getMessage());
		}
		return $return;
	}
	
	/**
	 * Escapes the arguments passed to Controller() and builds the command line.
	 *
	 * @param array $argv Command and arguments array.
	 * @param string $cmdline Actual command line to run.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function EscapeArgs($argv, &$cmdline)
	{
		if (count($argv) > 0) {
			$cmd= $argv[0];
			$argv= array_slice($argv, 1);
  	
			$cmdline= $cmd;
			foreach ($argv as $arg) {
				$cmdline.= ' '.escapeshellarg($arg);
			}
			return TRUE;
		}
		pfrewui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, '$argv is empty');
		return FALSE;
	}
}

/// For classifying gettext strings into files.
function _MENU($str)
{
	return _($str);
}

/// For classifying gettext strings into files.
function _CONTROL($str)
{
	return _($str);
}

/// For classifying gettext strings into files.
function _NOTICE($str)
{
	return _($str);
}

/// For classifying gettext strings into files.
function _TITLE($str)
{
	return _($str);
}

/// For classifying gettext strings into files.
function _HELPBOX($str)
{
	return _($str);
}
?>

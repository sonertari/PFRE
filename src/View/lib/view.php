<?php
/*
 * Copyright (C) 2004-2016 Soner Tari
 *
 * This file is part of PFRE.
 *
 * PFRE is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PFRE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PFRE.  If not, see <http://www.gnu.org/licenses/>.
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

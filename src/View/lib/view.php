<?php
/*
 * Copyright (C) 2004-2019 Soner Tari
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

require_once $VIEW_PATH.'/lib/phpseclib/Net/SSH2.php';

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
		global $SRC_ROOT, $UseSSH;

		$return= FALSE;
		try {
			$ctlr= $SRC_ROOT . '/Controller/ctlr.php';

			$argv= func_get_args();
			// Arg 0 is $output, skip it
			$argv= array_slice($argv, 1);

			if ($this->EscapeArgs($argv, $cmdline)) {
				$locale= $_SESSION['Locale'];
				$cmdline= "/usr/bin/doas $ctlr $locale $cmdline";
				
				// Init command output
				$outputArray= array();

				$executed= TRUE;
				if ($UseSSH) {
					// Subsequent calls use the encrypted password in the cookie, so we should decrypt it first.
					$ciphertext_base64= $_COOKIE['passwd'];
					$ciphertext= base64_decode($ciphertext_base64);

					$iv_size= openssl_cipher_iv_length('AES-256-CBC');
					$iv= substr($ciphertext, 0, $iv_size);

					$ciphertext= substr($ciphertext, $iv_size);

					$passwd= openssl_decrypt($ciphertext, 'AES-256-CBC', $_SESSION['cryptKey'], OPENSSL_RAW_DATA, $iv);

					$ssh= new Net_SSH2('localhost');

					// Give more time to all requests, the default timeout is 10 seconds
					$ssh->setTimeout(30);

					if ($ssh->login($_SESSION['USER'], $passwd)) {
						$outputArray[0]= $ssh->exec($cmdline);
						if ($ssh->isTimeout()) {
							$msg= 'SSH exec timed out';
							wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "$msg, ($cmdline)");
							PrintHelpWindow($msg, 'auto', 'ERROR');
							$executed= FALSE;
						}
					} else {
						$msg= 'SSH login failed';
						wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "$msg, ($cmdline)");
						PrintHelpWindow($msg, 'auto', 'ERROR');
						$executed= FALSE;
					}
				} else {
					/// @bug http://bugs.php.net/bug.php?id=49847, fixed/closed in SVN on 141009
					exec($cmdline, $outputArray);
				}
 
				if ($executed) {
					$output= array();
					$errorStr= '';
					$retval= 1;

					$decoded= json_decode($outputArray[0], TRUE);
					if ($decoded !== NULL && is_array($decoded)) {
						$output= explode("\n", $decoded[0]);
						$errorStr= $decoded[1];
						$retval= $decoded[2];
					} else {
						$msg= "Failed decoding output: $outputArray[0]";
						wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "$msg, ($cmdline)");
						PrintHelpWindow($msg, 'auto', 'ERROR');
					}

					// Show error, if any
					if ($errorStr !== '') {
						$error= explode("\n", $errorStr);

						wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Shell command exit status: $retval: (" . implode(', ', $error) . "), ($cmdline)");
						PrintHelpWindow(implode('<br>', $error), 'auto', 'ERROR');
					}

					// (exit status 0 in shell) == (TRUE in php)
					if ($retval === 0) {
						$return= TRUE;
					} else {
						wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Shell command exit status: $retval: ($cmdline)");
					}
				}
			}
		}
		catch (Exception $e) {
			echo 'Exception: '.__FILE__.' '.__FUNCTION__.' ('.__LINE__.'): '.$e->getMessage()."\n";
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Exception: '.$e->getMessage());
		}
		return $return;
	}

	/**
	 * Checks the given user:password pair by testing login.
	 * 
	 * @param string $user User name.
	 * @param string $passwd SHA encrypted password.
	 * @return bool TRUE if passwd matches, FALSE otherwise.
	 */
	function CheckAuthentication($user, $passwd)
	{
		$ssh = new Net_SSH2('localhost');
		if ($ssh->login($user, $passwd)) {
			$hostname= gethostname();
			/// @attention Trim the newline
			$output= trim($ssh->exec('hostname'));
			if ($hostname == $output) {
				return TRUE;
			} else {
				wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "SSH test command failed: $hostname == $output");
			}
		} else {
			PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Authentication failed'), 'auto', 'ERROR');
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Authentication failed');
		}
		return FALSE;
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
		wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, '$argv is empty');
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
function _HELPBOX($str)
{
	return _($str);
}

/// For classifying gettext strings into files.
function _TITLE($str)
{
	return _($str);
}
?>

<?php
/*
 * Copyright (C) 2004-2022 Soner Tari
 *
 * This file is part of UTMFW.
 *
 * UTMFW is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * UTMFW is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with UTMFW.  If not, see <http://www.gnu.org/licenses/>.
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
			// Prepend locale
			$argv= array_merge(array($_SESSION['Locale']), $argv);

			$encoded_args= json_encode($argv, JSON_UNESCAPED_SLASHES);
			if ($encoded_args !== NULL) {
				// Init command output
				$outputArray= array();

				$executed= TRUE;
				if ($UseSSH) {
					// Subsequent calls use the encrypted password in the cookie, so we should decrypt it first
					$ciphertext_base64= $_COOKIE['passwd'];
					$ciphertext= base64_decode($ciphertext_base64);

					$iv_size= openssl_cipher_iv_length('AES-256-CBC');
					$iv= substr($ciphertext, 0, $iv_size);

					$ciphertext= substr($ciphertext, $iv_size);

					$passwd= openssl_decrypt($ciphertext, 'AES-256-CBC', $_SESSION['cryptKey'], OPENSSL_RAW_DATA, $iv);

					$ssh= new Net_SSH2('127.0.0.1');

					// Give more time to all requests, the default timeout is 10 seconds
					$ssh->setTimeout(30);

					if ($ssh->login($_SESSION['USER'], $passwd)) {
						// The login shells of admin and user users are set to sh.php, so we just pass down the args
						$outputArray[0]= $ssh->exec($encoded_args);
						if ($ssh->isTimeout()) {
							$msg= 'SSH exec timed out';
							wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "$msg, ($encoded_args)");
							PrintHelpWindow($msg, 'auto', 'ERROR');
							$executed= FALSE;
						}
					} else {
						$msg= 'SSH login failed';
						wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "$msg, ($encoded_args)");
						PrintHelpWindow($msg, 'auto', 'ERROR');
						$executed= FALSE;
					}
				} else {
					// Runs the command as the www user
					// Escape args to avoid shell expansion
					/// @bug http://bugs.php.net/bug.php?id=49847, fixed/closed in SVN on 141009
					exec("/usr/bin/doas $ctlr ".escapeshellarg($encoded_args), $outputArray);
				}
 
				if ($executed) {
					$output= array();
					$errorStr= '';
					$retval= 1;

					$decoded= json_decode($outputArray[0], TRUE);
					if ($decoded !== NULL && is_array($decoded)) {
						$output= explode("\n", $decoded[0]);
						$errorStr= $decoded[1];
						// FALSE returned by the Model function is json_encoded/decoded as string 'false'
						if ($decoded[0] == 'false') {
							$retval= 1;
						} else {
							$retval= $decoded[2];
						}
					} else {
						$msg= 'Failed decoding output: '.print_r($outputArray[0], TRUE);
						wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "$msg, ($encoded_args)");
						PrintHelpWindow($msg, 'auto', 'ERROR');
					}

					// Show errors, if any
					if ($errorStr !== '') {
						$error= explode("\n", $errorStr);

						wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Shell command exit status: $retval: (" . implode(', ', $error) . "), ($encoded_args)");
						PrintHelpWindow(implode('<br>', $error), 'auto', 'ERROR');
					}

					// (exit status 0 in shell) == (TRUE in php)
					if ($retval === 0) {
						$return= TRUE;
					} else {
						wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Shell command exit status: $retval: ($encoded_args)");
					}
				}
			} else {
				wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed encoding args: ' . print_r($argv, TRUE));
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
		$ssh = new Net_SSH2('127.0.0.1');
		if ($ssh->login($user, $passwd)) {
			$hostname= gethostname();

			$encoded_args= json_encode(array($_SESSION['Locale'], 'GetMyName'), JSON_UNESCAPED_SLASHES);
			$output= $ssh->exec($encoded_args);
			$decoded= json_decode($output, TRUE);
			if ($decoded !== NULL && is_array($decoded)) {
				$output= explode("\n", $decoded[0]);
			} else {
				$msg= 'Failed decoding output: '.print_r($output, TRUE);
				wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "$msg, ($encoded_args)");
				PrintHelpWindow($msg, 'auto', 'ERROR');
			}

			if ($hostname == $output[0]) {
				return TRUE;
			} else {
				wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "SSH test command failed: $hostname == $output[0]");
			}
		} else {
			PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Authentication failed'), 'auto', 'ERROR');
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Authentication failed');
		}
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

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
 * Contains base class which runs basic Model tasks.
 */

require_once($MODEL_PATH.'/include.php');

class Model
{
	/// Apache password file pathname.
	protected $passwdFile= '/var/www/conf/.htpasswd';
	
	/**
	 * Argument lists and descriptions of commands.
	 *
	 * @todo Should we implement $Commands using Interfaces in OOP?
	 *
	 * @param array argv Array of arg types in order.
	 * @param string desc Description of the shell function.
	 */
	public $Commands= array();

	private $NVPS= '=';
	private $COMC= '#';

	function __construct()
	{
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'CheckAuthentication'	=>	array(
					'argv'	=>	array(NAME, SHA1STR),
					'desc'	=>	_('Check authentication'),
					),
				
				'SetPassword'	=>	array(
					'argv'	=>	array(NAME, SHA1STR),
					'desc'	=>	_('Set user password'),
					),

				'SetLogLevel'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set log level'),
					),

				'SetHelpBox'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set help boxes'),
					),

				'SetSessionTimeout'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Set session timeout'),
					),

				'SetDefaultLocale'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set default locale'),
					),

				'SetForceHTTPs'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set force HTTPs'),
					),

				'SetMaxAnchorNesting'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Set max anchor nesting'),
					),

				'SetPfctlTimeout'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Set pfctl timeout'),
					),
				)
			);
	}

	/**
	 * Checks user's password supplied against the one in .htpasswd file.
	 * 
	 * Note that the passwords in .htpasswd are double encrypted.
	 *
	 * @param string $user User name.
	 * @param string $passwd SHA encrypted password.
	 * @return bool TRUE if passwd matches, FALSE otherwise.
	 */
	function CheckAuthentication($user, $passwd)
	{
		/// @warning Args should never be empty, htpasswd expects 2 args
		$passwd= $passwd == '' ? "''" : $passwd;

		/// Passwords in htpasswd file are SHA encrypted.
		exec("/usr/local/bin/htpasswd -bn -s '' $passwd 2>&1", $output, $retval);
		if ($retval === 0) {
			$htpasswd= ltrim($output[0], ':');
		
			/// @warning Have to trim newline chars, or passwds do not match
			$passwdfile= file($this->passwdFile, FILE_IGNORE_NEW_LINES);
			
			// Do not use preg_match() here. If there is more than one line (passwd) for a user in passwdFile,
			// this array method ensures that only one password apply to each user, the last one in passwdFile.
			// This should never happen actually, but in any case.
			$passwdlist= array();
			foreach ($passwdfile as $nvp) {
				list($u, $p)= explode(':', $nvp, 2);
				$passwdlist[$u]= $p;
			}

			if ($passwdlist[$user] === $htpasswd) {
				return TRUE;
			}
		}
		Error(_('Authentication failed'));
		return FALSE;
	}

	/**
	 * Sets user's password in .htpasswd file.
	 * 
	 * @param string $user User name.
	 * @param string $passwd SHA encrypted password.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetPassword($user, $passwd)
	{
		/// Passwords in htpasswd file are SHA encrypted.
		exec("/usr/local/bin/htpasswd -b -s $this->passwdFile $user $passwd 2>&1", $output, $retval);
		if ($retval === 0) {
			return TRUE;
		}
		$errout= implode("\n", $output);
		Error($errout);
		pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Set password failed: $errout");
		return FALSE;
	}

	/**
	 * Sets global log level.
	 * 
	 * @param string $level Level to set to.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetLogLevel($level)
	{
		global $ROOT, $TEST_DIR_SRC;

		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$LOG_LEVEL', $level.';');
	}

	/**
	 * Enables or disables help boxes.
	 * 
	 * @param bool $bool TRUE to enable, FALSE otherwise.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetHelpBox($bool)
	{
		global $ROOT, $TEST_DIR_SRC;
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/View/lib/setup.php', '\$ShowHelpBox', $bool.';');
	}
	
	/**
	 * Sets session timeout.
	 * 
	 * If the given values is less than 10, we set the timeout to 10 seconds.
	 * 
	 * @param int $timeout Timeout in seconds.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetSessionTimeout($timeout)
	{
		global $ROOT, $TEST_DIR_SRC;

		if ($timeout < 10) {
			$timeout= 10;
		}
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/View/lib/setup.php', '\$SessionTimeout', $timeout.';');
	}

	/**
	 * Sets default locale.
	 * 
	 * @param string $locale Locale.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetDefaultLocale($locale)
	{
		global $ROOT, $TEST_DIR_SRC;

		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$DefaultLocale', $locale.';');
	}

	/**
	 * Enables or disables HTTPs.
	 * 
	 * @param bool $bool TRUE to enable, FALSE to disable HTTPs.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetForceHTTPs($bool)
	{
		global $ROOT, $TEST_DIR_SRC;
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$ForceHTTPs', $bool.';');
	}

	/**
	 * Sets the max number of nested anchors allowed.
	 * 
	 * @param int $max Number of nested anchors allowed.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetMaxAnchorNesting($max)
	{
		global $ROOT, $TEST_DIR_SRC;
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$MaxAnchorNesting', $max.';');
	}

	/**
	 * Sets pfctl timeout.
	 * 
	 * Note that setting this value to 0 effectively fails all pfctl calls.
	 * 
	 * @param int $timeout Timeout waiting pfctl output in seconds.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetPfctlTimeout($timeout)
	{
		global $ROOT, $TEST_DIR_SRC;
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$PfctlTimeout', $timeout.';');
	}
	
	/**
	 * Runs the given shell command and returns its output as string.
	 *
	 * @todo Fix return value checks in some references, RunShellCommand() does not return FALSE
	 *
	 * @param string $cmd Command string to run.
	 * @return string Command result in a string.
	 */
	function RunShellCommand($cmd)
	{
		/// @attention Do not use shell_exec() here, because it is disabled when PHP is running in safe_mode
		/// @warning Not all shell commands return 0 on success, such as grep, date...
		/// Hence, do not check return value
		exec($cmd, $output);
		if (is_array($output)) {
			return implode("\n", $output);
		}
		return '';
	}

	/**
	 * Returns files with the given filepath pattern.
	 *
	 * $filepath does not have to be just directory path, and may contain wildcards.
	 *
	 * @param string $filepath File pattern to match.
	 * @return string List of file names, without path.
	 */
	function GetFiles($filepath)
	{
		return $this->RunShellCommand("ls -1 $filepath");
	}

	/**
	 * Reads file contents.
	 *
	 * @param string $file Config file.
	 * @return mixed File contents in a string or FALSE on fail.
	 */
	function GetFile($file)
	{
		if (file_exists($file)) {
			return file_get_contents($file);
		}
		return FALSE;
	}

	/**
	 * Deletes the given file or directory.
	 *
	 * @param string $path File or dir to delete.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function DeleteFile($path)
	{
		if (file_exists($path)) {
			exec("/bin/rm -rf $path 2>&1", $output, $retval);
			if ($retval === 0) {
				return TRUE;
			}
			else {
				$errout= implode("\n", $output);
				Error($errout);
				pfrec_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Failed deleting: $path, $errout");
			}
		}
		else {
			pfrec_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "File path does not exist: $path");
		}
		return FALSE;
	}

	/**
	 * Writes contents to file.
	 *
	 * @param string $file Config filename.
	 * @param string $contents Contents to write.
	 * @return mixed Output of file_put_contents() or FALSE on fail.
	 */
	function PutFile($file, $contents)
	{
		if (file_exists($file)) {
			return file_put_contents($file, $contents, LOCK_EX);
		}
		return FALSE;
	}

	/**
	 * Changes value of NVP.
	 *
	 * @param string $file Config file.
	 * @param string $name Name of NVP.
	 * @param mixed $newvalue New value to set.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetNVP($file, $name, $newvalue)
	{
		if (copy($file, $file.'.bak')) {
			if (($value= $this->GetNVP($file, $name)) !== FALSE) {
				/// @warning Backslash should be escaped first, or causes double escapes
				$value= Escape($value, '\/$^*()."');
				$re= "^(\h*$name\b\h*$this->NVPS\h*)($value)(\h*$this->COMC.*|\h*)$";

				$contents= preg_replace("/$re/m", '${1}'.$newvalue.'${3}', file_get_contents($file), 1, $count);
				if ($contents !== NULL && $count == 1) {
					file_put_contents($file, $contents);
					return TRUE;
				}
				else {
					pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot set new value $file, $name, $newvalue");
				}
			}
			else {
				pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot find NVP: $file, $name");
			}
		}
		else {
			pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot copy file $file");
		}
		return FALSE;
	}

	/**
	 * Reads value of NVP.
	 *
	 * @param string $file Config file.
	 * @param string $name Name of NVP.
	 * @param string $trimchars Chars to trim in the results.
	 * @return mixed Value of NVP or NULL on failure.
	 */
	function GetNVP($file, $name, $trimchars= '')
	{
		return $this->SearchFile($file, "/^\h*$name\b\h*$this->NVPS\h*([^$this->COMC'\"\n]*|'[^'\n]*'|\"[^\"\n]*\"|[^$this->COMC\n]*)(\h*|\h*$this->COMC.*)$/m", 1, $trimchars);
	}

	/**
	 * Searches the given file with the given regex.
	 *
	 * @param string $file Config file.
	 * @param string $re Regex to search the file with, should have end markers.
	 * @param int $set There may be multiple parentheses in $re, which one to return.
	 * @param string $trimchars If given, these chars are trimmed on the left or right.
	 * @return mixed String found or FALSE if no match.
	 */
	function SearchFile($file, $re, $set= 1, $trimchars= '')
	{
		/// @todo What to do with multiple matching NVPs
		if (preg_match($re, file_get_contents($file), $match)) {
			$retval= $match[$set];
			if ($trimchars !== '') {
				$retval= trim($retval, $trimchars);
			}
			return rtrim($retval);
		}
		return FALSE;
	}
}
?>

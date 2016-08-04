<?php
/* $pfre: model.php,v 1.2 2016/07/29 06:42:08 soner Exp $ */

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
 * Base class for models.
 */

require_once($MODEL_PATH.'/lib/RuleSet.php');
require_once($MODEL_PATH.'/lib/Rule.php');
require_once($MODEL_PATH.'/lib/Timeout.php');
require_once($MODEL_PATH.'/lib/State.php');
require_once($MODEL_PATH.'/lib/FilterBase.php');
require_once($MODEL_PATH.'/lib/Filter.php');
require_once($MODEL_PATH.'/lib/Antispoof.php');
require_once($MODEL_PATH.'/lib/Anchor.php');
require_once($MODEL_PATH.'/lib/NatBase.php');
require_once($MODEL_PATH.'/lib/NatTo.php');
require_once($MODEL_PATH.'/lib/BinatTo.php');
require_once($MODEL_PATH.'/lib/RdrTo.php');
require_once($MODEL_PATH.'/lib/AfTo.php');
require_once($MODEL_PATH.'/lib/DivertTo.php');
require_once($MODEL_PATH.'/lib/DivertPacket.php');
require_once($MODEL_PATH.'/lib/Route.php');
require_once($MODEL_PATH.'/lib/Macro.php');
require_once($MODEL_PATH.'/lib/Table.php');
require_once($MODEL_PATH.'/lib/Queue.php');
require_once($MODEL_PATH.'/lib/Scrub.php');
require_once($MODEL_PATH.'/lib/Option.php');
require_once($MODEL_PATH.'/lib/Limit.php');
require_once($MODEL_PATH.'/lib/LoadAnchor.php');
require_once($MODEL_PATH.'/lib/Include.php');
require_once($MODEL_PATH.'/lib/Comment.php');
require_once($MODEL_PATH.'/lib/Blank.php');

class Model
{
	/// Apache password file pathname.
	protected $passwdFile= '/var/www/conf/.htpasswd';
	
	/** Argument lists and descriptions of commands.
	 *
	 * @param[out] argv	Array of arg types in order
	 * @param[out] desc	Description of the shell function
	 *
	 * @todo $Commands should be implemented with Interfaces in OOP?
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
				
				'GetFileCvsTag'=>	array(
					'argv'	=>	array(FILEPATH),
					'desc'	=>	_('Get source file CVS tag'),
					),

				'SetPassword'	=>	array(
					'argv'	=>	array(NAME, SHA1STR),
					'desc'	=>	_('Set user password'),
					),

				'SetLogLevel'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set log level'),
					),

				'SetHelpBoxes'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set help boxes'),
					),

				'SetSessionTimeout'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Set session timeout'),
					),

				'SetForceHTTPs'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set force HTTPs'),
					),
				)
			);
	}

	/** Runs given shell command and returns its output as string.
	 *
	 * @todo Fix return value checks in some references, RunShellCommand() does not return FALSE
	 *
	 * @param[in]	$cmd	Command line to run.
	 * @return Command result in a string.
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

	/** Returns files with the given filepath pattern.
	 *
	 * $filepath does not have to be just directory path, and may contain wildcards.
	 *
	 * @param[in]	$filepath	string Pattern
	 * @return List of file names, without path
	 */
	function GetFiles($filepath)
	{
		return $this->RunShellCommand("ls -1 $filepath");
	}

	/** Checks user's password supplied against the one in htpasswd file.
	 *
	 * @param[in]	$user	User name.
	 * @param[in]	$passwd	Password.
	 * @return TRUE if passwd matches, FALSE otherwise.
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
		ViewError('Authentication failed');
		return FALSE;
	}

	/** Returns cvs tag in the given file, if any.
	 *
	 * @param[in]	$file string File pathname.
	 */
	function GetFileCvsTag($file)
	{
		if (($contents= $this->GetFile($file)) !== FALSE) {
			/// @warning Don't add $ to tag, otherwise CVS changes $re during commit
			$re= '/pfre:\s+(.*\.php,v\s+\d+\.\d+\s+\d+\/\d+\/\d+\s+\d+:\d+:\d+)\s+\S+\s+Exp\s+\$/';
			if (preg_match($re, $contents, $match)) {
				return $match[1];
			}
		}
		return FALSE;
	}

	/** Reads file contents.
	 *
	 * @param[in]	$file	Config file
	 * @return File contents
	 */
	function GetFile($file)
	{
		if (file_exists($file)) {
			return file_get_contents($file);
		}
		return FALSE;
	}

	/** Deletes file or dir.
	 *
	 * @param[in]	$path	string File or dir.
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
				ViewError($errout);
				pfrec_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Failed deleting: $path, $errout");
			}
		}
		else {
			pfrec_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "File path does not exist: $path");
		}
		return FALSE;
	}

	/** Writes contents to file.
	 *
	 * @param[in]	$file		Config file.
	 * @param[in]	$contents	Contents to write.
	 */
	function PutFile($file, $contents)
	{
		if (file_exists($file)) {
			return file_put_contents($file, $contents, LOCK_EX);
		}
		return FALSE;
	}

	/** Sets user's password in htpasswd file.
	 */
	function SetPassword($user, $passwd)
	{
		/// Passwords in htpasswd file are SHA1 encrypted.
		exec("/usr/local/bin/htpasswd -b -s $this->passwdFile $user $passwd 2>&1", $output, $retval);
		if ($retval === 0) {
			return TRUE;
		}
		$errout= implode("\n", $output);
		ViewError($errout);
		pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Set password failed: $errout");
		return FALSE;
	}

	function SetLogLevel($level)
	{
		global $ROOT;

		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT.'/lib/setup.php', '\$LOG_LEVEL', $level.';');
	}

	function SetHelpBoxes($bool)
	{
		global $VIEW_PATH;
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($VIEW_PATH.'/lib/setup.php', '\$ShowHelpBox', $bool.';');
	}
	
	function SetSessionTimeout($timeout)
	{
		global $VIEW_PATH;

		if ($timeout < 10) {
			$timeout= 10;
		}
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($VIEW_PATH.'/lib/setup.php', '\$SessionTimeout', $timeout.';');
	}

	function SetForceHTTPs($bool)
	{
		global $ROOT;
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT.'/lib/setup.php', '\$ForceHTTPs', $bool.';');
	}
	

	/** Changes value of NVP.
	 *
	 * @param[in]	$file		Config file
	 * @param[in]	$name		Name of NVP
	 * @param[in]	$newvalue	New value to set
	 * @return boolean Success or failure
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

	/** Reads value of NVP.
	 *
	 * @param[in]	$file		string Config file
	 * @param[in]	$name		string Name of NVP
	 * @param[in]	$trimchars	string Chars to trim in the results
	 * @return Value of NVP or NULL on failure
	 */
	function GetNVP($file, $name, $trimchars= '')
	{
		return $this->SearchFile($file, "/^\h*$name\b\h*$this->NVPS\h*([^$this->COMC'\"\n]*|'[^'\n]*'|\"[^\"\n]*\"|[^$this->COMC\n]*)(\h*|\h*$this->COMC.*)$/m", 1, $trimchars);
	}

	/** Searches a given file with a given regexp.
	 *
	 * @param[in]	$file	string Config file
	 * @param[in]	$re		string Regexp to search the file with, should have end markers
	 * @param[in]	$set	int There may be multiple parentheses in $re, which one to return
	 * @param[in]	$trimchars If given, these chars are trimmed
	 * @return String found or FALSE if no match
	 */
	function SearchFile($file, $re, $set= 1, $trimchars= '')
	{
		/// @todo What to do multiple matching NVPs
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

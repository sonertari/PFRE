<?php
/*
 * Copyright (C) 2004-2023 Soner Tari
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
 * Contains base class which runs basic Model tasks.
 */

require_once($MODEL_PATH.'/include.php');

class Model
{
	/**
	 * Argument lists and descriptions of commands.
	 *
	 * @todo Should we implement $Commands using Interfaces in OOP?
	 *
	 * @param array argv Array of arg types in order.
	 * @param string desc Description of the shell function.
	 */
	public $Commands= array();

	private $confDir= '/etc/';
	public $NVPS= '=';
	public $COMC= '#';

	function __construct()
	{
		$this->Commands= array_merge(
			$this->Commands,
			array(
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

				'SetUseSSH'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set use SSH'),
					),

				'SetMaxAnchorNesting'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Set max anchor nesting'),
					),

				'SetPfctlTimeout'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Set pfctl timeout'),
					),
				'GetMyName'		=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Read system hostname'),
					),
				)
			);
	}

	/**
	 * Sets user's password in the system password file.
	 * 
	 * Note that passwords are double encrypted.
	 * 
	 * @param string $user User name.
	 * @param string $passwd SHA encrypted password.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetPassword($user, $passwd)
	{
		exec("/bin/cat /etc/master.passwd | /usr/bin/grep -E '^$user:' 2>&1", $output, $retval);
		if ($retval === 0) {
			$line= $output[0];
			if (preg_match("/^$user:[^:]+(:.+)$/", $line, $match)) {
				unset($output);
				$cmdline= '/usr/bin/chpass -a "' . $user . ':$(/usr/bin/encrypt ' . $passwd . ')' . $match[1] . '"';
				exec($cmdline, $output, $retval);
				if ($retval === 0) {
					return TRUE;
				}
			}
		}

		$errout= implode("\n", $output);
		Error($errout);
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Set password failed: $errout");
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
	 * @param string $bool 'TRUE' to enable, 'FALSE' otherwise.
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
	 * @param string $bool 'TRUE' to enable, 'FALSE' otherwise.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetForceHTTPs($bool)
	{
		global $ROOT, $TEST_DIR_SRC;
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/lib/setup.php', '\$ForceHTTPs', $bool.';');
	}

	/**
	 * Enables or disables SSH.
	 * 
	 * @param string $bool 'TRUE' to enable, 'FALSE' otherwise.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetUseSSH($bool)
	{
		global $ROOT, $TEST_DIR_SRC;
		
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT . $TEST_DIR_SRC . '/View/lib/setup.php', '\$UseSSH', $bool.';');
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
				ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Failed deleting: $path, $errout");
			}
		}
		else {
			ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "File path does not exist: $path");
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
	 * @param string $newvalue New value to set.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetNVP($file, $name, $newvalue)
	{
		if (copy($file, $file.'.bak')) {
			if (($value= $this->GetNVP($file, $name)) !== FALSE) {
				/// @warning Backslash should be escaped first, or causes double escapes
				$value= Escape($value, '\/$^*().-[]"');
				$re= "^(\h*$name\b\h*$this->NVPS\h*)($value)(\h*$this->COMC.*|\h*)$";

				/// @todo Put strings between single quotes, otherwise PHP conf files complain about certain chars, such as ':'
				$contents= preg_replace("/$re/m", '${1}'.$newvalue.'${3}', file_get_contents($file), 1, $count);
				if ($contents !== NULL && $count == 1) {
					file_put_contents($file, $contents);
					return TRUE;
				}
				else {
					ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot set new value $file, $name, new: $newvalue, old: $value, re: $re, $count");
				}
			}
			else {
				ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot find NVP: $file, $name");
			}
		}
		else {
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot copy file: $file");
		}
		return FALSE;
	}

	/**
	 * Reads value of NVP.
	 *
	 * @param string $file Config file.
	 * @param string $name Name of NVP.
	 * @param int $set There may be multiple parentheses in $re, which one to return.
	 * @param string $trimchars Chars to trim in the results.
	 * @return mixed Value of NVP or FALSE on failure.
	 */
	function GetNVP($file, $name, $set= 0, $trimchars= '')
	{
		return $this->SearchFile($file, "/^\h*$name\b\h*$this->NVPS\h*([^$this->COMC'\"\n]*|'[^'\n]*'|\"[^\"\n]*\"|[^$this->COMC\n]*)(\h*|\h*$this->COMC.*)$/m", $set, $trimchars);
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
	function SearchFile($file, $re, $set= 0, $trimchars= '')
	{
		// There may be multiple matching NVPs
		if (preg_match_all($re, file_get_contents($file), $match)) {
			// Index 0 always gives full matches, so use index 1
			$retval= $match[1][$set];
			if ($trimchars !== '') {
				$retval= trim($retval, $trimchars);
			}
			return rtrim($retval);
		}
		return FALSE;
	}

	/**
	 * Reads hostname.
	 *
	 * @return string System name, output of hostname too.
	 */
	function GetMyName()
	{
		return Output($this->_getMyName());
	}

	function _getMyName()
	{
		return $this->GetFile($this->confDir.'myname');
	}
}

trait Rules
{
	protected function registerRulesCommands()
	{
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetRules'=>	array(
					'argv'	=>	array(FILEPATH, BOOL|NONE, BOOL|NONE),
					'desc'	=>	_('Get rules'),
					),

				'ParseRules'=>	array(
					'argv'	=>	array(JSON, BOOL|NONE),
					'desc'	=>	_('Parse rules'),
					),

				'GetRuleFiles'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get rule files'),
					),

				'DeleteRuleFile'=>	array(
					'argv'	=>	array(FILEPATH),
					'desc'	=>	_('Delete rule file'),
					),

				'InstallRules'=>	array(
					'argv'	=>	array(JSON, SAVEFILEPATH|NONE, BOOL|NONE, BOOL|NONE),
					'desc'	=>	_('Install rules'),
					),

				'GenerateRule'=>	array(
					'argv'	=>	array(JSON, NUM, BOOL|NONE),
					'desc'	=>	_('Generate rule'),
					),

				'GenerateRules'=>	array(
					'argv'	=>	array(JSON, BOOL|NONE, BOOL|NONE),
					'desc'	=>	_('Generate rules'),
					),

				'TestRules'=>	array(
					'argv'	=>	array(JSON),
					'desc'	=>	_('Test rules'),
					),
				)
			);
	}

	/**
	 * Reads, parses, and validates the rules in the given file.
	 *
	 * @param string $file Rules file.
	 * @param bool $tmp Whether the given rule file is a temporary uploaded file or not.
	 * @param bool $force Used to override validation or other types of errors, hence forces loading of rules.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function GetRules($file, $tmp= FALSE, $force= FALSE)
	{
		global $TMP_PATH, $TEST_DIR_PATH;

		if ($file !== "$this->ConfFile") {
			if (!$this->ValidateFilename($file)) {
				return FALSE;
			}
			if ($tmp == FALSE) {
				$file= "$this->ConfPath/$file";
			} else {
				$file= "$TMP_PATH/$file";
			}
		}

		$ruleStr= $this->GetFile("$TEST_DIR_PATH$file");

		if ($ruleStr !== FALSE) {
			/// @todo Check if we need to unlink tmp file
			//if ($tmp !== FALSE) {
			//	unlink($file);
			//}

			$retval= $this->_parseRules($ruleStr, $force);
		} else {
			$retval= FALSE;
		}

		return $retval;
	}

	function ParseRules($json, $force= FALSE)
	{
		$ruleStr= json_decode($json, TRUE);
		return $this->_parseRules($ruleStr, $force);
	}

	function _parseRules($ruleStr, $force= FALSE)
	{
		$class= $this->getNamespace().'RuleSet';
		$ruleSet= new $class();
		$retval= $ruleSet->parse($ruleStr, $force);

		// Output ruleset, success or fail
		Output(json_encode($ruleSet));

		return $retval;
	}

	/**
	 * Returns the file list under ConfPath.
	 * 
	 * @todo Should we return success or fail status, instead of TRUE?
	 *
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function GetRuleFiles()
	{
		global $TEST_DIR_PATH;

		return Output($this->GetFiles("$TEST_DIR_PATH$this->ConfPath"));
	}

	/**
	 * Deletes the given file under ConfPath.
	 * 
	 * Makes sure the file name is valid.
	 * Deletes only files under ConfPath. ValidateFilename() strips other file paths.
	 *
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function DeleteRuleFile($file)
	{
		global $TEST_DIR_PATH;

		$result= $this->ValidateFilename($file);

		if ($result) {
			$result= $this->DeleteFile("$TEST_DIR_PATH$this->ConfPath/$file");
		}

		return $result;
	}

	/**
	 * Reads, parses, and validates the rules in the given file.
	 * 
	 * @attention We never run pfctl if the rules fail validation. Hence $force can only
	 * force loading the rules, not running pfctl.
	 *
	 * @param string $json JSON encoded rules array.
	 * @param string $file File name to save to.
	 * @param bool $load Whether to load the rules using pfctl after saving.
	 * @param bool $force Used to override validation or other types of errors, hence forces loading of rules.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function InstallRules($json, $file= NULL, $load= TRUE, $force= FALSE)
	{
		global $INSTALL_USER, $TEST_DIR_PATH;

		if ($file == NULL) {
			$file= $this->ConfFile;
		} else {
			if (!$this->ValidateFilename($file)) {
				return FALSE;
			}
			$file= "$this->ConfPath/$file";
		}

		/// @todo Check if $rulesArray is in correct format
		$rulesArray= json_decode($json, TRUE);

		$class= $this->getNamespace().'RuleSet';
		$ruleSet= new $class();
		$loadResult= $ruleSet->load($rulesArray, $force);

		if (!$loadResult && !$force) {
			ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Will not generate rules with errors');
			return FALSE;
		}

		$rules= $ruleSet->generate();

		$output= array();
		$return= TRUE;

		$tmpFile= tempnam("$TEST_DIR_PATH/tmp", 'tmp.conf.');
		if ($this->PutFile($tmpFile, $rules) !== FALSE) {
			$SUFFIX_OPT= '-B';
			if (posix_uname()['sysname'] === 'Linux') {
				$SUFFIX_OPT= '-S';
			}

			exec("/usr/bin/install -o $INSTALL_USER -m 0600 -D -b $SUFFIX_OPT '.orig' '$tmpFile' $TEST_DIR_PATH$file 2>&1", $output, $retval);
			if ($retval === 0) {
				if ($load === TRUE) {
					if ($loadResult) {
						$cmd= preg_replace('/<FILE>/', $TEST_DIR_PATH.$file, $this->ReloadCmd);

						if (!$this->RunCmd($cmd, $output, $retval)) {
							Error(_('Failed loading rules') . ": $file");
							ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed loading rules: $file");
							$return= FALSE;
						}

						if ($retval !== 0) {
							Error(_('Cannot load rules') . "\n" . implode("\n", $output));
							ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot load rules');
							$return= FALSE;
						}
					} else {
						// Install button on the View is disabled if the ruleset has errors, so we should never reach here
						// But this method can be called on the command line too, that's why we check $loadResult
						Error(_('Will not load rules with errors') . ": $file");
						ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Will not load rules with errors: $file");
						$return= FALSE;
					}
				}
			} else {
				Error(_('Cannot install rule file') . ": $file\n" . implode("\n", $output));
				ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot install rule file: $file");
				$return= FALSE;
			}

			// Clean up after ourselves, even if there are errors
			exec("/bin/rm '$tmpFile' 2>&1", $output, $retval);
			if ($retval !== 0) {
				Error(_('Cannot remove tmp file') . ": $tmpFile\n" . implode("\n", $output));
				ctlr_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "Cannot remove tmp file: $tmpFile");
				$return= FALSE;
			}
		} else {
			Error(_('Cannot write to tmp file') . ": $tmpFile\n" . implode("\n", $output));
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot write to tmp file: $tmpFile");
			$return= FALSE;
		}

		return $return;
	}

	/**
	 * Validates the given file name.
	 * 
	 * Strips the file path, because we work with files under ConfPath only.
	 *
	 * @param string $file File name to validate [out].
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function ValidateFilename(&$file)
	{
		$file= basename($file);
		if (preg_match('/^[\w._\-]+$/', $file)) {
			return TRUE;
		}

		Error(_('Filename not accepted') . ": $file");
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Filename not accepted: $file");
		return FALSE;
	}

	/**
	 * Loads and generates the given JSON encoded rule array.
	 * 
	 * @param string $json JSON encoded rule array.
	 * @param int $ruleNumber Rule number.
	 * @param bool $force Used to override validation or other types of errors, hence forces loading of rules.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function GenerateRule($json, $ruleNumber, $force= FALSE)
	{
		$ruleDef= json_decode($json, TRUE);

		$cat= $this->getNamespace().$ruleDef['cat'];

		$ruleObj= new $cat('');
		$retval= $ruleObj->load($ruleDef['rule'], $ruleNumber, $force);

		if ($retval || $force) {
			Output($ruleObj->generate());
		} else {
			ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Will not generate rule with errors');
		}

		return $retval;
	}

	/**
	 * Loads and generates the given JSON encoded rules array.
	 * 
	 * @param string $json JSON encoded rules array.
	 * @param bool $lines Whether to print line numbers in front of each line.
	 * @param bool $force Used to override validation or other types of errors, hence forces loading of rules.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function GenerateRules($json, $lines= FALSE, $force= FALSE)
	{
		$rulesArray= json_decode($json, TRUE);

		$class= $this->getNamespace().'RuleSet';
		$ruleSet= new $class();
		$retval= $ruleSet->load($rulesArray, $force);

		if ($retval || $force) {
			Output($ruleSet->generate($lines));
		} else {
			ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Will not generate rules with errors');
		}

		return $retval;
	}

	/**
	 * Tests the given JSON encoded rules array.
	 * 
	 * Note that testing involves running pfctl, so there is no $force param here,
	 * because we never run pfctl with rules failed validation.
	 * 
	 * @param string $json JSON encoded rules array.
	 * @return bool Test result, TRUE on success, FALSE on fail.
	 */
	function TestRules($json)
	{
		$rv= FALSE;

		$rulesArray= json_decode($json, TRUE);

		$class= $this->getNamespace().'RuleSet';
		$ruleSet= new $class();
		if (!$ruleSet->load($rulesArray)) {
			Error(_('Will not test rules with errors'));
			ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Will not test rules with errors');
			return FALSE;
		}

		/// @attention pfctl reports line numbers, not rule numbers, so do not reduce multi-line rules into single-line
		$rulesStr= $ruleSet->generate();

		$cmd= $this->getTestRulesCmd($rulesStr, $tmpFile);

		if (!$this->RunCmd($cmd, $output, $retval)) {
			Error(_('Failed testing rules'));
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed testing rules');
			goto out;
		}

		if ($retval === 0) {
			$rv= TRUE;
			goto out;
		}

		$rules= explode("\n", $rulesStr);

		foreach ($output as $o) {
			if (preg_match('/^([^:]+):(\d+):\s*(.*)$/', $o, $match)) {
				$src= $match[1];
				$line= $match[2];
				$err= $match[3];

				// Rule numbers are 0 based, hence decrement once
				$line--;

				if ($src == 'stdin') {
					$rule= $rules[$line];
					Error(_('Line') . " $line: $err:\n<pre>" . htmlentities($rule) . '</pre>');
				} else {
					// Rule numbers in include files need an extra decrement
					$line--;
					Error(_('Error in include file') . ": $src\n" . _('Line') . " $line: $err");
				}
			} else {
				Error($o);
			}
		}
out:
		$rv&= $this->removeTmpTestFile($tmpFile);
		return $rv;
	}

	/**
	 * Daemonizes to run the given command.
	 * 
	 * We create a sysv message queue before forking the child process. The parent process
	 * waits for a message from the child. The child process runs the pfctl command, packs
	 * its output and return value in an array, and returns it in a message.
	 * 
	 * The parent loops waiting for a message from the child. In the loop we use a sleep interval
	 * obtained by an equation involving $PfctlTimeout, instead of a constant like 0.1,
	 * so that if $PfctlTimeout is set to 0, the interval becomes 0 too.
	 * 
	 * However, note that disabling the sleep interval may fail pfctl calls, because the parent
	 * exits without waiting for a message from the child.
	 * 
	 * pfctl takes a long time to return in certain cases. The WUI should not wait for too long,
	 * but exit upon timeout. In fact, all such external calls should timeout, instead of
	 * waiting indefinitely.
	 * 
	 * @bug pfctl gets stuck, or takes a long time to return on some errors.
	 * 
	 * Example 1: A macro using an unknown interface: int_if = "a1",
	 * pfctl tries to look up for its IP address, which takes a long time before failing with:
	 * > no IP address found for a1,
	 * > could not parse host specification
	 * 
	 * Example 2: A table with an entry (e.g. "test") for which no DNS record can be found,
	 * pfctl waits for name service lookup, which takes too long:
	 * > no IP address found for test,
	 * > could not parse host specification
	 * Therefore, we need to use a function which returns upon timeout, hence this method.
	 * 
	 * @param string $cmd command to run.
	 * @param array $output Output of cmd.
	 * @param int $retval Return value of cmd.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function RunCmd($cmd, &$output, &$retval)
	{
		global $PfctlTimeout;

		$retval= 0;
		$output= array();

		/// @todo Check why using 0 as mqid eventually (30-50 accesses later) fails creating or attaching to the queue.
		$mqid= 1;

		// Create or attach to the queue before forking
		$queue= msg_get_queue($mqid);

		if (!msg_queue_exists($mqid)) {
			Error(_('Failed creating or attaching to message queue'));
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed creating or attaching to message queue');
			return FALSE;
		}

		$sendtype= 1;

		$pid= pcntl_fork();

		if ($pid == -1) {
			Error(_('Cannot fork process'));
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot fork process');
		} elseif ($pid) {
			// This is the parent!

			$return= FALSE;

			// Parent should wait for output for $PfctlTimeout seconds
			// Wait count starts from 1 due to do..while loop
			$count= 1;

			// We use this $interval var instead of a constant like .1, because
			// if $PfctlTimeout is set to 0, $interval becomes 0 too, effectively disabling sleep
			// Add 1 to prevent division by zero ($PfctlTimeout cannot be set to -1 on the WUI)
			$interval= $PfctlTimeout/($PfctlTimeout + 1)/10;

			do {
				exec("/bin/sleep $interval");
				ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Receive message wait count: $count, sleep interval: $interval");

				/// @attention Do not wait for a message, loop instead: MSG_IPC_NOWAIT
				$received= msg_receive($queue, 0, $recvtype, 10000, $msg, TRUE, MSG_NOERROR|MSG_IPC_NOWAIT, $error);

				if ($received && $sendtype == $recvtype) {
					if (is_array($msg) && array_key_exists('retval', $msg) && array_key_exists('output', $msg)) {
						$retval= $msg['retval'];
						$output= $msg['output'];

						ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Received cmd output: ' . print_r($msg, TRUE));

						$return= TRUE;
						break;
					} else {
						Error(_('Output not in correct format') . ': ' . print_r($msg, TRUE));
						ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Output not in correct format: ' . print_r($msg, TRUE));
						break;
					}
				} else {
					ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Failed receiving cmd output: ' . posix_strerror($error));
				}

			} while ($count++ < $PfctlTimeout * 10);

			if (!$return) {
				Error(_('Timed out running command'));
				ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Timed out running command');
			}

			// Parent removes the queue
			if (!msg_remove_queue($queue)) {
				Error(_('Failed removing message queue'));
				ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed removing message queue');
			}

			/// @attention Make sure the child is terminated, otherwise the parent gets stuck too.
			if (posix_getpgid($pid)) {
				exec("/bin/kill -KILL $pid");
			}

			// Parent survives
			return $return;
		} else {
			// This is the child!

			// Child should run the command and send the result in a message
			ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Running command');
			exec($cmd, $output, $retval);

			$msg= array(
				'retval' => $retval,
				'output' => $output
				);

			if (!msg_send($queue, $sendtype, $msg, TRUE, TRUE, $error)) {
				ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed sending output: ' . print_r($msg, TRUE) . ', error: ' . posix_strerror($error));
			} else {
				ctlr_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Sent output: ' . print_r($msg, TRUE));
			}

			// Child exits
			exit;
		}
	}

	protected function getNamespace()
	{
		return 'Model\\';
	}

	abstract protected function getTestRulesCmd($rulesStr, &$tmpFile);

	protected function removeTmpTestFile($tmpFile)
	{
		return TRUE;
	}
}
?>

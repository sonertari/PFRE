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
 * Contains Pf class to run pf tasks.
 */

use Model\RuleSet;

require_once($MODEL_PATH.'/model.php');

class Pf extends Model
{
	function __construct()
	{
		parent::__construct();
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetPfRules'=>	array(
					'argv'	=>	array(FILEPATH, BOOL|NONE, BOOL|NONE),
					'desc'	=>	_('Get pf rules'),
					),
				
				'GetPfRuleFiles'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get pf rule files'),
					),
				
				'DeletePfRuleFile'=>	array(
					'argv'	=>	array(FILEPATH),
					'desc'	=>	_('Delete pf rule file'),
					),

				'InstallPfRules'=>	array(
					'argv'	=>	array(JSON, SAVEFILEPATH|NONE, BOOL|NONE, BOOL|NONE),
					'desc'	=>	_('Install pf rules'),
					),
				
				'GeneratePfRule'=>	array(
					'argv'	=>	array(JSON, NUM, BOOL|NONE),
					'desc'	=>	_('Generate pf rule'),
					),

				'GeneratePfRules'=>	array(
					'argv'	=>	array(JSON, BOOL|NONE, BOOL|NONE),
					'desc'	=>	_('Generate pf rules'),
					),

				'TestPfRules'=>	array(
					'argv'	=>	array(JSON),
					'desc'	=>	_('Test pf rules'),
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
	function GetPfRules($file, $tmp= FALSE, $force= FALSE)
	{
		global $PF_CONFIG_PATH, $TMP_PATH, $TEST_DIR_PATH;

		if ($file !== '/etc/pf.conf') {
			if (!$this->ValidateFilename($file)) {
				return FALSE;
			}
			if ($tmp == FALSE) {
				$file= "$PF_CONFIG_PATH/$file";
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

			$ruleSet= new RuleSet();
			$retval= $ruleSet->parse($ruleStr, $force);

			// Output ruleset, success or fail
			Output(json_encode($ruleSet));
		} else {
			$retval= FALSE;
		}

		return $retval;
	}

	/**
	 * Returns the file list under $PF_CONFIG_PATH.
	 * 
	 * @todo Should we return success or fail status, instead of TRUE?
	 *
	 * @return bool TRUE always.
	 */
	function GetPfRuleFiles()
	{
		global $PF_CONFIG_PATH, $TEST_DIR_PATH;

		Output($this->GetFiles("$TEST_DIR_PATH$PF_CONFIG_PATH"));
		return TRUE;
	}
	
	/**
	 * Deletes the given file under $PF_CONFIG_PATH.
	 * 
	 * Makes sure the file name is valid.
	 * Deletes only files under $PF_CONFIG_PATH. ValidateFilename() strips other file paths.
	 *
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function DeletePfRuleFile($file)
	{
		global $PF_CONFIG_PATH, $TEST_DIR_PATH;

		$result= $this->ValidateFilename($file);

		if ($result) {
			$result= $this->DeleteFile("$TEST_DIR_PATH$PF_CONFIG_PATH/$file");
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
	function InstallPfRules($json, $file= NULL, $load= TRUE, $force= FALSE)
	{
		global $PF_CONFIG_PATH, $INSTALL_USER, $TEST_DIR_PATH;

		if ($file == NULL) {
			$file= '/etc/pf.conf';
		} else {
			if (!$this->ValidateFilename($file)) {
				return FALSE;
			}
			$file= "$PF_CONFIG_PATH/$file";
		}
				
		/// @todo Check if $rulesArray is in correct format
		$rulesArray= json_decode($json, TRUE);

		$ruleSet= new RuleSet();
		$loadResult= $ruleSet->load($rulesArray, $force);

		if (!$loadResult && !$force) {
			pfrec_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Will not generate rules with errors');
			return FALSE;
		}

		$rules= $ruleSet->generate();

		$output= array();
		$return= TRUE;
		
		$tmpFile= tempnam("$TEST_DIR_PATH/tmp", 'pf.conf.');
		if ($this->PutFile($tmpFile, $rules) !== FALSE) {
			$SUFFIX_OPT= '-B';
			if (posix_uname()['sysname'] === 'Linux') {
				$SUFFIX_OPT= '-S';
			}

			exec("/usr/bin/install -o $INSTALL_USER -m 0600 -D -b $SUFFIX_OPT '.orig' '$tmpFile' $TEST_DIR_PATH$file 2>&1", $output, $retval);
			if ($retval === 0) {
				if ($load === TRUE) {
					if ($loadResult) {
						$cmd= "/sbin/pfctl -f $TEST_DIR_PATH$file 2>&1";

						if (!$this->RunPfctlCmd($cmd, $output, $retval)) {
							Error(_('Failed loading pf rules') . ": $file");
							pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed loading pf rules: $file");
							$return= FALSE;
						}

						if ($retval !== 0) {
							Error(_('Cannot load pf rules') . "\n" . implode("\n", $output));
							pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot load pf rules');
							$return= FALSE;
						}
					} else {
						// Install button on the View is disabled if the ruleset has errors, so we should never reach here
						// But this method can be called on the command line too, that's why we check $loadResult
						Error(_('Will not load rules with errors') . ": $file");
						pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Will not load rules with errors: $file");
						$return= FALSE;
					}
				}
			} else {
				Error(_('Cannot install pf rule file') . ": $file\n" . implode("\n", $output));
				pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot install pf rule file: $file");
				$return= FALSE;
			}

			// Clean up after ourselves, even if there are errors
			exec("/bin/rm '$tmpFile' 2>&1", $output, $retval);
			if ($retval !== 0) {
				Error(_('Cannot remove tmp pf file') . ": $tmpFile\n" . implode("\n", $output));
				pfrec_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "Cannot remove tmp pf file: $tmpFile");
				$return= FALSE;
			}
		} else {
			Error(_('Cannot write to tmp pf file') . ": $tmpFile\n" . implode("\n", $output));
			pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot write to tmp pf file: $tmpFile");
			$return= FALSE;
		}
		
		return $return;
	}

	/**
	 * Validates the given file name.
	 * 
	 * Strips the file path, because we work with files under $PF_CONFIG_PATH only.
	 *
	 * @param string $file File name to validate.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function ValidateFilename(&$file)
	{
		$file= basename($file);
		if (preg_match('/^[\w._\-]+$/', $file)) {
			return TRUE;
		}

		Error(_('Filename not accepted') . ": $file");
		pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Filename not accepted: $file");
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
	function GeneratePfRule($json, $ruleNumber, $force= FALSE)
	{
		$ruleDef= json_decode($json, TRUE);

		$cat= 'Model\\' . $ruleDef['cat'];
		$ruleObj= new $cat('');
		$retval= $ruleObj->load($ruleDef['rule'], $ruleNumber, $force);

		if ($retval || $force) {
			Output($ruleObj->generate());
		} else {
			pfrec_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Will not generate rule with errors');
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
	function GeneratePfRules($json, $lines= FALSE, $force= FALSE)
	{
		$rulesArray= json_decode($json, TRUE);

		$ruleSet= new RuleSet();
		$retval= $ruleSet->load($rulesArray, $force);

		if ($retval || $force) {
			Output($ruleSet->generate($lines));
		} else {
			pfrec_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Will not generate rules with errors');
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
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function TestPfRules($json)
	{
		$rulesArray= json_decode($json, TRUE);

		$ruleSet= new RuleSet();
		if (!$ruleSet->load($rulesArray)) {
			Error(_('Will not test rules with errors'));
			pfrec_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Will not test rules with errors');
			return FALSE;
		}

		/// @attention pfctl reports line numbers, not rule numbers, so do not reduce multi-line rules into single-line
		$rulesStr= $ruleSet->generate();

		$cmd= "/bin/echo '$rulesStr' | /sbin/pfctl -nf - 2>&1";

		if (!$this->RunPfctlCmd($cmd, $output, $retval)) {
			Error(_('Failed testing pf rules'));
			pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed testing pf rules');
			return FALSE;
		}

		if ($retval === 0) {
			return TRUE;
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
		return FALSE;
	}

	/**
	 * Daemonizes to run the given pfctl command.
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
	 * @param string $cmd pfctl command to run.
	 * @param string $output Output of pfctl.
	 * @param int $retval Return value of pfctl.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function RunPfctlCmd($cmd, &$output, &$retval)
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
			pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed creating or attaching to message queue');
			return FALSE;
		}
		
		$sendtype= 1;

		$pid= pcntl_fork();

		if ($pid == -1) {
			Error(_('Cannot fork pfctl process'));
			pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot fork pfctl process');
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
				pfrec_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Receive message wait count: $count, sleep interval: $interval");

				/// @attention Do not wait for a message, loop instead: MSG_IPC_NOWAIT
				$received= msg_receive($queue, 0, $recvtype, 10000, $msg, TRUE, MSG_NOERROR|MSG_IPC_NOWAIT, $error);

				if ($received && $sendtype == $recvtype) {
					if (is_array($msg) && array_key_exists('retval', $msg) && array_key_exists('output', $msg)) {
						$retval= $msg['retval'];
						$output= $msg['output'];

						pfrec_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Received pfctl output: ' . print_r($msg, TRUE));

						$return= TRUE;
						break;
					} else {
						Error(_('Output not in correct format') . ': ' . print_r($msg, TRUE));
						pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Output not in correct format: ' . print_r($msg, TRUE));
						break;
					}
				} else {
					pfrec_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Failed receiving pfctl output: ' . posix_strerror($error));
				}

			} while ($count++ < $PfctlTimeout * 10);

			if (!$return) {
				Error(_('Timed out running pfctl command'));
				pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Timed out running pfctl command');
			}

			// Parent removes the queue
			if (!msg_remove_queue($queue)) {
				Error(_('Failed removing message queue'));
				pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed removing message queue');
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
			pfrec_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Running pfctl command');
			exec($cmd, $output, $retval);

			$msg= array(
				'retval' => $retval,
				'output' => $output
				);

			if (!msg_send($queue, $sendtype, $msg, TRUE, TRUE, $error)) {
				pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed sending pfctl output: ' . print_r($msg, TRUE) . ', error: ' . posix_strerror($error));
			} else {
				pfrec_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Sent pfctl output: ' . print_r($msg, TRUE));
			}

			// Child exits
			exit;
		}
	}
}
?>

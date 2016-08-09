<?php
/* $pfre: pf.php,v 1.19 2016/08/08 15:48:29 soner Exp $ */

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

	function GetPfRules($file, $tmp= FALSE, $force= FALSE)
	{
		global $PF_CONFIG_PATH, $TMP_PATH;

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

		$ruleStr= $this->GetFile($file);

		/// @todo Check if we need to unlink tmp file
		//if ($tmp !== FALSE) {
		//	unlink($file);
		//}

		$ruleSet= new RuleSet();
		$retval= $ruleSet->parse($ruleStr, $force);

		// Output ruleset, success or fail
		Output(json_encode($ruleSet));
		return $retval;
	}

	function GetPfRuleFiles()
	{
		global $PF_CONFIG_PATH;

		Output($this->GetFiles($PF_CONFIG_PATH));
		return TRUE;
	}
	
	function DeletePfRuleFile($file)
	{
		global $PF_CONFIG_PATH;

		if ($this->ValidateFilename($file)) {
			Output($this->DeleteFile("$PF_CONFIG_PATH/$file"));
			return TRUE;
		}
		return FALSE;
	}
	
	function InstallPfRules($json, $file= NULL, $load= TRUE, $force= FALSE)
	{
		global $PF_CONFIG_PATH;

		if ($file == NULL) {
			$file= '/etc/pf.conf';
		} else {
			if (!$this->ValidateFilename($file)) {
				return FALSE;
			}
			$file= "$PF_CONFIG_PATH/$file";
		}
				
		$rulesArray= json_decode($json, TRUE);

		$ruleSet= new RuleSet();
		if (!$ruleSet->load($rulesArray, $force) && !$force) {
			pfrec_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Will not generate rules with errors');
			return FALSE;
		}

		$rules= $ruleSet->generate();

		$output= array();
		
		$tmpFile= tempnam('/tmp', 'pf.conf.');
		if ($this->PutFile($tmpFile, $rules) !== FALSE) {
			exec("/usr/bin/install -o root -m 0600 -D -b -B '.orig' '$tmpFile' $file 2>&1", $output, $retval);
			if ($retval === 0) {
				if ($load === TRUE) {
					$cmd= "/sbin/pfctl -f $file 2>&1";

					if (!$this->RunPfctlCmd($cmd, $output, $retval)) {
						pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, Error("Failed loading pf rules: $file"));
						return FALSE;
					}

					if ($retval !== 0) {
						$err= 'Cannot load pf rules';
					}
				}
			} else {
				$err= "Cannot install pf rule file: $file";
			}

			exec("/bin/rm '$tmpFile' 2>&1", $output2, $retval);
			if ($retval !== 0) {
				$err2= "Cannot remove tmp pf file: $tmpFile";
				Error($err2 . "\n" . implode("\n", $output2));
				pfrec_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, $err2);
			}
			
			if (!isset($err) && !isset($err2)) {
				return TRUE;
			}
		} else {
			$err= "Cannot write to tmp pf file: $tmpFile";
		}
		
		if (isset($err)) {
			Error($err . "\n" . implode("\n", $output));
			pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, Error($err));
		}
		return FALSE;
	}

	function ValidateFilename(&$file)
	{
		$file= basename($file);
		if (preg_match('/[\w.-_]+/', $file)) {
			return TRUE;
		}

		$err= "Filename not accepted: $file";
		Error($err . "\n" . implode("\n", $output));
		pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, $err);
		return FALSE;
	}

	function GeneratePfRule($json, $ruleNumber, $force= FALSE)
	{
		$ruleDef= json_decode($json, TRUE);

		$class= $ruleDef['cat'];
		$ruleObj= new $class('');
		$retval= $ruleObj->load($ruleDef['rule'], $ruleNumber, $force);
		if ($retval || $force) {
			Output($ruleObj->generate());
		} else {
			pfrec_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Will not generate rule with errors');
		}

		return $retval;
	}

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

	function TestPfRules($json)
	{
		$rulesArray= json_decode($json, TRUE);

		$ruleSet= new RuleSet();
		if (!$ruleSet->load($rulesArray)) {
			pfrec_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, Error('Will not test rules with errors'));
			return FALSE;
		}

		$rulesStr= $ruleSet->generate(FALSE, NULL, TRUE, TRUE);

		$cmd= "/bin/echo '$rulesStr' | /sbin/pfctl -nf - 2>&1";

		if (!$this->RunPfctlCmd($cmd, $output, $retval)) {
			pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, Error('Failed testing pf rules'));
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
					Error("$line: $err:\n<pre>" . htmlentities($rule) . '</pre>');
				} else {
					// Rule numbers in include files need an extra decrement
					$line--;
					Error("Error in include file: $src\n$line: $err");
				}
			} else {
				Error($o);
			}
		}
		return FALSE;
	}

	/** Daemonizes to run the given pfctl command.
	 */
	function RunPfctlCmd($cmd, &$output, &$retval)
	{
		global $PfctlTimeout;

		/// @bug pfctl gets stuck, or takes a long time to return on some errors
		// Example 1: A macro using an unknown interface: int_if = "a1",
		// pfctl tries to look up for its IP address, which takes a long time before failing with:
		// > no IP address found for a1
		// > could not parse host specification
		// Example 2: A table with an entry for which no DNS record can be found
		// pfctl waits for name service lookup, which takes too long:
		// > no IP address found for test
		// > could not parse host specification
		// Therefore, need to use a function which returns upon timeout, hence this method

		$retval= 0;
		$output= array();

		/// @todo Check why using 0 as mqid eventually (30-50 accesses later) fails creating or attaching to the queue
		$mqid= 1;

		// Create or attach to the queue before forking
		$queue= msg_get_queue($mqid);
		
		if (!msg_queue_exists($mqid)) {
			pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, Error('Failed creating or attaching to message queue'));
			return FALSE;
		}
		
		$sendtype= 1;

		$pid= pcntl_fork();

		if ($pid == -1) {
			pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, Error('Cannot fork pfctl process'));
		} elseif ($pid) {
			// This is the parent!

			$return= FALSE;

			// Parent should wait for output for $PfctlTimeout seconds
			// Wait count starts from 1 due to do..while loop
			$count= 1;

			// Add 1 to prevent division by zero
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

						pfrec_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Received pfctl output: $msg");

						$return= TRUE;
						break;
					} else {
						pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, Error("Output not in correct format: $msg"));
						break;
					}
				} else {
					pfrec_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Failed receiving pfctl output: ' . posix_strerror($error));
				}

			} while ($count++ < $PfctlTimeout * 10);

			if (!$return) {
				pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, Error('Timed out running pfctl command'));
			}

			// Parent removes the queue
			if (!msg_remove_queue($queue)) {
				pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, Error('Failed removing message queue'));
			}

			/// @attention Make sure the child is terminated, otherwise the parent gets stuck too
			exec("/bin/kill -KILL $pid");

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
				pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed sending pfctl output: ' . $msg . ', error: ' . posix_strerror($error));
			} else {
				pfrec_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Sent pfctl output: ' . $msg);
			}

			// Child exits
			exit;
		}
	}
}
?>

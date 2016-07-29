<?php
/* $pfre: pf.php,v 1.45 2016/07/25 10:18:38 soner Exp $ */

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
					'argv'	=>	array(FILEPATH|NONE),
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
					'argv'	=>	array(SERIALARRAY, SAVEFILEPATH|NONE, BOOL|NONE),
					'desc'	=>	_('Install pf rules'),
					),
				
				'TestPfRules'=>	array(
					'argv'	=>	array(SERIALARRAY, NUM|NONE, NUM|NONE),
					'desc'	=>	_('Test pf rules'),
					),
				)
			);
	}

	function GetPfRules($filename= NULL)
	{
		global $PF_CONFIG_PATH;

		if ($filename == NULL) {
			$filename= '/etc/pf.conf';
		} else {
			if (!$this->ValidateFilename($filename)) {
				return FALSE;
			}
			$filename= "$PF_CONFIG_PATH/$filename";
		}
		return $this->GetFile($filename);
	}

	function GetPfRuleFiles()
	{
		global $PF_CONFIG_PATH;

		return $this->GetFiles($PF_CONFIG_PATH);
	}
	
	function DeletePfRuleFile($filename)
	{
		global $PF_CONFIG_PATH;

		if (!$this->ValidateFilename($filename)) {
			return FALSE;
		}
		return $this->DeleteFile("$PF_CONFIG_PATH/$filename");
	}
	
	function InstallPfRules($rules, $filename= NULL, $load= TRUE)
	{
		global $PF_CONFIG_PATH;

		if ($filename == NULL) {
			$filename= '/etc/pf.conf';
		} else {
			if (!$this->ValidateFilename($filename)) {
				return FALSE;
			}
			$filename= "$PF_CONFIG_PATH/$filename";
		}
				
		$rules= unserialize($rules)[0];
		
		$logLevel= LOG_ERR;
		$output= array();
		
		$tempFile= tempnam('/tmp', 'pf.conf.');
		if ($this->PutFile($tempFile, $rules) !== FALSE) {
			exec("/usr/bin/install -o root -m 0600 -D -b -B '.orig' '$tempFile' $filename 2>&1", $output, $retval);
			if ($retval === 0) {
				if ($load === TRUE) {
					exec("/sbin/pfctl -f $filename 2>&1", $output, $retval);
					if ($retval !== 0) {
						$err= 'Cannot load pf rules';
					}
				}
			} else {
				$err= "Cannot install pf rule file: $filename";
			}

			exec("/bin/rm '$tempFile' 2>&1", $output2, $retval);
			if ($retval !== 0) {
				$err2= "Cannot remove temp pf file: $tempFile";
				ViewError($err2 . "\n" . implode("\n", $output2));
				pfrec_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, $err2);
			}
			
			if (!isset($err) && !isset($err2)) {
				return TRUE;
			}
		} else {
			$err= "Cannot write to temp pf file: $tempFile";
		}
		
		if (isset($err)) {
			ViewError($err . "\n" . implode("\n", $output));
			pfrec_syslog($logLevel, __FILE__, __FUNCTION__, __LINE__, $err);
		}
		return FALSE;
	}

	function ValidateFilename(&$filename)
	{
		$filename= basename($filename);
		if (!preg_match('/[\w.-_]+/', $filename)) {
			$err= "Filename not accepted: $filename";
			ViewError($err . "\n" . implode("\n", $output));
			pfrec_syslog($logLevel, __FILE__, __FUNCTION__, __LINE__, $err);
			return FALSE;
		}
		return TRUE;
	}

	function TestPfRules($rules, $offset= 0, $ruleNumber= -1)
	{
		$rules= unserialize($rules)[0];
		$cmd= "/bin/echo '$rules' | /sbin/pfctl -nf - 2>&1";
		
		/// @todo pfctl gets stuck and takes a long time to return on some errors
		// Example 1: A macro using an unknown interface: int_if = "a1",
		// pfctl tries to look up for its IP address, which takes a long time before failing with:
		// > no IP address found for a1
		// > could not parse host specification
		// Example 2: A table with an entry for which no DNS record can be found
		// pfctl waits for name service lookup, which takes too long:
		// > no IP address found for test
		// > could not parse host specification
		// Therefore, need to use an exec function which returns with timeout
		exec($cmd, $output, $retval);

		$rv= TRUE;
		if ($retval === 0) {
			return $rv;
		}
		
		$rulesArray= explode("\n", $rules);
		
		$lastLine= 0;
		foreach ($output as $o) {
			if (preg_match('/stdin:(\d+):\s*(.*)/', $o, $match)) {
				$line= $match[1];
				$err= $match[2];
				
				// Rule numbers are 0 based, hence decrement once
				$line--;
				$rule= $rulesArray[$line];
				
				$line-= $offset;
				
				if ($lastLine < 0 && $line >= 0) {
					// Insert a newline between context and current rules
					ViewError('');
				}
		
				$n= $line;
				if ($ruleNumber >= 0) {
					// Edit pages provide the rule number separately
					$n= $ruleNumber;
				}
				
				if ($line >= 0) {
					ViewError($n . ': ' . $err . ":\n<code>	" . $rule . '</code>');
					$rv= FALSE;
				} else {
					// Negative rule number means that the error is in the other types of rules, i.e. in the context
					// Hence, do not display the rule number
					ViewError($err);
					/// @attention Do not set $rv to FALSE here, we are not interested in errors in context rules
				}
								
				$lastLine= $line;
			}
		}
		return $rv;
	}
}
?>

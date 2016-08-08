<?php
/* $pfre: RuleSet.php,v 1.25 2016/08/08 15:48:29 soner Exp $ */

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

class RuleSet
{
	public $filename= '';
	public $rules= array();
			
	function load($filename, $tmp= 0, $force= 0, $tmpFilename= '')
	{
		global $View;

		$retval= TRUE;
		if ($filename == '/etc/pf.conf') {
			$retval= $View->Controller($Output, 'GetPfRules', $filename, 0, $force);
		} else {
			$retval= $View->Controller($Output, 'GetPfRules', $filename, $tmp, $force);
		}

		if ($retval !== FALSE || $force) {
			$this->filename= $filename;
			if ($tmp && $tmpFilename !== '') {
				// Mark uploaded files
				$this->filename= "$tmpFilename (uploaded)";
			}
			$rulesArray= json_decode($Output[0], TRUE)['rules'];
		} else {
			return FALSE;
		}

		$this->deleteRules();
		foreach ($rulesArray as $ruleDef) {
			$class= $ruleDef['cat'];
			$ruleObj= new $class();
			$ruleObj->rule= $ruleDef['rule'];
			$this->rules[]= $ruleObj;
		}
		return TRUE;
	}
	
	function deleteRules()
	{
		$this->rules= array();
	}
	
	function up($ruleNumber)
	{
		if (isset($this->rules[$ruleNumber - 1])) {
			$tmp= $this->rules[$ruleNumber - 1];
			$this->rules[$ruleNumber - 1]= $this->rules[$ruleNumber];
			$this->rules[$ruleNumber]= $tmp;
		}
	}
	
	function down($ruleNumber)
	{
		if (isset($this->rules[$ruleNumber + 1])) {
			$tmp= $this->rules[$ruleNumber + 1];
			$this->rules[$ruleNumber + 1]= $this->rules[$ruleNumber];
			$this->rules[$ruleNumber]= $tmp;
		}
	}
	
	function del($ruleNumber)
	{
		/// @todo No need for a separate function now
		unset($this->rules[$ruleNumber]);
		// Fake slice to update the keys
		$this->rules= array_slice($this->rules, 0);
	}
	
	function move($ruleNumber, $moveTo)
	{
		if ($ruleNumber < 0 || $ruleNumber >= count($this->rules)) {
			PrintHelpWindow(_NOTICE('FAILED').': '."Invalid rule number $ruleNumber", 'auto', 'ERROR');
			return;
		}
		if ($moveTo < 0 || $moveTo >= count($this->rules) || $ruleNumber == $moveTo) {
			PrintHelpWindow(_NOTICE('FAILED').': '."Invalid destination rule number: $moveTo", 'auto', 'ERROR');
			return;
		}

		$rule= $this->rules[$ruleNumber];
		unset($this->rules[$ruleNumber]);
		// array_slice() takes care of possible off-by-one error due to unset above
		$head= array_slice($this->rules, 0, $moveTo);
		$tail= array_slice($this->rules, $moveTo);
		$this->rules= array_merge($head, array($rule), $tail);
	}
	
	function add($ruleNumber= 0)
	{
		if (count($this->rules) == 0 || ($ruleNumber >= $this->nextRuleNumber())) {
			// Add the first rule or append a new one to the end
			array_push($this->rules, array());
			return $this->nextRuleNumber();
		} else {
			// Preserve the keys for diff
			$tail= array_slice($this->rules, $ruleNumber, NULL, TRUE);
			$head= array_diff_key($this->rules, $tail);

			// Insert a new rule in the middle
			array_push($head, array());
			$this->rules= array_merge($head, $tail);
			return $ruleNumber;
		}
	}
	
	function computeNewRuleNumber($ruleNumber= 0)
	{
		if (count($this->rules) == 0 || ($ruleNumber >= $this->nextRuleNumber())) {
			// Add the first rule or append a new one to the end
			return $this->nextRuleNumber();
		} else {
			// Insert a new rule in the middle
			return $ruleNumber;
		}
	}
		
	function nextRuleNumber()
	{
		return count($this->rules);
	}
	
	function setupEditSession($cat, &$action, &$ruleNumber)
	{
		global $View;

		// Make sure we deal with possible rule numbers only
		if (!array_key_exists($ruleNumber, $View->RuleSet->rules)) {
			$ruleNumber= $this->computeNewRuleNumber($ruleNumber);
			if ($action == 'edit') {
				$action= 'add';
			}
		}

		if ($action == 'create') {
			if (!isset($_SESSION['edit']['object']) || $_SESSION['edit']['object']->cat!= $cat || $_SESSION['edit']['ruleNumber'] != $ruleNumber) {
				$action= 'add';
			}
		}

		if ($action == 'edit') {
			if (!isset($_SESSION['edit']['object']) || $_SESSION['edit']['object']->cat != $cat || $_SESSION['edit']['ruleNumber'] != $ruleNumber ||
				$View->RuleSet->rules[$ruleNumber]->cat != $cat) {
				// The rule being edited has changed, setup a new edit session
				if (array_key_exists($ruleNumber, $View->RuleSet->rules)) {
					// Rule exists, so clone from the ruleset
					unset($_SESSION['edit']);
					$_SESSION['edit']['ruleNumber']= $ruleNumber;
					$_SESSION['edit']['object']= clone $this->rules[$ruleNumber];
				} elseif (!isset($_SESSION['edit'])) {
					// @attention Add and del operations on multi-valued vars use GET method, so check if there is an active edit session
					// Rule does not exists, assume add if we are not already editing a new rule
					// Assume a new rule requested, if the page is submitted on the address line with a non-existing rule number
					$action= 'add';
				}
			}
		}

		if ($action == 'add') {
			// Create a new rule and setup a new edit session
			// Change action state to create, so we don't come back here to reinit session
			$action= 'create';
			unset($_SESSION['edit']);
			$_SESSION['edit']['ruleNumber']= $ruleNumber;
			$_SESSION['edit']['object']= new $cat('');
		}
	}

	function test($ruleNumber, $ruleObj)
	{
		global $View;
		
		$rulesArray= array_slice(json_decode(json_encode($this), TRUE)['rules'], 0, $ruleNumber);
		$rulesArray[]= json_decode(json_encode($ruleObj), TRUE);

		return $View->Controller($Output, 'TestPfRules', json_encode($rulesArray));
	}
	
	function cancel()
	{
		if (filter_has_var(INPUT_POST, 'cancel') && (filter_input(INPUT_POST, 'cancel') == 'Cancel')) {
			unset($_SESSION['edit']);
			header('Location: /pf/conf.php');
			exit;
		}
	}
	
	function save($action, $ruleNumber, $ruleObj, $testResult)
	{
		if (filter_has_var(INPUT_POST, 'save') && filter_input(INPUT_POST, 'save') == 'Save') {
			if ($testResult || filter_input(INPUT_POST, 'forcesave')) {
				if ($action == 'create') {
					$this->add($ruleNumber);
				}
				$this->rules[$ruleNumber]= $ruleObj;
				unset($_SESSION['edit']);
				header('Location: /pf/conf.php');
				exit;
			}
		}
	}
	
	function isModified($action, $ruleNumber, $ruleObj)
	{
		$modified= TRUE;
		if ($action != 'create') {
			// Make sure keys are sorted before comparison
			$newRule= $ruleObj->rule;
			ksort($newRule);

			$origRule= $this->rules[$ruleNumber]->rule;
			ksort($origRule);

			if (serialize($newRule) === serialize($origRule)) {
				$modified= FALSE;
			}
		}
		return $modified;
	}
	
	function getQueueNames() {
		$queues= array();
		foreach ($this->rules as $ruleObj) {
			if  ($ruleObj->cat == 'Queue') {
				$queues[]= $ruleObj->rule['name'];
			}
		}
		return $queues;
	}
}
?>
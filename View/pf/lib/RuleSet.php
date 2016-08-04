<?php
/* $pfre: RuleSet.php,v 1.17 2016/08/04 01:19:31 soner Exp $ */

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
	
	function __construct($View, $filename= '/etc/pf.conf', $tmp= 0) {
		$retval= TRUE;

		if ($filename == '/etc/pf.conf') {
			$retval= $View->Controller($Output, 'GetPfRules');
		} else {
			$retval= $View->Controller($Output, 'GetPfRules', $filename, $tmp);
		}

		if ($retval !== FALSE) {
			$this->filename= $filename;

			$rulesArray= json_decode($Output[0], TRUE)['rules'];
			$this->load($rulesArray);
			return TRUE;
		}
		return FALSE;
	}
	
	function load($rulesArray)
	{
		$this->deleteRules();
		foreach ($rulesArray as $ruleDef) {
			$class= $ruleDef['cat'];
			$ruleObj= new $class();
			$ruleObj->rule= $ruleDef['rule'];
			$this->rules[]= $ruleObj;
		}
	}
	
	function deleteRules()
	{
		$this->rules= array();
	}
	
	function up($rulenumber)
	{
		if (isset($this->rules[$rulenumber - 1])) {
			$tmp= $this->rules[$rulenumber - 1];
			$this->rules[$rulenumber - 1]= $this->rules[$rulenumber];
			$this->rules[$rulenumber]= $tmp;
		}
	}
	
	function down($rulenumber)
	{
		if (isset($this->rules[$rulenumber + 1])) {
			$tmp= $this->rules[$rulenumber + 1];
			$this->rules[$rulenumber + 1]= $this->rules[$rulenumber];
			$this->rules[$rulenumber]= $tmp;
		}
	}
	
	function del($rulenumber)
	{
		/// @todo No need for a separate function now
		unset($this->rules[$rulenumber]);
		// Fake slice to update the keys
		$this->rules= array_slice($this->rules, 0);
	}
	
	function move($rulenumber, $moveto)
	{
		if ($rulenumber < 0 || $rulenumber >= count($this->rules)) {
			PrintHelpWindow(_NOTICE('FAILED').': '."Invalid rule number $rulenumber", 'auto', 'ERROR');
			return;
		}
		if ($moveto < 0 || $moveto >= count($this->rules) || $rulenumber == $moveto) {
			PrintHelpWindow(_NOTICE('FAILED').': '."Invalid destination rule number: $moveto", 'auto', 'ERROR');
			return;
		}

		$rule= $this->rules[$rulenumber];
		unset($this->rules[$rulenumber]);
		// array_slice() takes care of possible off-by-one error due to unset above
		$head= array_slice($this->rules, 0, $moveto);
		$tail= array_slice($this->rules, $moveto);
		$this->rules= array_merge($head, array($rule), $tail);
	}
	
	function add($rulenumber= 0)
	{
		if (count($this->rules) == 0 || ($rulenumber >= $this->nextRuleNumber())) {
			// Add the first rule or append a new one to the end
			array_push($this->rules, array());
			return $this->nextRuleNumber();
		} else {
			// Preserve the keys for diff
			$tail= array_slice($this->rules, $rulenumber, NULL, TRUE);
			$head= array_diff_key($this->rules, $tail);

			// Insert a new rule in the middle
			array_push($head, array());
			$this->rules= array_merge($head, $tail);
			return $rulenumber;
		}
	}
	
	function computeNewRuleNumber($rulenumber= 0)
	{
		if (count($this->rules) == 0 || ($rulenumber >= $this->nextRuleNumber())) {
			// Add the first rule or append a new one to the end
			return $this->nextRuleNumber();
		} else {
			// Insert a new rule in the middle
			return $rulenumber;
		}
	}
		
	function nextRuleNumber()
	{
		return count($this->rules);
	}
	
	function setupEditSession($cat, &$action, &$rulenumber)
	{
		if ($action == 'add') {
			// Create a new rule and setup a new edit session
			// Change action state to create, so we don't come back here to reinit session
			$action= 'create';
			unset($_SESSION['edit']);
			$_SESSION['edit']['type']= $cat;
			$rulenumber= $this->computeNewRuleNumber($rulenumber);
			$_SESSION['edit']['rulenumber']= $rulenumber;
			$_SESSION['edit']['object']= new $cat('');
		} elseif (!isset($_SESSION['edit']['type']) || $_SESSION['edit']['type'] != $cat || $_SESSION['edit']['rulenumber'] != $rulenumber) {
			// Rule changed, setup a new edit session
			unset($_SESSION['edit']);
			$_SESSION['edit']['type']= $cat;
			$_SESSION['edit']['rulenumber']= $rulenumber;
			$_SESSION['edit']['object']= clone $this->rules[$rulenumber];
		}
	}

	function test($rulenumber, $ruleObj)
	{
		global $View;
		
		$rulesArray= array_slice(json_decode(json_encode($this), TRUE)['rules'], 0, $rulenumber);
		$rulesArray[]= json_decode(json_encode($ruleObj), TRUE);

		return $View->Controller($Output, 'TestPfRules', json_encode($rulesArray));
	}
	
	function cancel()
	{
		if (filter_has_var(INPUT_POST, 'cancel') && (filter_input(INPUT_POST, 'cancel') == 'Cancel')) {
			unset($_SESSION['edit']);
			header('Location: conf.php');
			exit;
		}
	}
	
	function save($action, $rulenumber, $ruleObj, $testResult)
	{
		if (filter_has_var(INPUT_POST, 'save') && filter_input(INPUT_POST, 'save') == 'Save') {
			if ($testResult || filter_input(INPUT_POST, 'forcesave')) {
				if ($action == 'create') {
					$this->add($rulenumber);
				}
				$this->rules[$rulenumber]= $ruleObj;
				unset($_SESSION['edit']);
				header('Location: conf.php');
				exit;
			}
		}
	}
	
	function isModified($action, $rulenumber, $ruleObj)
	{
		$modified= TRUE;
		if ($action != 'create') {
			// Make sure keys are sorted before comparison
			$newRule= $ruleObj->rule;
			ksort($newRule);

			$origRule= $this->rules[$rulenumber]->rule;
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
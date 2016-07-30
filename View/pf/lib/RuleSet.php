<?php
/* $pfre: RuleSet.php,v 1.6 2016/07/30 03:37:37 soner Exp $ */

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

/*
 * Copyright (c) 2004 Allard Consulting.  All rights reserved.
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
 *    product includes software developed by Allard Consulting
 *    and its contributors.
 * 4. Neither the name of Allard Consulting nor the names of
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
	
	function __construct($View, $filename= '/etc/pf.conf', $ruleset= NULL) {
		$retval= TRUE;

		if ($ruleset == NULL) {
			if ($filename == '/etc/pf.conf') {
				$retval= $View->Controller($Output, 'GetPfRules');
			} else {
				$retval= $View->Controller($Output, 'GetPfRules', $filename);
			}
			if ($retval !== FALSE) {
				$ruleset= implode("\n", $Output);
			}
		}
		
		if ($retval !== FALSE) {
			$this->deleteRules();
			$this->filename= $filename;
			$this->parse($ruleset);
			return TRUE;
		}
		return FALSE;
	}
	
	function deleteRules()
	{
		$this->rules= array();
	}
	
	/**
	 * Moves a rule in a ruleset up.
	 *
	 * @param int $rulenumber The number of the rule to move up in the ruleset.
	 * @return void
	 */
	function up($rulenumber)
	{
		$rules= array();
		for ($i= 0; $i < count($this->rules); $i++) {
			if ($i == ($rulenumber - 1)) {
				$rules[]= $this->rules[$i + 1];
				$rules[]= $this->rules[$i];
			} elseif ($i != $rulenumber) {
				$rules[]= $this->rules[$i];
			}
		}
		$this->rules= $rules;
	}
	
	/**
	 * Moves a rule in a ruleset down.
	 *
	 * @param int $rulenumber The number of the rule to move down in the ruleset.
	 * @return void
	 */
	function down($rulenumber)
	{
		$rules= array();
		for ($i= 0; $i < count($this->rules); $i++) {
			if ($i == $rulenumber) {
				$rules[]= $this->rules[$i + 1];
				$rules[]= $this->rules[$i];
			} elseif ($i != ($rulenumber + 1)) {
				$rules[]= $this->rules[$i];
			}
		}
		$this->rules= $rules;
	}
	
	/**
	 * Deletes a rule in the ruleset
	 *
	 * @param int $rulenumber The number of the rule to delete
	 * @return void
	 */
	function del($rulenumber)
	{
		/// @todo No need for a separate function now
		unset($this->rules[$rulenumber]);
		// Fake slice to update the keys
		$this->rules= array_slice($this->rules, 0);
	}
	
	/**
	 * Adds a rule to the ruleset
	 *
	 * @param int $rulenumber The number in the rulebase where you want to insert the rule, if not specified, it will be the last rule.
	 * @return int The number of the inserted rule in the ruleset
	 */
	function addRule($rulenumber= 0)
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
	
	function parse($text)
	{
		$rulebase= array();

		$text= preg_replace("/\n#/", "\n# ", $text);
		$text= str_replace("\\\n", "", $text);
		foreach (preg_split("/\n/", $text, '-1') as $line) {
			$rulebase[]= trim($line);
		}

		$order= 0;
		foreach ($rulebase as $str) {
			$words= preg_split('/[\s,\t]+/', $str, '-1');
			
			$type= $words[0];
            // Do not search in comment lines
			if ($type != '' && $type != '#' && preg_match('/\b(scrub|af-to|nat-to|binat-to|divert-to|rdr-to|timeout|limit)\b/', $str, $match)) {
				$type= $match[1];
			}

			// Add any accumulated comment or blank lines if a non-comment/blank rule is next
			if ($type != '' && $type != '#') {
				if (isset($comment)) {
					$this->rules[]= new Comment($comment);
					unset($comment);
				}
				if ($blank != '') {
					$this->rules[]= new Blank($blank);
					$blank= '';
				}
			}

			switch ($type) {
				case '':
					if (isset($comment)) {
						$this->rules[]= new Comment($comment);
						unset($comment);
					}
					$blank.= "\n";
					break;
				case '#':
					if ($blank != '') {
						$this->rules[]= new Blank($blank);
						$blank= '';
					}
					if (!isset($comment)) {
						$comment= trim(substr($str, 1));
					} else {
						$comment.= "\n" . trim(substr($str, 1));
					}
					break;
				case 'include':
					$this->rules[]= new _Include($str);
					break;
				case 'anchor':
					$this->rules[]= new Anchor($str);
					break;
				case 'pass':
				case 'block':
				case 'match':
				case 'antispoof':
					$this->rules[]= new Filter($str);
					break;
				case 'af-to':
				case 'nat-to':
				case 'binat-to':
				case 'divert-to':
				case 'rdr-to':
					$this->rules[]= new Nat($str);
					break;
				case 'timeout':
					$this->rules[]= new Timeout($str);
					break;
				case 'set':
					$this->rules[]= new Option($str);
					break;
				case 'limit':
					$this->rules[]= new Limit($str);
					break;
				case 'scrub':
					$this->rules[]= new Scrub($str);
					break;
				case 'table':
					$this->rules[]= new Table($str);
					break;
				case 'queue':
					$this->rules[]= new Queue($str);
					break;
				case 'load':
					$this->rules[]= new LoadAnchor($str);
					break;
				default:
					$this->rules[]= new Macro($str);
					break;
			}
		}
        
        // Necessary if there is no non-comment rule at the end of ruleset
        if (isset($comment)) {
            $this->rules[]= new Comment($comment);
        }
        /// @attention Do not append accumulated blank lines to the end
	}

	function generate($lines= FALSE, $uptoRuleNumber= NULL, $includeNonRules= TRUE, $singleLineNonRules= FALSE)
	{
		if ($uptoRuleNumber == NULL) {
			$uptoRuleNumber= count($this->rules);
		}
		
		$str= '';
		$count= 0;
		foreach ($this->rules as $rule) {
			if (!in_array($rule->cat, array('Comment', 'Blank'))) {
				$str.= $rule->generate();
			} elseif ($includeNonRules) {
				$str.= $rule->generate($singleLineNonRules);
			}
			
            // Exclusive, not inclusive of the rule $uptoRuleNumber
			if (++$count >= $uptoRuleNumber) {
				break;
			}
		}
        
		if ($lines) {
			$linenumber= 0;
			foreach (explode("\n", $str) as $line) {
				$s.= sprintf('% 4d', $linenumber++) . ": $line\n";
			}
			$str= $s;
		}
		return $str;
	}
	
	function SetupEditSession($cat, &$action, &$rulenumber)
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

	function ProcessTestCancelSaveModified($action, $rulenumber, $ruleObj, &$modified, &$testResult, $test= TRUE)
	{
		global $View;

		if ($test) {
			$rulesStr= $this->generate(FALSE, $rulenumber, TRUE, TRUE);
			$rulesStr.= $ruleObj->generate();
			$testResult= $View->Controller($Output, 'TestPfRules', serialize(explode('\n', $rulesStr)));
		}

		if (filter_has_var(INPUT_POST, 'cancel') && (filter_input(INPUT_POST, 'cancel') == 'Cancel')) {
			unset($_SESSION['edit']);
			header('Location: conf.php');
			exit;
		}

		if (filter_has_var(INPUT_POST, 'save') && filter_input(INPUT_POST, 'save') == 'Save') {
			if ($testResult || filter_input(INPUT_POST, 'forcesave')) {
				if ($action == 'create') {
					$this->addRule($rulenumber);
				}
				$this->rules[$rulenumber]= $ruleObj;
				unset($_SESSION['edit']);
				header('Location: conf.php');
				exit;
			}
		}

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
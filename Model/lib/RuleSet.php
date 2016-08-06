<?php
/* $pfre: RuleSet.php,v 1.5 2016/08/06 17:00:04 soner Exp $ */

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
	public $rules= array();
	
	function load($rulesArray, $force= FALSE)
	{
		$this->deleteRules();
	
		$retval= TRUE;
		$ruleNumber= 0;
		foreach ($rulesArray as $ruleDef) {
			$class= $ruleDef['cat'];
			$ruleObj= new $class('');
			if (!$ruleObj->load($ruleDef['rule'], $ruleNumber, $force)) {
				if (!$force) {
					$msg= 'Error loading, rule loaded partially';
				} else {
					$msg= 'Error loading, rule load forced';
				}
				pfrec_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, Error("$ruleNumber: $msg"));
				$retval= FALSE;
			}
			$this->rules[]= $ruleObj;
			$ruleNumber++;
		}
		return $retval;
	}
	
	function deleteRules()
	{
		$this->rules= array();
	}
	
	function parse($text, $force= FALSE)
	{
		$this->deleteRules();
		$rulebase= array();

		$text= preg_replace("/\n#/", "\n# ", $text);
		$text= str_replace("\\\n", '', $text);

		$rulebase= explode("\n", $text);

		for ($order= 0; $order < count($rulebase); $order++) {
			$str= $rulebase[$order];
			$words= preg_split('/[\s,\t]+/', trim($str), -1);
			
			$type= $words[0];
            // Do not search in comment lines
			if ($type !== '' && $type !== '#' && preg_match('/\b(scrub|af-to|nat-to|binat-to|divert-to|rdr-to|timeout|limit|route-to|reply-to|dup-to|divert-packet|state-defaults)\b/', $str, $match)) {
				$type= $match[1];
			}

			// Add any accumulated comment or blank lines if a non-comment/blank rule is next
			if ($type !== '' && $type !== '#') {
				if (isset($comment)) {
					$this->rules[]= new Comment($comment);
					unset($comment);
				}
				if ($blank != '') {
					$this->rules[]= new Blank($blank);
					$blank= '';
				}
			}

			if ($type === 'anchor' && preg_match('/^.*{\s*$/', $str)) {
				$this->parseInlineRules($rulebase, $str, $order, $force);
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
					$this->rules[]= new Filter($str);
					break;
				case 'antispoof':
					$this->rules[]= new Antispoof($str);
					break;
				case 'af-to':
					$this->rules[]= new AfTo($str);
					break;
				case 'nat-to':
					$this->rules[]= new NatTo($str);
					break;
				case 'binat-to':
					$this->rules[]= new BinatTo($str);
					break;
				case 'divert-to':
					$this->rules[]= new DivertTo($str);
					break;
				case 'divert-packet':
					$this->rules[]= new DivertPacket($str);
					break;
				case 'rdr-to':
					$this->rules[]= new RdrTo($str);
					break;
				case 'route-to':
				case 'reply-to':
				case 'dup-to':
					$this->rules[]= new Route($str);
					break;
				case 'timeout':
					$this->rules[]= new Timeout($str);
					break;
				case 'state-defaults':
					$this->rules[]= new State($str);
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
		
		return $this->validate($force);
	}

	function validate($force= FALSE)
	{
		// Reload for validation
		$rulesArray= json_decode(json_encode($this->rules), TRUE);
		if (!$this->load($rulesArray, $force)) {
			pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, Error('Load Error: Ruleset contains errors'));
			return FALSE;
		}
		return TRUE;
	}

	function parseInlineRules($rulebase, &$str, &$order, $force= FALSE)
	{
		if (preg_match('/^(.*){\s*$/', $str, $match)) {
			$str= $match[1] . ' inline ';

			$nesting= 1;
			$order++;
			while ($order < count($rulebase)) {
				$line= $rulebase[$order];

				// anchor-close = "}", but there may be a comment after it, hence match
				if (!preg_match('/^\s*}(.*)$/', $line, $match)) {
					$str.= "$line\n";
					/// @todo Use recursion instead?
					if (preg_match('/^.*{\s*$/', $line)) {
						// Do not allow more than 2 nested inline rules
						if (++$nesting > 2) {
							Error("Parse Error: Reached max nesting for inline anchors: <pre>" . print_r($line, TRUE) . '</pre>');
							pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Parse Error: Reached max nesting for inline anchors: $line");
							if (!$force) {
								break;
							}
						}
					}
				} else {
					if (--$nesting == 0) {
						// Discard the last anchor-close, keep the trailing text
						$str.= $match[1] . "\n";
						break;
					} else {
						// Don't discard the anchor-close of nested anchors
						$str.= "$line\n";
					}
				}
				$order++;
			}
		}
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
        
		// Do not merge this loop with the generate loop above
		if ($lines) {
			$linenumber= 0;
			foreach (explode("\n", $str) as $line) {
				$s.= sprintf('% 4d', $linenumber++) . ": $line\n";
			}
			$str= $s;
		}
		return $str;
	}
}
?>
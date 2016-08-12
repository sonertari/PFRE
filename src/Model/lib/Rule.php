<?php
/* $pfre: Rule.php,v 1.13 2016/08/12 15:28:34 soner Exp $ */

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

namespace Model;

class Rule
{
	public $cat= '';
	public $rule= array();
	
	protected $str= '';
	protected $index= 0;
	protected $words= array();

	protected $keywords = array();
	
	protected $ruleNumber= 0;

	protected $keyInterface= array(
		'on' => array(
			'method' => 'parseItems',
			'params' => array('interface'),
			),
		);

	protected $keyAf= array(
		'inet' => array(
			'method' => 'parseNVP',
			'params' => array('af'),
			),
		'inet6' => array(
			'method' => 'parseNVP',
			'params' => array('af'),
			),
		);

	protected $keyLog= array(
		'log' => array(
			'method' => 'parseLog',
			'params' => array(),
			),
		);

	protected $keyQuick= array(
		'quick' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		);

	protected $typedef= array();

	protected $typeInterface= array(
		'interface' => array(
			'multi' => TRUE,
			'regex' => RE_IFSPEC,
			),
		);

	protected $typeAf= array(
		'af' => array(
			'regex' => RE_AF,
			),
		);

	protected $typeLog= array(
		'log' => array(
			/// @attention log can be of type either bool or array of values
			// Validate functions can handle multi-type values like this, no problem
			'regex' => RE_BOOL,
			'values' => array(
				'all' => array(
					'regex' => RE_BOOL,
					),
				'matches' => array(
					'regex' => RE_BOOL,
					),
				'user' => array(
					'regex' => RE_BOOL,
					),
				'to' => array(
					'regex' => RE_IF,
					),
				),
			),
		);

	protected $typeQuick= array(
		'quick' => array(
			'regex' => RE_BOOL,
			),
		);

	protected $typeComment= array(
		'comment' => array(
			'regex' => RE_COMMENT_INLINE,
			),
		);

	function __construct($str)
	{
		$this->cat= str_replace(__NAMESPACE__ . '\\', '', get_called_class());

		if ($str != '') {
			$this->parse($str);
		}
	}

	function load($arr, $ruleNumber= 0, $force= FALSE)
	{
		$this->ruleNumber= $ruleNumber;

		$retval= $this->validate($arr, $force);
		if ($retval || $force) {
			$this->rule= $arr;
		}
		return $retval;
	}

	function validate($ruleArray, $force= FALSE)
	{
		$arr= $ruleArray;
		foreach ($this->typedef as $key => $def) {
			if (!$this->validateKeyDef($arr, $key, $def, '', $force)) {
				return FALSE;
			}
		}

		if (count($arr) > 0) {
			pfrec_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, Error("$this->ruleNumber: Validation Error: Unexpected elements: " . implode(', ', array_keys($arr))));
			return FALSE;
		}
		return TRUE;
	}

	function validateKeyDef(&$arr, $key, $def, $parent, $force= FALSE)
	{
		if (array_key_exists($key, $arr)) {
			if (is_array($arr[$key])) {
				// Recursion
				if (!$this->validateArrayValues($arr[$key], $key, $def, $parent, $force)) {
					return FALSE;
				}
			} elseif (!$this->validateValue($key, $arr[$key], $def, $parent, $force)) {
				return FALSE;
			}
			unset($arr[$key]);
		} elseif (isset($def['require']) && $def['require']) {
			pfrec_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, Error("$this->ruleNumber: Validation Error: Required element missing: " . ltrim("$parent.$key", '.')));
			return FALSE;
		}
		return TRUE;
	}

	function validateArrayValues(&$arr, $key, $def, $parent, $force= FALSE)
	{
		if (isset($def['multi']) && $def['multi']) {
			foreach ($arr as $v) {
				if (!$this->validateValue($key, $v, $def, $parent, $force)) {
					return FALSE;
				}
			}
		} elseif (isset($def['values']) && is_array($def['values'])) {
			foreach ($def['values'] as $k => $d) {
				// Recursion
				if (!$this->validateKeyDef($arr, $k, $d, $key, $force)) {
					return FALSE;
				}
			}

			if (count($arr) > 0) {
				pfrec_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, Error("$this->ruleNumber: Validation Error: Unexpected elements: " . ltrim("$parent.$key", '.') . ' ' . implode(', ', array_keys($arr))));
				return FALSE;
			}
		} else {
			pfrec_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, Error("$this->ruleNumber: Validation Error: Multiple values not allowed for " . ltrim("$parent.$key", '.')));
			return FALSE;
		}
		return TRUE;
	}

	function validateValue($key, $value, $def, $parent, $force= FALSE)
	{
		if (isset($def['regex'])) {
			$rxfn= $def['regex'];
			$result= preg_match("/$rxfn/", $value);
		} elseif (isset($def['func'])) {
			$rxfn= $def['func'];
			if (isset($def['force']) && $def['force']) {
				$result= $rxfn($value, $force);
			} else {
				$result= $rxfn($value);
			}
		} else {
			pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, Error("$this->ruleNumber: Validation Error: No regex or func def for: " . ltrim("$parent.$key", '.')));
			return FALSE;
		}

		if (!$result) {
			Error("$this->ruleNumber: Validation Error: Invalid value for '" . ltrim("$parent.$key", '.') . "': <pre>" . htmlentities(print_r($value, TRUE)) . '</pre>');
			pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "$this->ruleNumber: Validation Error: Invalid value for '" . ltrim("$parent.$key", '.') . "': " . print_r($value, TRUE));
			return FALSE;
		} else {
			pfrec_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "$this->ruleNumber: Valid value for '" . ltrim("$parent.$key", '.') . "': " . print_r($value, TRUE) . ", $rxfn");
		}
		return TRUE;
	}

	function parse($str)
	{
		$this->str= $str;
		$this->init();
		$this->parseComment();
		$this->sanitize();
		$this->split();

		for ($this->index= 0; $this->index < count($this->words); $this->index++) {
			$key= $this->words[$this->index];
			if (array_key_exists($key, $this->keywords)) {
				$method= $this->keywords[$key]['method'];				
				if (is_callable($method, TRUE)) {
					call_user_method_array($method, $this, $this->keywords[$key]['params']);
				} else {
					pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Parser method '$method' not callable");
				}
			} else {
				pfrec_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Word '$key' not in keywords");
			}
		}
	}

	function init()
	{
		$this->rule= array();
	}

	function parseComment()
	{
		$pos= strpos($this->str, '#');
		if ($pos) {
			$this->rule['comment']= trim(substr($this->str, $pos + 1));
			$this->str= substr($this->str, 0, $pos);
		}
	}

	function sanitize()
	{
		$this->str= preg_replace('/! +/', '!', $this->str);
		$this->str= preg_replace('/{/', ' { ', $this->str);
		$this->str= preg_replace('/}/', ' } ', $this->str);
		$this->str= preg_replace('/\(/', ' ( ', $this->str);
		$this->str= preg_replace('/\)/', ' ) ', $this->str);
		$this->str= preg_replace('/,/', ' , ', $this->str);
		$this->str= preg_replace('/"/', ' " ', $this->str);
	}

	function split()
	{
		$this->words= preg_split('/[\s,\t]+/', $this->str, -1, PREG_SPLIT_NO_EMPTY);
	}

	function isEndOfWords()
	{
		return $this->index >= count($this->words);
	}

	function parseNVP($key)
	{
		$this->rule[$key]= $this->words[$this->index];
	}

	function parseNVPInc($key)
	{
		$this->rule[$key]= $this->words[$this->index++];
	}

	function parseNextValue()
	{
		$this->rule[$this->words[$this->index]]= preg_replace('/"/', '', $this->words[++$this->index]);
	}

	function parseNextNVP($key)
	{
		$this->rule[$key]= $this->words[++$this->index];
	}

	function parseBool()
	{
		$this->rule[$this->words[$this->index]]= TRUE;
	}
	
	function parseItems($key, $delimPre= '{', $delimPost= '}')
	{
		$this->rule[$key]= $this->parseItem($delimPre, $delimPost);		
	}
	
	function parseItem($delimPre= '{', $delimPost= '}')
	{
		$this->index++;
		if (($this->words[$this->index] == $delimPre)) {
			while (preg_replace('/[\s,]+/', '', $this->words[++$this->index]) != $delimPost && !$this->isEndOfWords()) {
				$value[]= $this->parseParenthesized();
			}
		} else {
			// ($ext_if)
			$value[]= $this->parseParenthesized();
			FlattenArray($value);
		}
		return $value;
	}

	function parseParenthesized()
	{
		if ($this->words[$this->index] == '(') {
			while ($this->words[++$this->index] != ')' && !$this->isEndOfWords()) {
				$items[]= $this->words[$this->index];
			}
			$retval= '(' . implode(' ', $items) . ')';
		} else {
			// IP range, routehost = host "@" interface-name, IP net
			if (isset($this->words[$this->index + 1]) && ($this->words[$this->index + 1] == '-' || $this->words[$this->index + 1] == '@' || $this->words[$this->index + 1] == '/')) {
				$retval= $this->words[$this->index] . ' ' . $this->words[$this->index + 1] . ' ' . $this->words[$this->index + 2];
				$this->index+= 2;
			} else {
				$retval= $this->words[$this->index];
			}
		}

		// address [ "weight" number ] | address [ "/" mask-bits ] [ "weight" number ]
		if (isset($this->words[$this->index + 1]) && ($this->words[$this->index + 1] == 'weight')) {
			$retval= $this->words[$this->index] . ' ' . $this->words[$this->index + 1] . ' ' . $this->words[$this->index + 2];
			$this->index+= 2;
		}

		return $retval;
	}

	function parsePortItem()
	{
		$this->index++;
		if ($this->words[$this->index] == '{') {
			while (preg_replace('/[\s,]+/', '', $this->words[++$this->index]) != '}' && !$this->isEndOfWords()) {
				$this->words[$this->index]= preg_replace('/[\s,]+/', '', $this->words[$this->index]);
				$value[]= $this->parsePort();
			}
		} else {
			$value= $this->parsePort();
		}
		return $value;
	}

	function parsePort()
	{
		if (in_array($this->words[$this->index], array('=', '!=', '<', '<=', '>', '>='))) {
			// unary-op = [ "=" | "!=" | "<" | "<=" | ">" | ">=" ] ( name | number )
			return $this->words[$this->index] . ' ' . $this->words[++$this->index];
		} elseif (isset($this->words[$this->index + 1]) && (in_array($this->words[$this->index + 1], array('<>', '><', ':')))) {
			// binary-op = number ( "<>" | "><" | ":" ) number
			// portspec = "port" ( number | name ) [ ":" ( "*" | number | name ) ]
			return $this->words[$this->index] . ' ' . $this->words[++$this->index] . ' ' . $this->words[++$this->index];
		} else {
			// ( name | number )
			return $this->words[$this->index];
		}
	}

	function parseDelimitedStr($key, $delimPre= '"', $delimPost= '"')
	{
		$this->index++;
		$this->rule[$key]= $this->parseString($delimPre, $delimPost);		
	}

	function parseString($delimPre= '"', $delimPost= '"')
	{
		$value= '';
		if ($this->words[$this->index] == $delimPre) {
			while ($this->words[++$this->index] != $delimPost && !$this->isEndOfWords()) {
				$value.= ' ' . $this->words[$this->index];
			}
		} else {
			$value= $this->words[$this->index];
		}
		return trim($value);
	}

	function parseAny()
	{
		if (!isset($this->rule['from'])) {
			$this->rule['from']= 'any';
		} else {
			$this->rule['to']= 'any';
		}
	}

	function parseSrcDest($portKey)
	{
		if ($this->words[$this->index + 1] != 'port') {
			if ($this->words[$this->index + 1] == 'route') {
				$hostKey= $this->words[$this->index];
				$this->index+= 2;
				$this->rule[$hostKey . 'route']= $this->words[$this->index];
			} else {
				$this->parseItems($this->words[$this->index]);
			}
		}
		if (isset($this->words[$this->index + 1]) && ($this->words[$this->index + 1] == 'port')) {
			$this->index++;
			$this->rule[$portKey]= $this->parsePortItem();
		}
	}

	function parseOS()
	{
		$this->index++;
		if ($this->words[$this->index] == '{') {
			while (preg_replace('/[\s,]+/', '', $this->words[++$this->index]) != '}' && !$this->isEndOfWords()) {
				$this->rule['os'][]= $this->parseString();		
			}
		} else {
			$this->rule['os']= $this->parseString();		
		}
	}

	function parseLog()
	{
		if ($this->words[$this->index + 1] == '(') {
			$opts= $this->parseItem('(', ')');
			$this->rule['log']= array();
			for ($i= 0; $i < count($opts); $i++) {
				if ($opts[$i] == 'to') {
					$this->rule['log']['to']= $opts[++$i];
				} else {
					$this->rule['log'][$opts[$i]]= TRUE;
				}
			}
		} else {
			$this->rule['log']= TRUE;
		}
	}
	
	function parseICMPType($code)
	{
		$this->parseItems($this->words[$this->index]);
		if ($this->words[$this->index + 1] == 'code') {
			$this->index++;
			$this->parseItems($code);
		}
	}

	function genKey($key)
	{
		if (isset($this->rule[$key])) {
			$this->str.= ' ' . $key;
		}
	}

	function genValue($key, $head= '', $tail= '')
	{
		if (isset($this->rule[$key])) {
			$this->str.= ' ' . $head . $this->rule[$key] . $tail;
		}
	}

	function genItems($key, $head= '', $delimPre= '{', $delimPost= '}')
	{
		if (isset($this->rule[$key])) {
			$this->str.= $this->generateItem($this->rule[$key], $head, $delimPre, $delimPost);
		}
	}

	function generateItem($items, $head= '', $delimPre= '{', $delimPost= '}')
	{
		$head= $head == '' ? '' : ' ' . trim($head);
		if (is_array($items)) {
			return $head . " $delimPre " . implode(', ', $items) . " $delimPost";
		} else {
			return $head . ' ' . $items;
		}
	}

	function genInterface()
	{
		$this->genItems('interface', 'on');
	}

	function genComment()
	{
		if (isset($this->rule['comment'])) {
			$this->str.= ' # ' . trim(stripslashes($this->rule['comment']));
		}
	}

	function genLog()
	{
		if (isset($this->rule['log'])) {
			if (is_array($this->rule['log'])) {
				$s= ' log ( ';
				foreach ($this->rule['log'] as $k => $v) {
					$s.= (is_bool($v) ? "$k" : "$k $v") . ', ';
				}
				$this->str.= rtrim($s, ', ') . ' )';
			} else {
				$this->str.= ' log';
			}
		}
	}
}
?>

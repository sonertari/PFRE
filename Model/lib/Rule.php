<?php
/* $pfre: Rule.php,v 1.23 2016/08/04 02:16:13 soner Exp $ */

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

class Rule
{
	public $cat= '';
	public $rule= array();
	
	protected $str= '';
	protected $index= 0;
	protected $words= array();

	protected $keywords = array();
	
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

	function __construct($str)
	{
		$this->cat= get_called_class();
		$this->parse($str);
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
					$this->rule[]= $method;
				}
			} else {
				$this->rule[]= $key;
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
			while (preg_replace('/[\s,]+/', '', $this->words[++$this->index]) != $delimPost) {
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
			while ($this->words[++$this->index] != ')') {
				$items[]= $this->words[$this->index];
			}
			return '(' . implode(' ', $items) . ')';
		} else {
			return $this->words[$this->index];
		}
	}

	function parsePortItem()
	{
		$this->index++;
		if ($this->words[$this->index] == '{') {
			while (preg_replace('/[\s,]+/', '', $this->words[++$this->index]) != '}') {
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
		} elseif (in_array($this->words[$this->index + 1], array('<>', '><', ':'))) {
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
		if ($this->words[$this->index] == $delimPre) {
			while ($this->words[++$this->index] != $delimPost) {
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

	function parseSrcDest($port)
	{
		if ($this->words[$this->index + 1] != 'port') {
			$this->rule[$this->words[$this->index]]= $this->parseItem();
		}
		if ($this->words[$this->index + 1] == 'port') {
			$this->index++;
			$this->rule[$port]= $this->parsePortItem();
		}
	}

	function parseOS()
	{
		$this->index++;
		if ($this->words[$this->index] == '{') {
			while (preg_replace('/[\s,]+/', '', $this->words[++$this->index]) != '}') {
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
		$this->rule[$this->words[$this->index]]= $this->parseItem();
		if ($this->words[$this->index + 1] == 'code') {
			$this->index++;
			$this->rule[$code]= $this->parseItem();
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

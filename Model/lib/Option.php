<?php 
/* $pfre: Option.php,v 1.15 2016/08/04 02:16:13 soner Exp $ */

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

class Option extends Rule
{
	function __construct($str)
	{
		/// @todo Support set state-defaults state-option, ...
		$this->keywords = array(
			'loginterface' => array(
				'method' => 'parseOption',
				'params' => array(),
				),
			'block-policy' => array(
				'method' => 'parseOption',
				'params' => array(),
				),
			'state-policy' => array(
				'method' => 'parseOption',
				'params' => array(),
				),
			'optimization' => array(
				'method' => 'parseOption',
				'params' => array(),
				),
			'ruleset-optimization' => array(
				'method' => 'parseOption',
				'params' => array(),
				),
			'debug' => array(
				'method' => 'parseOption',
				'params' => array(),
				),
			'hostid' => array(
				'method' => 'parseOption',
				'params' => array(),
				),
			'skip' => array(
				'method' => 'parseSkip',
				'params' => array(),
				),
			'fingerprints' => array(
				'method' => 'parseFingerprints',
				'params' => array(),
				),
			'reassemble' => array(
				'method' => 'parseReassemble',
				'params' => array(),
				),
			);

		parent::__construct($str);
	}

	function parseOption()
	{
		$this->rule['type']= $this->words[$this->index];
		$this->rule[$this->words[$this->index]]= $this->words[++$this->index];
	}

	function parseSkip()
	{
		$this->rule['type']= 'skip';
		$this->index++;
		$this->rule['skip']= $this->parseItem();
	}

	function parseFingerprints()
	{
		$this->rule['type']= 'fingerprints';
		// File name is in quotes
		$this->parseDelimitedStr('fingerprints');
	}

	function parseReassemble()
	{
		$this->parseOption();
		if ($this->words[$this->index + 1] === 'no-df') {
			$this->index++;
			$this->parseBool();
		}
	}

	function generate()
	{
		$this->str= '';

		$this->genOption('block-policy');
		$this->genOption('debug');
		$this->genOption('fingerprints', '"', '"');
		$this->genOption('hostid');
		$this->genOption('loginterface');
		$this->genOption('optimization');
		$this->genOption('ruleset-optimization');
		$this->genOption('state-policy');
		$this->genSkip();
		$this->genReassemble();
		
		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}

	function genOption($key, $head= '', $tail= '')
	{
		if (isset($this->rule[$key])) {
			$this->str.= "set $key " . $head . preg_replace('/"/', '', $this->rule[$key]) . $tail;
		}
	}

	function genSkip()
	{
		if (isset($this->rule['skip'])) {
			if (!is_array($this->rule['skip'])) {
				$this->genOption('skip', 'on ');
			} else {
				$this->str.= 'set skip on { ' . implode(' ', $this->rule['skip']) . ' }';
			}
		}
	}

	function genReassemble()
	{
		if (isset($this->rule['reassemble'])) {
			$this->genOption('reassemble');
			$this->genKey('no-df');
		}
	}
}
?>
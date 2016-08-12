<?php
/* $pfre: Filter.php,v 1.6 2016/08/11 18:29:20 soner Exp $ */

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

class Filter extends FilterBase
{
	protected $keyAction= array(
		'pass' => array(
			'method' => 'parseNVP',
			'params' => array('action'),
			),
		'match' => array(
			'method' => 'parseNVP',
			'params' => array('action'),
			),
		'block' => array(
			'method' => 'parseNVP',
			'params' => array('action'),
			),
		'drop' => array(
			'method' => 'parseNVP',
			'params' => array('blockoption'),
			),
		'return' => array(
			'method' => 'parseNVP',
			'params' => array('blockoption'),
			),
		'return-rst' => array(
			'method' => 'parseBlockOption',
			'params' => array(),
			),
		'return-icmp' => array(
			'method' => 'parseBlockOption',
			'params' => array(),
			),
		'return-icmp6' => array(
			'method' => 'parseBlockOption',
			'params' => array(),
			),
		);

	protected $keyPoolType= array(
		'bitmask' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'least-states' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'round-robin' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'random' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'source-hash' => array(
			'method' => 'parseSourceHash',
			'params' => array(),
			),
		'sticky-address' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		);

	protected $keyInterface= array(
		'on' => array(
			'method' => 'parseInterface',
			'params' => array(),
			),
		);

	protected $typeAction= array(
		'action' => array(
			'regex' => RE_ACTION,
			),
		'blockoption' => array(
			'regex' => RE_BLOCKOPTION,
			),
		'block-ttl' => array(
			'regex' => RE_NUM,
			),
		'block-icmpcode' => array(
			'regex' => RE_ICMPCODE,
			),
		'block-icmp6code' => array(
			'regex' => RE_ICMPCODE,
			),
		);

	protected $typeType= array(
		'type' => array(
			'regex' => RE_TYPE,
			),
		);

	protected $typeRedirHost= array(
		'redirhost' => array(
			'multi' => TRUE,
			'regex' => RE_REDIRHOST,
			),
		);

	protected $typePoolType= array(
		'bitmask' => array(
			'regex' => RE_BOOL,
			),
		'least-states' => array(
			'regex' => RE_BOOL,
			),
		'round-robin' => array(
			'regex' => RE_BOOL,
			),
		'random' => array(
			'regex' => RE_BOOL,
			),
		'source-hash' => array(
			'regex' => RE_BOOL,
			),
		'source-hash-key' => array(
			'regex' => RE_SOURCE_HASH_KEY,
			),
		'sticky-address' => array(
			'regex' => RE_BOOL,
			),
		);

	protected $typeDivertPort= array(
		'divertport' => array(
			'regex' => RE_PORT,
			),
		);

	protected $typeInterface= array(
		'interface' => array(
			'multi' => TRUE,
			'regex' => RE_IFSPEC,
			),
		'rdomain' => array(
			'regex' => RE_NUM,
			),
		);

	function __construct($str)
	{
		$this->keywords= array_merge(
			$this->keywords,
			$this->keyAction,
			$this->keyLog,
			$this->keyQuick
			);

		$this->typedef= array_merge(
			$this->typedef,
			$this->typeAction,
			$this->typeLog,
			$this->typeQuick,
			$this->typeType
			);

		parent::__construct($str);
	}

	function parseInterface()
	{
		if ($this->words[$this->index + 1] == 'rdomain') {
			$this->index++;
			$this->rule['rdomain']= $this->words[++$this->index];
		} else {
			$this->parseItems('interface');
		}
	}

	function parseRedirHostPort($hostKey= 'redirhost', $portKey= 'redirport')
	{
		$this->parseNVP('type');

		/// @todo Fix these off-by-N errors
		if ($this->words[$this->index + 1] != 'port') {
			$this->parseItems($hostKey);
		}
		// @attention Do not use else here
		if (isset($this->words[$this->index + 1]) && ($this->words[$this->index + 1] == 'port')) {
			$this->index+= 2;
			$this->rule[$portKey]= $this->words[$this->index];
		}
	}

	function parseBlockOption()
	{
		$this->parseNVP('blockoption');

		if ($this->rule['blockoption'] == 'return-rst') {
			if ($this->words[$this->index + 1] == '(' && $this->words[$this->index + 2] == 'block-ttl') {
				$this->index+= 3;
				$this->rule['block-ttl']= $this->words[$this->index];
			}
		} elseif ($this->rule['blockoption'] == 'return-icmp') {
			if ($this->words[$this->index + 1] == '(') {
				$this->index+= 2;
				$this->rule['block-icmpcode']= $this->words[$this->index];

				if ($this->words[$this->index + 1] == ',') {
					$this->index+= 2;
					$this->rule['block-icmp6code']= $this->words[$this->index];
				}
			}
		} elseif ($this->rule['blockoption'] == 'return-icmp6') {
			if ($this->words[$this->index + 1] == '(') {
				$this->index+= 2;
				$this->rule['block-icmp6code']= $this->words[$this->index];
			}
		}
	}
	
	function parseSourceHash()
	{
		$this->parseBool();

		/// @attention No pattern for hash key or string, so check keywords instead
		/// This is one of the benefits of using keyword lists instead of switch/case structs while parsing
		//if (preg_match('/^[a-f\d]{16,}$/', $this->words[$this->index + 1])) {
		if (!in_array($this->words[$this->index + 1], $this->keywords)) {
			$this->rule['source-hash-key']= $this->words[++$this->index];
		}
	}

	function generate()
	{
		$this->genAction();

		$this->genFilterHead();
		$this->genFilterOpts();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}

	function genAction()
	{
		$this->str= $this->rule['action'];
		if ($this->rule['action'] == 'block') {
			$this->genBlockOption();
		}
	}

	function genBlockOption()
	{
		$this->genValue('blockoption');

		if (isset($this->rule['blockoption'])) {
			if ($this->rule['blockoption'] == 'return-rst') {
				$this->genValue('block-ttl', '( ttl ', ' )');
			} elseif ($this->rule['blockoption'] == 'return-icmp') {
				$this->arr= array();

				if (isset($this->rule['block-icmpcode'])) {
					$this->arr[]= $this->rule['block-icmpcode'];

					if (isset($this->rule['block-icmp6code'])) {
						$this->arr[]= $this->rule['block-icmp6code'];
					}
				}

				if (count($this->arr)) {
					$this->str.= ' ( ';
					$this->str.= implode(', ', $this->arr);
					$this->str.= ' )';
				}
			} elseif ($this->rule['blockoption'] == 'return-icmp6') {
				$this->genValue('block-icmp6code', '( ', ' )');
			}
		}
	}
	
	function genInterface()
	{
		if (isset($this->rule['interface'])) {
			$this->genItems('interface', 'on');
		} else {
			$this->genValue('rdomain', 'on rdomain ');
		}
	}

	function genPoolType()
	{
		$this->genKey('bitmask');
		$this->genKey('least-states');
		$this->genKey('random');
		$this->genKey('round-robin');

		$this->genKey('source-hash');
		if (isset($this->rule['source-hash'])) {
			$this->genValue('source-hash-key');
		}

		$this->genKey('sticky-address');
	}
}
?>

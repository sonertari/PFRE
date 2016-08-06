<?php
/* $pfre: NatBase.php,v 1.2 2016/08/05 22:30:06 soner Exp $ */

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

class NatBase extends Filter
{
	protected $keyNatBase= array(
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

	protected $typeNatBase= array(
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

	function __construct($str)
	{
		$this->keywords = array_merge(
			$this->keywords,
			$this->keyNatBase
			);

		$this->typedef= array_merge(
			$this->typedef,
			$this->typeNatBase
			);

		parent::__construct($str);
	}

	function sanitize()
	{
		$this->str= preg_replace("/! +/", "!", $this->str);
		$this->str= preg_replace("/{/", " { ", $this->str);
		$this->str= preg_replace("/}/", " } ", $this->str);
		$this->str= preg_replace("/\"/", " \" ", $this->str);
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

		$this->genValue('type');
		$this->genValue('redirhost');
		$this->genValue('redirport', 'port ');
		$this->genPoolType();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
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

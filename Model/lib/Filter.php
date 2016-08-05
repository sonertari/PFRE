<?php
/* $pfre: Filter.php,v 1.1 2016/08/04 14:42:52 soner Exp $ */

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
			'method' => 'parseNVP',
			'params' => array('blockoption'),
			),
		'return-icmp' => array(
			'method' => 'parseNVP',
			'params' => array('blockoption'),
			),
		'return-icmp6' => array(
			'method' => 'parseNVP',
			'params' => array('blockoption'),
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
			'regex' => '^(pass|match|block)$'
			),
		'blockoption' => array(
			'regex' => '^(drop|return|return-rst|return-icmp|return-icmp6)$'
			),
		);

	protected $typeType= array(
		'type' => array(
			/// @todo Enum types instead
			'regex' => '^[a-z-]{0,20}$',
			),
		);

	protected $typeRedirHostPort= array(
		'redirhost' => array(
			'regex' => '^[\w_.\/\-*:$<>!()]{0,50}$',
			),
		'redirport' => array(
			'regex' => '^[\w_.\/\-*$<>!=\s:]{0,50}$',
			),
		);

	protected $typeInterface= array(
		'interface' => array(
			'multi' => TRUE,
			'regex' => '^(\w|\$|!)[\w_.\/\-*]{0,50}$',
			),
		'rdomain' => array(
			'func' => 'IsNumber',
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
			$this->typeType,
			$this->typeRedirHostPort
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

	/// @todo Insert a new class between Filter and Nat/Redirect classes, move this func there?
	function parseRedirHostPort()
	{
		$this->parseNVP('type');

		/// @todo Fix these off-by-N errors
		if ($this->words[$this->index + 1] != 'port') {
			$this->rule['redirhost']= $this->words[++$this->index];
		}
		// @attention Do not use else here
		if ($this->words[$this->index + 1] == 'port') {
			$this->index+= 2;
			$this->rule['redirport']= $this->words[$this->index];
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
			$this->genValue('blockoption');
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
}
?>

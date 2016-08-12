<?php
/* $pfre: Scrub.php,v 1.4 2016/08/11 18:29:20 soner Exp $ */

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

class Scrub extends Filter
{
	protected $keyScrub= array(
		'min-ttl' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'max-mss' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'no-df' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'random-id' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'reassemble' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		);

	protected $typeScrub= array(
		'min-ttl' => array(
			'regex' => RE_NUM,
			),
		'max-mss' => array(
			'regex' => RE_NUM,
			),
		'no-df' => array(
			'regex' => RE_BOOL,
			),
		'random-id' => array(
			'regex' => RE_BOOL,
			),
		'reassemble' => array(
			'regex' => RE_REASSEMBLE_TCP,
			),
		);

	function __construct($str)
	{
		$this->keywords= $this->keyScrub;

		$this->typedef= $this->typeScrub;

		parent::__construct($str);
	}

	function generate()
	{
		$this->genAction();

		$this->genFilterHead();
		$this->genScrub();
		$this->genFilterOpts();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}

	function genScrub()
	{
		$this->str.= ' scrub';
		$opt= '';
		if (isset($this->rule['no-df'])) {
			$opt.= 'no-df';
		}
		if (isset($this->rule['min-ttl'])) {
			$opt.= ', min-ttl ' . $this->rule['min-ttl'];
		}
		if (isset($this->rule['max-mss'])) {
			$opt.= ', max-mss ' . $this->rule['max-mss'];
		}
		if (isset($this->rule['random-id'])) {
			$opt.= ', random-id';
		}
		if (isset($this->rule['reassemble'])) {
			$opt.= ', reassemble ' . $this->rule['reassemble'];
		}
		if ($opt !== '') {
			$this->str.= ' (' . trim($opt, ' ,') . ')';
		}
	}
}
?>
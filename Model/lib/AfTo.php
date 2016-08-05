<?php
/* $pfre: AfTo.php,v 1.1 2016/08/04 14:42:53 soner Exp $ */

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

class AfTo extends Filter
{
	protected $typeNatBase= array(
		'rediraf' => array(
			'regex' => '^(inet|inet6)$',
			),
		'toredirhost' => array(
			'regex' => '^[\w_.\/\-*:$<>!()]{0,50}$',
			),
		);

	function __construct($str)
	{
		$this->keywords = array(
			'af-to' => array(
				'method' => 'parseAfto',
				'params' => array(),
				),
			);

		$this->typedef= $this->typeNatBase;

		parent::__construct($str);
	}

	function parseAfto()
	{
		$this->rule['rediraf']= $this->words[++$this->index];

		if ($this->words[$this->index + 1] === 'from') {
			$this->index+= 2;
			$this->rule['redirhost']= $this->words[$this->index];

			if ($this->words[$this->index + 1] === 'to') {
				$this->index+= 2;
				$this->rule['toredirhost']= $this->words[$this->index];
			}
		}
	}

	function generate()
	{
		$this->genAction();

		$this->genFilterHead();
		$this->genFilterOpts();

		$this->genAfto();
		// @todo Can we have pooltype with af-to? BNF says no, but pfctl does not complain about it

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
	
	function genAfto()
	{
		$this->str.= ' af-to';
		$this->genValue('rediraf');
		$this->genValue('redirhost', 'from ');
		$this->genValue('toredirhost', 'to ');
	}
}
?>

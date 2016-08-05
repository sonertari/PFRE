<?php
/* $pfre: Macro.php,v 1.2 2016/08/04 16:44:37 soner Exp $ */

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

class Macro extends Rule
{
	protected $typeMacro= array(
		'identifier' => array(
			'require' => TRUE,
			'regex' => '^\w[\w_.\/\-*]{0,50}$',
			),
		'value' => array(
			'require' => TRUE,
			'multi' => TRUE,
			'regex' => '^(\w|\$)[\w_.\/\-*]{0,50}$',
			),
		);

	function __construct($str)
	{
		$this->typedef = array_merge(
			$this->typeMacro,
			$this->typeComment
			);

		parent::__construct($str);
	}

	function parse($str)
	{
		$this->str= $str;
		$this->init();
		$this->parseComment();
		$this->sanitize();
		$this->split();

		$this->index= 0;
		$this->rule['identifier']= $this->words[$this->index++];
		if ($this->words[++$this->index] != '{') {
			$this->rule['value']= $this->words[$this->index];
		} else {
			while (preg_replace('/,/', '', $this->words[++$this->index]) != '}' && !$this->isEndOfWords()) {
				$this->rule['value'][]= $this->words[$this->index];
			}
		}
	}

	function sanitize()
	{
		$this->str= preg_replace('/{/', ' { ', $this->str);
		$this->str= preg_replace('/}/', ' } ', $this->str);
		$this->str= preg_replace('/\(/', ' ( ', $this->str);
		$this->str= preg_replace('/\)/', ' ) ', $this->str);
		$this->str= preg_replace('/,/', ' , ', $this->str);
		$this->str= preg_replace('/=/', ' = ', $this->str);
		$this->str= preg_replace('/"/', '', $this->str);
	}

	function generate()
	{
		$this->str= $this->rule['identifier'] . ' = "';

		if (!is_array($this->rule['value'])) {
			$this->str.= $this->rule['value'];
		} else {
			$this->str.= '{ ' . implode(', ', $this->rule['value']) . ' }';
		}
		$this->str.= '"';
		
		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
}
?>
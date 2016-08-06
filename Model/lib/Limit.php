<?php 
/* $pfre: Limit.php,v 1.2 2016/08/05 22:30:06 soner Exp $ */

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

class Limit extends Rule
{
	protected $keyLimit= array(
		'states' => array(
			'method' => 'parseLimit',
			'params' => array(),
			),
		'frags' => array(
			'method' => 'parseLimit',
			'params' => array(),
			),
		'src-nodes' => array(
			'method' => 'parseLimit',
			'params' => array(),
			),
		'tables' => array(
			'method' => 'parseLimit',
			'params' => array(),
			),
		'table-entries' => array(
			'method' => 'parseLimit',
			'params' => array(),
			),
		);

	protected $typeLimit= array(
		'limit' => array(
			'values' => array(
				'states' => array(
					'regex' => RE_NUM,
					),
				'frags' => array(
					'regex' => RE_NUM,
					),
				'src-nodes' => array(
					'regex' => RE_NUM,
					),
				'tables' => array(
					'regex' => RE_NUM,
					),
				'table-entries' => array(
					'regex' => RE_NUM,
					),
				),
			),
		);

	function __construct($str)
	{
		$this->keywords= $this->keyLimit;

		$this->typedef = array_merge(
			$this->typeLimit,
			$this->typeComment
			);

		parent::__construct($str);
	}

	function sanitize()
	{
		$this->str= preg_replace('/{/', ' { ', $this->str);
		$this->str= preg_replace('/}/', ' } ', $this->str);
		$this->str= preg_replace('/\(/', ' ( ', $this->str);
		$this->str= preg_replace('/\)/', ' ) ', $this->str);
		$this->str= preg_replace('/,/', ' , ', $this->str);
		$this->str= preg_replace('/"/', ' " ', $this->str);
	}

	function parseLimit()
	{
		$this->rule['limit'][$this->words[$this->index]]= $this->words[++$this->index];
	}

	function generate()
	{
		$this->str= '';

		if (count($this->rule['limit'])) {
			reset($this->rule['limit']);

			if (count($this->rule['limit']) == 1) {
				list($key, $val)= each($this->rule['limit']);
				$this->str.= "set limit $key $val";
			} else {
				$this->str= 'set limit {';
				while (list($key, $val)= each($this->rule['limit'])) {
					$this->str.= " $key $val,";
				}
				$this->str= rtrim($this->str, ',');
				$this->str.= ' }';
			}
		}

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
}
?>
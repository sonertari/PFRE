<?php
/* $pfre: Table.php,v 1.6 2016/08/10 06:03:14 soner Exp $ */

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

class Table extends Rule
{
	protected $keyTable= array(
		'table' => array(
			'method' => 'parseDelimitedStr',
			'params' => array('identifier', '<', '>'),
			),
		'persist' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'const' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'counters' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'file' => array(
			'method' => 'parseFile',
			'params' => array(),
			),
		'{' => array(
			'method' => 'parseData',
			'params' => array(),
			),
		);

	protected $typeTable= array(
		'identifier' => array(
			'require' => TRUE,
			'regex' => RE_ID,
			),
		'persist' => array(
			'regex' => RE_BOOL,
			),
		'const' => array(
			'regex' => RE_BOOL,
			),
		'counters' => array(
			'regex' => RE_BOOL,
			),
		'file' => array(
			'multi' => TRUE,
			'func' => 'IsFilePath',
			),
		'data' => array(
			'multi' => TRUE,
			'regex' => RE_TABLE_ADDRESS,
			),
		);

	function __construct($str)
	{
		$this->keywords= $this->keyTable;

		$this->typedef = array_merge(
			$this->typeTable,
			$this->typeComment
			);

		parent::__construct($str);
	}

	function sanitize()
	{
		$this->str= preg_replace('/{/', ' { ', $this->str);
		$this->str= preg_replace('/}/', ' } ', $this->str);
		$this->str= preg_replace('/</', ' < ', $this->str);
		$this->str= preg_replace('/>/', ' > ', $this->str);
		$this->str= preg_replace('/,/', ' , ', $this->str);
	}

	function parseFile()
	{
		$filename= preg_replace('/"/', '', $this->words[++$this->index]);
		if (!isset($this->rule['file'])) {
			$this->rule['file']= $filename;
		} else {
			if (!is_array($this->rule['file'])) {
				$tmp= $this->rule['file'];
				unset($this->rule['file']);
				$this->rule['file'][]= $tmp;
			}
			$this->rule['file'][]= $filename;
		}
	}

	function parseData()
	{
		while (preg_replace('/[\s,]+/', '', $this->words[++$this->index]) != '}' && !$this->isEndOfWords()) {
			$this->rule['data'][]= $this->words[$this->index];
		}
	}

	function generate()
	{
		$this->str= 'table <' . $this->rule['identifier'] . '>';
		$this->genKey('persist');
		$this->genKey('const');
		$this->genKey('counters');
		$this->genFiles();
		$this->genData();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
	
	function genFiles()
	{
		if (isset($this->rule['file'])) {
			if (!is_array($this->rule['file'])) {
				$this->str.= ' file "' . $this->rule['file'] . '"';
			} else {
				foreach ($this->rule['file'] as $file) {
					$this->str.= ' file "' . $file . '"';
				}
			}
		}
	}

	function genData()
	{
		if (isset($this->rule['data'])) {
			$this->str.= ' { ';
			if (!is_array($this->rule['data'])) {
				$this->str.= $this->rule['data'];
			} else {
				$this->str.= implode(', ', $this->rule['data']);
			}
			$this->str.= ' }';
		}
	}
}
?>
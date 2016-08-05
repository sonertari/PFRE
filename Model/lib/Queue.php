<?php
/* $pfre: Queue.php,v 1.1 2016/08/04 14:42:52 soner Exp $ */

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

class Queue extends Rule
{
	protected $keyQueue= array(
		'queue' => array(
			'method' => 'parseNextNVP',
			'params' => array('name'),
			),
		'parent' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'bandwidth' => array(
			'method' => 'parseBandwidth',
			'params' => array('bw-burst', 'bw-time'),
			),
		'min' => array(
			'method' => 'parseBandwidth',
			'params' => array('min-burst', 'min-time'),
			),
		'max' => array(
			'method' => 'parseBandwidth',
			'params' => array('max-burst', 'max-time'),
			),
		'qlimit' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'default' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		);

	protected $typeQueue= array(
		'name' => array(
			'func' => 'IsName',
			),
		'parent' => array(
			'func' => 'IsName',
			),
		'bandwidth' => array(
			'regex' => '^\w{1,16}(|K|M|G)$',
			),
		'bw-burst' => array(
			'regex' => '^\w{1,16}(|K|M|G)$',
			),
		'bw-time' => array(
			'regex' => '^\w{1,16}ms$',
			),
		'min' => array(
			'regex' => '^\w{1,16}(|K|M|G)$',
			),
		'min-burst' => array(
			'regex' => '^\w{1,16}(|K|M|G)$',
			),
		'min-time' => array(
			'regex' => '^\w{1,16}ms$',
			),
		'max' => array(
			'regex' => '^\w{1,16}(|K|M|G)$',
			),
		'max-burst' => array(
			'regex' => '^\w{1,16}(|K|M|G)$',
			),
		'max-time' => array(
			'regex' => '^\w{1,16}ms$',
			),
		'qlimit' => array(
			'func' => 'IsNumber',
			),
		'default' => array(
			'func' => 'IsBool',
			),
		);

	function __construct($str)
	{
		$this->keywords= array_merge(
			$this->keyInterface,
			$this->keyQueue
			);

		$this->typedef = array_merge(
			$this->typeInterface,
			$this->typeQueue,
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
	}

	function parseBandwidth($burst, $time)
	{
		$this->parseNextValue();

		/// @todo Fix this possible off-by-N errors
		if ($this->words[$this->index + 1] == 'burst') {
			$this->index+= 2;
			$this->rule[$burst]= $this->words[$this->index];
		}
		if ($this->words[$this->index + 1] == 'for') {
			$this->index+= 2;
			$this->rule[$time]= $this->words[$this->index];
		}
	}

	function generate()
	{
		$this->str= 'queue ' . $this->rule['name'];
		$this->genInterface();
		$this->genValue('parent', 'parent ');
		$this->genBandwidth('bandwidth', 'bw');
		$this->genBandwidth('min', 'min');
		$this->genBandwidth('max', 'max');
		$this->genValue('qlimit', 'qlimit ');
		$this->genKey('default');

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
	
	function genBandwidth($key, $pre)
	{
		if (isset($this->rule[$key])) {
			$this->str.= " $key " . $this->rule[$key] . ($this->rule["$pre-burst"] ? ' burst ' . $this->rule["$pre-burst"] : '') . ($this->rule["$pre-time"] ? ' for ' . $this->rule["$pre-time"] : '');
		}
	}
}
?>
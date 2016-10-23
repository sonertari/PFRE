<?php
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

class Antispoof extends Rule
{
	protected $keyAntispoof= array(
		'for' => array(
			'method' => 'parseItems',
			'params' => array('interface'),
			),
		'label' => array(
			'method' => 'parseDelimitedStr',
			'params' => array('label'),
			),
		);

	protected $typeLabel= array(
		'label' => array(
			'regex' => RE_NAME,
			),
		);

	function __construct($str)
	{
		$this->keywords= array_merge(
			$this->keyLog,
			$this->keyQuick,
			$this->keyAf,
			$this->keyAntispoof
			);

		$this->typedef= array_merge(
			$this->typeLog,
			$this->typeQuick,
			$this->typeAf,
			$this->typeInterface,
			$this->typeLabel,
			$this->typeComment
			);

		parent::__construct($str);
	}

	function generate()
	{
		$this->str= 'antispoof';

		$this->genLog();
		$this->genKey('quick');
		$this->genItems('interface', 'for');
		$this->genValue('af');
		$this->genValue('label', 'label "', '"');

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
}
?>

<?php
/* $pfre: QueueTest.php,v 1.3 2016/08/11 18:29:20 soner Exp $ */

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

namespace ModelTest;

require_once('Rule.php');

class QueueTest extends Rule
{
	protected $inQueue= 'queue test on em0 parent rootq bandwidth 20M burst 90M for 100ms min 5M burst 10M for 50ms max 100M burst 1M for 10ms qlimit 100 default';
	protected $ruleQueue= array(
		'name' => 'test',
		'interface' => 'em0',
		'parent' => 'rootq',
		'bandwidth' => '20M',
		'bw-burst' => '90M',
		'bw-time' => '100ms',
		'min' => '5M',
		'min-burst' => '10M',
		'min-time' => '50ms',
		'max' => '100M',
		'max-burst' => '1M',
		'max-time' => '10ms',
		'qlimit' => '100',
		'default' => TRUE,
		);

	function __construct()
	{
		$this->rule= $this->ruleQueue;

		parent::__construct();

		$this->in= $this->inQueue . $this->inComment;
		$this->out= $this->in . "\n";
	}
}
?>
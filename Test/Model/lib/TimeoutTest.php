<?php
/* $pfre: TimeoutTest.php,v 1.2 2016/08/10 09:31:57 soner Exp $ */

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

require_once('Rule.php');

class TimeoutTest extends RuleTest
{
	protected $inTimeout= 'frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19';
	protected $ruleTimeout= array(
		'timeout' => array(
			'all' => array(
				'frag' => '1',
				'interval' => '2',
				'src.track' => '3',
				),
			'tcp' => array(
				'first' => '4',
				'opening' => '5',
				'established' => '6',
				'closing' => '7',
				'finwait' => '8',
				'closed' => '9',
				),
			'udp' => array(
				'first' => '10',
				'single' => '11',
				'multiple' => '12',
				),
			'icmp' => array(
				'first' => '13',
				'error' => '14',
				),
			'other' => array(
				'first' => '15',
				'single' => '16',
				'multiple' => '17',
				),
			'adaptive' => array(
				'start' => '18',
				'end' => '19',
				),
			),
		);

	function __construct()
	{
		$this->rule= array_merge(
			$this->rule,
			$this->ruleTimeout
			);

		parent::__construct();

		$this->in= 'set timeout { ' . $this->inTimeout . ' }' . $this->inComment;
		$this->out= $this->in . "\n";
	}
}
?>
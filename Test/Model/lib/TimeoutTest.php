<?php
/* $pfre$ */

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
	protected $ruleTimeout= 'frag 0, interval 1, src.track 2, tcp.first 3, tcp.opening 4, tcp.established 5, tcp.closing 6, tcp.finwait 7, tcp.closed 8, udp.first 9, udp.single 10, udp.multiple 11, icmp.first 12, icmp.error 13, other.first 14, other.single 15, other.multiple 16, adaptive.start 17, adaptive.end 18';
	protected $sampleTimeout= array(
		'timeout' => array(
			'all' => array(
				'frag' => '0',
				'interval' => '1',
				'src.track' => '2',
				),
			'tcp' => array(
				'first' => '3',
				'opening' => '4',
				'established' => '5',
				'closing' => '6',
				'finwait' => '7',
				'closed' => '8',
				),
			'udp' => array(
				'first' => '9',
				'single' => '10',
				'multiple' => '11',
				),
			'icmp' => array(
				'first' => '12',
				'error' => '13',
				),
			'other' => array(
				'first' => '14',
				'single' => '15',
				'multiple' => '16',
				),
			'adaptive' => array(
				'start' => '17',
				'end' => '18',
				),
			),
		);

	function __construct()
	{
		$this->sample= array_merge(
			$this->sample,
			$this->sampleTimeout
			);

		parent::__construct();

		$this->rule= 'set timeout { ' . $this->ruleTimeout . ' }' . $this->ruleComment;
	}
}
?>
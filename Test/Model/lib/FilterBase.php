<?php
/* $pfre: FilterBase.php,v 1.2 2016/08/10 09:31:57 soner Exp $ */

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

require_once('StateTest.php');

class FilterBaseTest extends StateTest
{
	protected $inDirection= 'in';
	protected $ruleDirection= array(
		'direction' => 'in',
		);

	protected $inProto= 'proto tcp';
	protected $ruleProto= array(
		'proto' => 'tcp',
		);

	protected $inSrcDest= 'from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh';
	protected $ruleSrcDest= array(
		'from' => '192.168.0.1',
		'fromport' => array(
			'ssh',
			'2222',
			),
		'os' => 'openbsd',
		'to' => '192.168.0.2',
		'toport' => 'ssh',
		);

	protected $inFilterOpts= 'user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 set queue (std, service) rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state';
	protected $ruleFilterOpts= array(
		'user' => 'root',
		'group' => 'wheel',
		'flags' => 'S/SA',
		'tos' => '1',
		'state-filter' => 'keep',
		'allow-opts' => TRUE,
		'once' => TRUE,
		'label' => 'test',
		'tag' => 'test',
		'tagged' => 'test',
		'not-tagged' => TRUE,
		'set-prio' => '2',
		'queue' => array(
			'std',
			'service',
			),
		'rtable' => '3',
		'probability' => '10%',
		'prio' => '4',
		'set-tos' => '5',
		'received-on' => 'em0',
		'not-received-on' => TRUE,
		);

	function __construct()
	{
		$this->rule= array_merge(
			$this->rule,
			$this->ruleDirection,
			$this->ruleInterface,
			$this->ruleAf,
			$this->ruleProto,
			$this->ruleSrcDest,
			$this->ruleFilterOpts
			);

		parent::__construct();

		$this->inFilterOpts.= ' ( ' . $this->inState . ', ' . $this->inTimeout . ' )';
	}
}
?>
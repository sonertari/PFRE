<?php
/* $pfre: FilterTest.php,v 1.1 2016/08/10 04:39:43 soner Exp $ */

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

require_once('FilterBase.php');

class FilterTest extends FilterBaseTest
{
	protected $ruleFilterHead= '';

	protected $ruleAction= 'pass';
	protected $sampleAction= array(
		'action' => 'pass',
		);

	protected $sampleType= array(
		);

	protected $ruleRedirHost= '192.168.0.1';
	protected $sampleRedirHost= array(
		'redirhost' => '192.168.0.1',
		);

	protected $rulePoolType= 'source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address';
	protected $samplePoolType= array(
		'source-hash' => TRUE,
		'source-hash-key' => '09f1cbe02e2f4801b433ba9fab728903',
		'sticky-address' => TRUE,
		);

	protected $ruleDivertPort= 'port ssh';
	protected $sampleDivertPort= array(
		'divertport' => 'ssh',
		);

	/// @todo Test rdomain
	function __construct()
	{
		$this->sample= array_merge(
			$this->sample,
			$this->sampleAction,
			$this->sampleLog,
			$this->sampleQuick,
			$this->sampleType
			);

		parent::__construct();

		$this->ruleFilterHead= $this->ruleAction . ' ' . $this->ruleDirection . ' ' . $this->ruleLog . ' ' . $this->ruleQuick . ' ' . $this->ruleInterface . ' ' . $this->ruleAf . ' ' . $this->ruleProto . ' ' . $this->ruleSrcDest;

		$this->rule= $this->ruleFilterHead . ' ' . $this->ruleFilterOpts . $this->ruleComment;
	}
}
?>
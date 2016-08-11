<?php
/* $pfre: Rule.php,v 1.3 2016/08/11 18:29:21 soner Exp $ */

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

require_once('RuleBase.php');

class Rule extends RuleBase
{
	protected $inInterface= 'on em0';
	protected $ruleInterface= array(
		'interface' => 'em0',
		);

	protected $inAf= 'inet';
	protected $ruleAf= array(
		'af' => 'inet',
		);

	protected $inLog= 'log ( all, matches, user, to pflog0 )';
	protected $ruleLog= array(
		'log' => array(
			'all' => TRUE,
			'matches' => TRUE,
			'user' => TRUE,
			'to' => 'pflog0',
			),
		);

	protected $inQuick= 'quick';
	protected $ruleQuick= array(
		'quick' => TRUE,
		);

	protected $inComment= ' # Test';
	protected $ruleComment= array(
		'comment' => 'Test',
		);

	function __construct()
	{
		$this->rule= array_merge(
			$this->rule,
			$this->ruleComment
			);

		parent::__construct();
	}
}
?>
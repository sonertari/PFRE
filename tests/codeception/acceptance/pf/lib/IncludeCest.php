<?php 
/* $pfre: IncludeCest.php,v 1.1 2016/08/15 12:51:14 soner Exp $ */

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

require_once ('Rule.php');

class IncludeCest extends Rule
{
	protected $type= 'Include';
	protected $ruleNumber= 19;
	protected $ruleNumberGenerated= 25;
	protected $sender= 'include';

	protected $origRule= 'include "/etc/pfre/include.conf" # Test';
	protected $expectedDispOrigRule= 'include /etc/pfre/include.conf Test e u d x';

	protected $modifiedRule= 'ERROR: Cannot generate rule';
	protected $expectedDispModifiedRule= 'include Test1 e u d x';

	function __construct()
	{
		parent::__construct();

		$this->revertedRule= $this->modifiedRule;
		$this->generatedRule= 'include "" # Test1';
	}

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->selectOption('#file', '');
		$I->click('Apply');

		// Force generate, otherwise we get 'ERROR: Cannot generate rule' instead
		// Check #forcegenerate after clicking Apply, otherwise it is disabled (rule is not modified yet)
		$I->checkOption('#forcegenerate');

		$this->clickApplySeeResult($I, 'include "" # Test');

		$I->fillField('comment', 'Test1');
		$this->clickApplySeeResult($I, 'include "" # Test1');

		// Uncheck #forcegenerate, so the base methods get 'ERROR: Cannot generate rule'
		$I->uncheckOption('#forcegenerate');
		$I->click('Apply');
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		// Cannot select the orig file, so pick none
		$I->selectOption('#file', '');
		$I->click('Apply');

		$I->checkOption('#forcegenerate');

		$I->fillField('comment', 'Test');
		$this->clickApplySeeResult($I, 'include "" # Test');

		$I->uncheckOption('#forcegenerate');
		$I->click('Apply');
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->selectOption('#file', '');
		$I->fillField('comment', 'Test1');
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->selectOption('#file', '');
		$I->fillField('comment', 'Test');
		$I->click('Apply');
	}
}
?>
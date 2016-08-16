<?php 
/* $pfre: OptionSkipCest.php,v 1.1 2016/08/16 02:23:25 soner Exp $ */

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

class OptionSkipCest extends Rule
{
	protected $type= 'Option';
	protected $ruleNumber= 14;
	protected $ruleNumberGenerated= 20;
	protected $sender= 'option';

	protected $origRule= 'set skip on { lo, em0 } # Test';
	protected $expectedDispOrigRule= 'skip on lo, em0 Test e u d x';

	protected $modifiedRule= ' # Test1';
	protected $expectedDispModifiedRule= 'skip on Test1 e u d x';

	protected function modifyRule(AcceptanceTester $I)
	{
		$this->clickDeleteLink($I, 'delSkip', 'lo');
		$this->seeResult($I, 'set skip on em0 # Test');

		$this->clickDeleteLink($I, 'delSkip', 'em0');
		$this->seeResult($I, ' # Test');

		$I->fillField('#comment', 'Test1');
		$this->clickApplySeeResult($I, $this->modifiedRule);
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->fillField('#addSkip', 'lo');
		$this->clickApplySeeResult($I, 'set skip on lo # Test1');

		$I->fillField('#addSkip', 'em0');
		$this->clickApplySeeResult($I, 'set skip on { lo, em0 } # Test1');

		$I->fillField('#comment', 'Test');
		$this->clickApplySeeResult($I, $this->revertedRule);
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$this->clickDeleteLink($I, 'delSkip', 'lo');
		$this->clickDeleteLink($I, 'delSkip', 'em0');
		$I->fillField('#comment', 'Test1');
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->fillField('#addSkip', 'lo');
		$I->click('Apply');

		$I->fillField('#addSkip', 'em0');
		$I->fillField('#comment', 'Test');
		$I->click('Apply');
	}
}
?>
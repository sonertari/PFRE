<?php 
/* $pfre: AntispoofCest.php,v 1.1 2016/08/15 20:05:28 soner Exp $ */

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

class AntispoofCest extends Rule
{
	protected $type= 'Antispoof';
	protected $ruleNumber= 1;
	protected $ruleNumberGenerated= 1;
	protected $sender= 'antispoof';

	protected $origRule= 'antispoof log ( all, matches, user, to pflog0 ) quick for em0 inet label "test" # Test';
	protected $expectedDispOrigRule= 'em0
quick inet
log all, matches, user, to=pflog0 test
Test e u d x';

	protected $modifiedRule= 'antispoof # Test1';
	protected $expectedDispModifiedRule= 'Test1 e u d x';

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->fillField('#log-to', '');
		$this->clickApplySeeResult($I, 'antispoof log ( all, matches, user ) quick for em0 inet label "test" # Test');

		$I->uncheckOption('#log-all');
		$this->clickApplySeeResult($I, 'antispoof log ( matches, user ) quick for em0 inet label "test" # Test');

		$I->uncheckOption('#log-matches');
		$this->clickApplySeeResult($I, 'antispoof log ( user ) quick for em0 inet label "test" # Test');

		$I->uncheckOption('#log-user');
		$this->clickApplySeeResult($I, 'antispoof log quick for em0 inet label "test" # Test');

		$I->uncheckOption('#log');
		$this->clickApplySeeResult($I, 'antispoof quick for em0 inet label "test" # Test');

		$I->uncheckOption('#quick');
		$this->clickApplySeeResult($I, 'antispoof for em0 inet label "test" # Test');

		$this->clickDeleteLink($I, 'delInterface', 'em0');
		$this->seeResult($I, 'antispoof inet label "test" # Test');

		$I->selectOption('#af', '');
		$this->clickApplySeeResult($I, 'antispoof label "test" # Test');

		$I->fillField('#label', '');
		$this->clickApplySeeResult($I, 'antispoof # Test');

		$I->fillField('#comment', 'Test1');
		$this->clickApplySeeResult($I, $this->modifiedRule);
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->checkOption('#log');
		$this->clickApplySeeResult($I, 'antispoof log # Test1');

		$I->fillField('#log-to', 'pflog0');
		$this->clickApplySeeResult($I, 'antispoof log ( to pflog0 ) # Test1');

		$I->checkOption('#log-all');
		$this->clickApplySeeResult($I, 'antispoof log ( all, to pflog0 ) # Test1');

		$I->checkOption('#log-matches');
		$this->clickApplySeeResult($I, 'antispoof log ( all, matches, to pflog0 ) # Test1');

		$I->checkOption('#log-user');
		$this->clickApplySeeResult($I, 'antispoof log ( all, matches, user, to pflog0 ) # Test1');

		$I->checkOption('#quick');
		$this->clickApplySeeResult($I, 'antispoof log ( all, matches, user, to pflog0 ) quick # Test1');

		$I->fillField('#addInterface', 'em0');
		$this->clickApplySeeResult($I, 'antispoof log ( all, matches, user, to pflog0 ) quick for em0 # Test1');

		$I->selectOption('#af', 'inet');
		$this->clickApplySeeResult($I, 'antispoof log ( all, matches, user, to pflog0 ) quick for em0 inet # Test1');

		$I->fillField('#label', 'test');
		$this->clickApplySeeResult($I, 'antispoof log ( all, matches, user, to pflog0 ) quick for em0 inet label "test" # Test1');

		$I->fillField('#comment', 'Test');
		$this->clickApplySeeResult($I, $this->revertedRule);
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->fillField('#log-to', '');
		$I->uncheckOption('#log-all');
		$I->uncheckOption('#log-matches');
		$I->uncheckOption('#log-user');
		$I->uncheckOption('#log');
		$I->uncheckOption('#quick');
		$I->click('Apply');

		$this->clickDeleteLink($I, 'delInterface', 'em0');

		$I->selectOption('#af', '');
		$I->fillField('#label', '');
		$I->fillField('#comment', 'Test1');
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->checkOption('#log');
		$I->click('Apply');

		$I->fillField('#log-to', 'pflog0');
		$I->checkOption('#log-all');
		$I->checkOption('#log-matches');
		$I->checkOption('#log-user');
		$I->checkOption('#quick');
		$I->fillField('#addInterface', 'em0');
		$I->selectOption('#af', 'inet');
		$I->fillField('#label', 'test');
		$I->fillField('#comment', 'Test');
		$I->click('Apply');
	}
}
?>
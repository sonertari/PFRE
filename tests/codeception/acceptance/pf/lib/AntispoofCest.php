<?php 
/*
 * Copyright (C) 2004-2020 Soner Tari
 *
 * This file is part of PFRE.
 *
 * PFRE is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PFRE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PFRE.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once ('Rule.php');

class AntispoofCest extends Rule
{
	protected $type= 'Antispoof';
	protected $ruleNumber= 1;
	protected $lineNumber= 1;
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
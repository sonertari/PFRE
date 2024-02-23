<?php 
/*
 * Copyright (C) 2004-2024 Soner Tari
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

class OptionSkipCest extends Rule
{
	protected $type= 'Option';
	protected $ruleNumber= 14;
	protected $lineNumber= 20;
	protected $sender= 'option';

	protected $origRule= 'set skip on { lo, em0 } # Test';
	protected $expectedDispOrigRule= 'skip on lo, em0 Test';

	protected $modifiedRule= ' # Test1';
	protected $expectedDispModifiedRule= 'skip on Test1';

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
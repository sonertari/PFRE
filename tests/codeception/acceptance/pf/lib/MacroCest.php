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

class MacroCest extends Rule
{
	protected $type= 'Macro';
	protected $ruleNumber= 3;
	protected $lineNumber= 9;
	protected $sender= 'macro';

	protected $origRule= 'test = "{ ssh, 2222 }" # Test';
	protected $expectedDispOrigRule= 'test
ssh
2222
Test';

	protected $modifiedRule= 'test1 = "{ ssh, 2222, 1111 }" # Test1';
	protected $expectedDispModifiedRule= 'test1
ssh
2222
1111
Test1';

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->fillField('identifier', 'test1');
		$this->clickApplySeeResult($I, 'test1 = "{ ssh, 2222 }" # Test');

		$I->fillField('addValue', '1111');
		$this->clickApplySeeResult($I, 'test1 = "{ ssh, 2222, 1111 }" # Test');

		$I->fillField('comment', 'Test1');
		$this->clickApplySeeResult($I, $this->modifiedRule);
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->fillField('identifier', 'test');
		$this->clickApplySeeResult($I, 'test = "{ ssh, 2222, 1111 }" # Test1');
		
		$this->clickDeleteLink($I, 'delValue', '1111');
		$this->seeResult($I, 'test = "{ ssh, 2222 }" # Test1');

		$I->fillField('comment', 'Test');
		$this->clickApplySeeResult($I, $this->revertedRule);
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->fillField('identifier', 'test1');
		$I->fillField('addValue', '1111');
		$I->fillField('comment', 'Test1');

		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->fillField('identifier', 'test');
		$I->click('Apply');
		
		$this->clickDeleteLink($I, 'delValue', '1111');

		$I->fillField('comment', 'Test');
		$I->click('Apply');
	}
}
?>
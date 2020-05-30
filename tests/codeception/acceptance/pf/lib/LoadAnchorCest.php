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

class LoadAnchorCest extends Rule
{
	protected $type= 'LoadAnchor';
	protected $ruleNumber= 18;
	protected $lineNumber= 24;
	protected $sender= 'loadanchor';

	protected $origRule= 'load anchor test from "/etc/pfre/include.conf" # Test';
	protected $expectedDispOrigRule= 'test /etc/pfre/include.conf Test e u d x';

	protected $modifiedRule= 'load anchor test1 from "/etc/pfre/test.conf" # Test1';
	protected $expectedDispModifiedRule= 'test1 /etc/pfre/test.conf Test1 e u d x';

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->fillField('#anchor', 'test1');
		$this->clickApplySeeResult($I, 'load anchor test1 from "/etc/pfre/include.conf" # Test');

		$I->fillField('#file', '/etc/pfre/test.conf');
		$this->clickApplySeeResult($I, 'load anchor test1 from "/etc/pfre/test.conf" # Test');

		$I->fillField('#comment', 'Test1');
		$this->clickApplySeeResult($I, $this->modifiedRule);
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->fillField('#anchor', 'test');
		$this->clickApplySeeResult($I, 'load anchor test from "/etc/pfre/test.conf" # Test1');

		$I->fillField('#file', '/etc/pfre/include.conf');
		$this->clickApplySeeResult($I, 'load anchor test from "/etc/pfre/include.conf" # Test1');

		$I->fillField('#comment', 'Test');
		$this->clickApplySeeResult($I, $this->revertedRule);
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->fillField('#anchor', 'test1');
		$I->fillField('#file', '/etc/pfre/test.conf');
		$I->fillField('#comment', 'Test1');
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->fillField('#anchor', 'test');
		$I->fillField('#file', '/etc/pfre/include.conf');
		$I->fillField('#comment', 'Test');
		$I->click('Apply');
	}
}
?>
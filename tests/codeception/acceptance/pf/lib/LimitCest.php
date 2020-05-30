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

class LimitCest extends Rule
{
	protected $type= 'Limit';
	protected $ruleNumber= 16;
	protected $lineNumber= 22;
	protected $sender= 'limit';

	protected $origRule= 'set limit { states 1, frags 2, src-nodes 3, tables 4, table-entries 5 } # Test';
	protected $expectedDispOrigRule= 'states: 1, frags: 2, src-nodes: 3, tables: 4, table-entries: 5 Test e u d x';

	protected $modifiedRule= ' # Test1';
	protected $expectedDispModifiedRule= 'Test1 e u d x';

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->fillField('#states', '');
		$this->clickApplySeeResult($I, 'set limit { frags 2, src-nodes 3, tables 4, table-entries 5 } # Test');

		$I->fillField('#frags', '');
		$this->clickApplySeeResult($I, 'set limit { src-nodes 3, tables 4, table-entries 5 } # Test');

		$I->fillField('#srcnodes', '');
		$this->clickApplySeeResult($I, 'set limit { tables 4, table-entries 5 } # Test');

		$I->fillField('#tables', '');
		$this->clickApplySeeResult($I, 'set limit table-entries 5 # Test');

		$I->fillField('#table-entries', '');
		$this->clickApplySeeResult($I, ' # Test');

		$I->fillField('#comment', 'Test1');
		$this->clickApplySeeResult($I, $this->modifiedRule);
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->fillField('#states', '1');
		$this->clickApplySeeResult($I, 'set limit states 1 # Test1');

		$I->fillField('#frags', '2');
		$this->clickApplySeeResult($I, 'set limit { states 1, frags 2 } # Test1');

		$I->fillField('#srcnodes', '3');
		$this->clickApplySeeResult($I, 'set limit { states 1, frags 2, src-nodes 3 } # Test1');

		$I->fillField('#tables', '4');
		$this->clickApplySeeResult($I, 'set limit { states 1, frags 2, src-nodes 3, tables 4 } # Test1');

		$I->fillField('#table-entries', '5');
		$this->clickApplySeeResult($I, 'set limit { states 1, frags 2, src-nodes 3, tables 4, table-entries 5 } # Test1');

		$I->fillField('#comment', 'Test');
		$this->clickApplySeeResult($I, $this->revertedRule);
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->fillField('#states', '');
		$I->fillField('#frags', '');
		$I->fillField('#srcnodes', '');
		$I->fillField('#tables', '');
		$I->fillField('#table-entries', '');
		$I->fillField('#comment', 'Test1');
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->fillField('#states', '1');
		$I->fillField('#frags', '2');
		$I->fillField('#srcnodes', '3');
		$I->fillField('#tables', '4');
		$I->fillField('#table-entries', '5');
		$I->fillField('#comment', 'Test');
		$I->click('Apply');
	}
}
?>
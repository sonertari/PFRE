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

class TableCest extends Rule
{
	protected $type= 'Table';
	protected $ruleNumber= 4;
	protected $lineNumber= 10;
	protected $sender= 'table';

	protected $origRule= 'table <test> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test';
	protected $expectedDispOrigRule= 'test const persist counters 192.168.0.1
192.168.0.2
file "/etc/pf.restrictedips1"
file "/etc/pf.restrictedips2"
Test';

	protected $modifiedRule= 'table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1';
	protected $expectedDispModifiedRule= 'test1 192.168.0.1
192.168.0.2
1.1.1.1
file "/etc/pf.restrictedips1"
file "/etc/pf.restrictedips2"
file "/etc/pf.restrictedips3"
Test1';

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->fillField('identifier', 'test1');
		$this->clickApplySeeResult($I, 'table <test1> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test');

		$I->uncheckOption('#const');
		$this->clickApplySeeResult($I, 'table <test1> persist counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test');

		$I->uncheckOption('#persist');
		$this->clickApplySeeResult($I, 'table <test1> counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test');

		$I->uncheckOption('#counters');
		$this->clickApplySeeResult($I, 'table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test');
		
		$I->fillField('addValue', '1.1.1.1');
		$this->clickApplySeeResult($I, 'table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test');

		$I->fillField('addFile', '/etc/pf.restrictedips3');
		$this->clickApplySeeResult($I, 'table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test');

		$I->fillField('comment', 'Test1');
		$this->clickApplySeeResult($I, $this->modifiedRule);
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->fillField('identifier', 'test');
		$this->clickApplySeeResult($I, 'table <test> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#const');
		$this->clickApplySeeResult($I, 'table <test> const file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#persist');
		$this->clickApplySeeResult($I, 'table <test> persist const file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#counters');
		$this->clickApplySeeResult($I, 'table <test> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1');
		
		$this->clickDeleteLink($I, 'delValue', '1.1.1.1');
		$this->seeResult($I, 'table <test> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2 } # Test1');

		$this->clickDeleteLink($I, 'delFile', '/etc/pf.restrictedips3');
		$this->seeResult($I, 'table <test> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test1');

		$I->fillField('comment', 'Test');
		$this->clickApplySeeResult($I, $this->revertedRule);
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->fillField('identifier', 'test1');
		$I->uncheckOption('#const');
		$I->uncheckOption('#persist');
		$I->uncheckOption('#counters');
		$I->fillField('addValue', '1.1.1.1');
		$I->fillField('addFile', '/etc/pf.restrictedips3');
		$I->fillField('comment', 'Test1');
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->fillField('identifier', 'test');
		$I->checkOption('#const');
		$I->checkOption('#persist');
		$I->checkOption('#counters');
		$I->click('Apply');
		
		$this->clickDeleteLink($I, 'delValue', '1.1.1.1');
		$this->clickDeleteLink($I, 'delFile', '/etc/pf.restrictedips3');

		$I->fillField('comment', 'Test');
		$I->click('Apply');
	}
}
?>
<?php 
/*
 * Copyright (C) 2004-2021 Soner Tari
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

class QueueCest extends Rule
{
	protected $type= 'Queue';
	protected $ruleNumber= 12;
	protected $lineNumber= 18;
	protected $sender= 'queue';

	protected $origRule= 'queue test on em0 parent rootq bandwidth 20M burst 90M for 100ms min 5M burst 10M for 50ms max 100M burst 1M for 10ms flows 1024 quantum 1 qlimit 100 default # Test';
	protected $expectedDispOrigRule= 'test
em0
rootq
20M
burst: 90M
time: 100ms 5M
burst: 10M
time: 50ms 100M
burst: 1M
time: 10ms 1024
1
100
default Test e u d x';

	protected $modifiedRule= 'queue test1 # Test1';
	/// @todo Check why we have a new line after test1
	protected $expectedDispModifiedRule= 'test1
Test1 e u d x';

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->fillField('#name', 'test1');
		$this->clickApplySeeResult($I, 'queue test1 on em0 parent rootq bandwidth 20M burst 90M for 100ms min 5M burst 10M for 50ms max 100M burst 1M for 10ms flows 1024 quantum 1 qlimit 100 default # Test');

		$I->fillField('#interface', '');
		$this->clickApplySeeResult($I, 'queue test1 parent rootq bandwidth 20M burst 90M for 100ms min 5M burst 10M for 50ms max 100M burst 1M for 10ms flows 1024 quantum 1 qlimit 100 default # Test');

		$I->fillField('#parent', '');
		$this->clickApplySeeResult($I, 'queue test1 bandwidth 20M burst 90M for 100ms min 5M burst 10M for 50ms max 100M burst 1M for 10ms flows 1024 quantum 1 qlimit 100 default # Test');

		$I->fillField('#bw-time', '');
		$this->clickApplySeeResult($I, 'queue test1 bandwidth 20M burst 90M min 5M burst 10M for 50ms max 100M burst 1M for 10ms flows 1024 quantum 1 qlimit 100 default # Test');

		$I->fillField('#bw-burst', '');
		$this->clickApplySeeResult($I, 'queue test1 bandwidth 20M min 5M burst 10M for 50ms max 100M burst 1M for 10ms flows 1024 quantum 1 qlimit 100 default # Test');

		$I->fillField('#bandwidth', '');
		$this->clickApplySeeResult($I, 'queue test1 min 5M burst 10M for 50ms max 100M burst 1M for 10ms flows 1024 quantum 1 qlimit 100 default # Test');

		$I->fillField('#min-time', '');
		$this->clickApplySeeResult($I, 'queue test1 min 5M burst 10M max 100M burst 1M for 10ms flows 1024 quantum 1 qlimit 100 default # Test');

		$I->fillField('#min-burst', '');
		$this->clickApplySeeResult($I, 'queue test1 min 5M max 100M burst 1M for 10ms flows 1024 quantum 1 qlimit 100 default # Test');

		$I->fillField('#min', '');
		$this->clickApplySeeResult($I, 'queue test1 max 100M burst 1M for 10ms flows 1024 quantum 1 qlimit 100 default # Test');

		$I->fillField('#max-time', '');
		$this->clickApplySeeResult($I, 'queue test1 max 100M burst 1M flows 1024 quantum 1 qlimit 100 default # Test');

		$I->fillField('#max-burst', '');
		$this->clickApplySeeResult($I, 'queue test1 max 100M flows 1024 quantum 1 qlimit 100 default # Test');

		$I->fillField('#max', '');
		$this->clickApplySeeResult($I, 'queue test1 flows 1024 quantum 1 qlimit 100 default # Test');

		$I->fillField('#flows', '');
		$this->clickApplySeeResult($I, 'queue test1 quantum 1 qlimit 100 default # Test');

		$I->fillField('#quantum', '');
		$this->clickApplySeeResult($I, 'queue test1 qlimit 100 default # Test');

		$I->fillField('#qlimit', '');
		$this->clickApplySeeResult($I, 'queue test1 default # Test');

		$I->uncheckOption('#default');
		$this->clickApplySeeResult($I, 'queue test1 # Test');

		$I->fillField('#comment', 'Test1');
		$this->clickApplySeeResult($I, $this->modifiedRule);
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->fillField('#name', 'test');
		$this->clickApplySeeResult($I, 'queue test # Test1');

		$I->fillField('#interface', 'em0');
		$this->clickApplySeeResult($I, 'queue test on em0 # Test1');

		$I->fillField('#parent', 'rootq');
		$this->clickApplySeeResult($I, 'queue test on em0 parent rootq # Test1');

		$I->fillField('#bandwidth', '20M');
		$this->clickApplySeeResult($I, 'queue test on em0 parent rootq bandwidth 20M # Test1');

		$I->fillField('#bw-burst', '90M');
		$this->clickApplySeeResult($I, 'queue test on em0 parent rootq bandwidth 20M burst 90M # Test1');

		$I->fillField('#bw-time', '100ms');
		$this->clickApplySeeResult($I, 'queue test on em0 parent rootq bandwidth 20M burst 90M for 100ms # Test1');

		$I->fillField('#min', '5M');
		$this->clickApplySeeResult($I, 'queue test on em0 parent rootq bandwidth 20M burst 90M for 100ms min 5M # Test1');

		$I->fillField('#min-burst', '10M');
		$this->clickApplySeeResult($I, 'queue test on em0 parent rootq bandwidth 20M burst 90M for 100ms min 5M burst 10M # Test1');

		$I->fillField('#min-time', '50ms');
		$this->clickApplySeeResult($I, 'queue test on em0 parent rootq bandwidth 20M burst 90M for 100ms min 5M burst 10M for 50ms # Test1');

		$I->fillField('#max', '100M');
		$this->clickApplySeeResult($I, 'queue test on em0 parent rootq bandwidth 20M burst 90M for 100ms min 5M burst 10M for 50ms max 100M # Test1');

		$I->fillField('#max-burst', '1M');
		$this->clickApplySeeResult($I, 'queue test on em0 parent rootq bandwidth 20M burst 90M for 100ms min 5M burst 10M for 50ms max 100M burst 1M # Test1');

		$I->fillField('#max-time', '10ms');
		$this->clickApplySeeResult($I, 'queue test on em0 parent rootq bandwidth 20M burst 90M for 100ms min 5M burst 10M for 50ms max 100M burst 1M for 10ms # Test1');

		$I->fillField('#flows', '1024');
		$this->clickApplySeeResult($I, 'queue test on em0 parent rootq bandwidth 20M burst 90M for 100ms min 5M burst 10M for 50ms max 100M burst 1M for 10ms flows 1024 # Test1');

		$I->fillField('#quantum', '1');
		$this->clickApplySeeResult($I, 'queue test on em0 parent rootq bandwidth 20M burst 90M for 100ms min 5M burst 10M for 50ms max 100M burst 1M for 10ms flows 1024 quantum 1 # Test1');

		$I->fillField('#qlimit', '100');
		$this->clickApplySeeResult($I, 'queue test on em0 parent rootq bandwidth 20M burst 90M for 100ms min 5M burst 10M for 50ms max 100M burst 1M for 10ms flows 1024 quantum 1 qlimit 100 # Test1');

		$I->checkOption('#default');
		$this->clickApplySeeResult($I, 'queue test on em0 parent rootq bandwidth 20M burst 90M for 100ms min 5M burst 10M for 50ms max 100M burst 1M for 10ms flows 1024 quantum 1 qlimit 100 default # Test1');

		$I->fillField('#comment', 'Test');
		$this->clickApplySeeResult($I, $this->revertedRule);
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->fillField('#name', 'test1');
		$I->fillField('#interface', '');
		$I->fillField('#parent', '');
		$I->fillField('#bw-time', '');
		$I->fillField('#bw-burst', '');
		$I->fillField('#bandwidth', '');
		$I->fillField('#min-time', '');
		$I->fillField('#min-burst', '');
		$I->fillField('#min', '');
		$I->fillField('#max-time', '');
		$I->fillField('#max-burst', '');
		$I->fillField('#max', '');
		$I->fillField('#flows', '');
		$I->fillField('#quantum', '');
		$I->fillField('#qlimit', '');
		$I->uncheckOption('#default');
		$I->fillField('#comment', 'Test1');
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->fillField('#name', 'test');
		$I->fillField('#interface', 'em0');
		$I->fillField('#parent', 'rootq');
		$I->fillField('#bandwidth', '20M');
		$I->click('Apply');

		$I->fillField('#bw-burst', '90M');
		$I->fillField('#bw-time', '100ms');
		$I->fillField('#min', '5M');
		$I->click('Apply');

		$I->fillField('#min-burst', '10M');
		$I->fillField('#min-time', '50ms');
		$I->fillField('#max', '100M');
		$I->click('Apply');

		$I->fillField('#max-burst', '1M');
		$I->fillField('#max-time', '10ms');
		$I->fillField('#flows', '1024');
		$I->fillField('#quantum', '1');
		$I->fillField('#qlimit', '100');
		$I->checkOption('#default');
		$I->fillField('#comment', 'Test');
		$I->click('Apply');
	}
}
?>
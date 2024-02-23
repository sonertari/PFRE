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

class OptionFingerprintsCest extends Rule
{
	protected $type= 'Option';
	protected $ruleNumber= 21;
	protected $lineNumber= 29;
	protected $sender= 'option';

	protected $origRule= 'set fingerprints "/etc/pf.os" # Test';
	protected $expectedDispOrigRule= 'fingerprints: /etc/pf.os Test';

	protected $modifiedRule= 'set fingerprints "/etc/pf.os1" # Test1';
	protected $expectedDispModifiedRule= 'fingerprints: /etc/pf.os1 Test1';

	function __construct()
	{
		parent::__construct();

		$this->dLink= NULL;
	}

	protected function loadTestRules(AcceptanceTester $I)
	{
		parent::loadTestRules($I);

		$I->click('Rules');
		$I->wait(STALE_ELEMENT_INTERVAL);

		$I->click(['xpath' => '//a[contains(@href, "conf.editor.php?del=14")]']);
		$I->wait(POPUP_DISPLAY_INTERVAL);
		$I->seeInPopup('Are you sure you want to delete Option rule number 14?');
		$I->acceptPopup();
		$I->wait(POPUP_DISPLAY_INTERVAL);

		$I->selectOption('#category', 'Option');
		$I->click('Add');

		$I->selectOption('#type', 'fingerprints');
		$I->click('Apply');

		$I->fillField('#fingerprints', '/etc/pf.os');
		$I->fillField('#comment', 'Test');

		$I->checkOption('#forcesave');
		$I->click('Save');
	}

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->fillField('#fingerprints', '/etc/pf.os1');
		$this->clickApplySeeResult($I, 'set fingerprints "/etc/pf.os1" # Test');

		$I->fillField('#comment', 'Test1');
		$this->clickApplySeeResult($I, $this->modifiedRule);
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->fillField('#fingerprints', '/etc/pf.os');
		$this->clickApplySeeResult($I, 'set fingerprints "/etc/pf.os" # Test1');

		$I->fillField('#comment', 'Test');
		$this->clickApplySeeResult($I, $this->revertedRule);
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->fillField('#fingerprints', '/etc/pf.os1');
		$I->fillField('#comment', 'Test1');
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->fillField('#fingerprints', '/etc/pf.os');
		$I->fillField('#comment', 'Test');
		$I->click('Apply');
	}
}
?>
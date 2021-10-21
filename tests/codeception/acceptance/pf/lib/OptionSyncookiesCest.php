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

class OptionSyncookiesCest extends Rule
{
	protected $type= 'Option';
	protected $ruleNumber= 21;
	protected $lineNumber= 29;
	protected $sender= 'option';

	protected $origRule= 'set syncookies adaptive (start 25%, end 12%) # Test';
	protected $expectedDispOrigRule= 'syncookies: adaptive (start 25%, end 12%) Test';

	protected $modifiedRule= 'set syncookies always # Test1';
	protected $expectedDispModifiedRule= 'syncookies: always Test1';

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

		$I->selectOption('#type', 'syncookies');
		$I->click('Apply');

		$I->selectOption('#syncookies', 'adaptive');
		$I->fillField('#comment', 'Test');
		$this->clickApplySeeResult($I, 'set syncookies adaptive (start , end ) # Test');

		$I->fillField('#start', '25%');
		$I->fillField('#end', '12%');

		$I->checkOption('#forcesave');
		$I->click('Save');
	}

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->selectOption('#syncookies', 'always');
		$this->clickApplySeeResult($I, 'set syncookies always # Test');

		$I->fillField('#comment', 'Test1');
		$this->clickApplySeeResult($I, $this->modifiedRule);
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->selectOption('#syncookies', 'adaptive');
		$this->clickApplySeeResult($I, 'set syncookies adaptive (start , end ) # Test1');

		$I->fillField('#start', '25%');
		$I->fillField('#end', '12%');
		$this->clickApplySeeResult($I, 'set syncookies adaptive (start 25%, end 12%) # Test1');

		$I->fillField('#comment', 'Test');
		$this->clickApplySeeResult($I, $this->revertedRule);
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->selectOption('#syncookies', 'always');
		$I->fillField('#comment', 'Test1');
		$I->click('Apply');
		// Click Apply twice, so the start and end boxes are cleared
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->selectOption('#syncookies', 'adaptive');
		$I->click('Apply');
		$I->fillField('#start', '25%');
		$I->fillField('#end', '12%');
		$I->fillField('#comment', 'Test');
		$I->click('Apply');
	}
}
?>
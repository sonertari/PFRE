<?php 
/*
 * Copyright (C) 2004-2022 Soner Tari
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

class OptionOptimizationCest extends Rule
{
	protected $type= 'Option';
	protected $ruleNumber= 21;
	protected $lineNumber= 29;
	protected $sender= 'option';

	protected $origRule= 'set optimization normal # Test';
	protected $expectedDispOrigRule= 'optimization: normal Test';

	protected $modifiedRule= 'set optimization high-latency # Test1';
	protected $expectedDispModifiedRule= 'optimization: high-latency Test1';

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

		/// @todo Check why the following 3 methods do not work, a bug in xpath locator?
		//$I->click(\Codeception\Util\Locator::href('conf.editor.php?del=14'));
		//$aLink = $I->grabMultiple(['xpath' => '//a[contains(@href, "conf.editor.php?del=14")]'], 'href');
		//$I->click(\Codeception\Util\Locator::href($aLink[0]));
		//$I->click(['xpath' => '//a[@href="http://pfre/pf/conf.editor.php?del=14"]']);

		$I->click(['xpath' => '//a[contains(@href, "conf.editor.php?del=14")]']);
		$I->wait(POPUP_DISPLAY_INTERVAL);
		$I->seeInPopup('Are you sure you want to delete Option rule number 14?');
		$I->acceptPopup();
		$I->wait(POPUP_DISPLAY_INTERVAL);

		$I->selectOption('#category', 'Option');
		$I->click('Add');

		$I->selectOption('#type', 'optimization');
		$I->click('Apply');

		$I->selectOption('#optimization', 'normal');
		$I->fillField('#comment', 'Test');

		$I->checkOption('#forcesave');
		$I->click('Save');
	}

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->selectOption('#optimization', 'high-latency');
		$this->clickApplySeeResult($I, 'set optimization high-latency # Test');

		$I->fillField('#comment', 'Test1');
		$this->clickApplySeeResult($I, $this->modifiedRule);
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->selectOption('#optimization', 'normal');
		$this->clickApplySeeResult($I, 'set optimization normal # Test1');

		$I->fillField('#comment', 'Test');
		$this->clickApplySeeResult($I, $this->revertedRule);
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->selectOption('#optimization', 'high-latency');
		$I->fillField('#comment', 'Test1');
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->selectOption('#optimization', 'normal');
		$I->fillField('#comment', 'Test');
		$I->click('Apply');
	}
}
?>
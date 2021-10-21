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

class BlankCest extends Rule
{
	protected $type= 'Blank';
	protected $ruleNumber= 20;
	protected $lineNumber= 26;
	protected $sender= 'blank';

	protected $origRule= "\n";
	protected $expectedDispOrigRule= '';

	protected $modifiedRule= "\n\n\n";
	protected $expectedDispModifiedRule= '';

	function __construct()
	{
		parent::__construct();

		$this->expectedDispOrigRule= $this->ruleNumber . ' ' . $this->type . ' ' . $this->lineNumber . '
' . ($this->lineNumber + 1);
		$this->expectedDispModifiedRule= $this->ruleNumber . ' ' . $this->type . ' ' . $this->lineNumber . '
' . ($this->lineNumber + 1) . '
' . ($this->lineNumber + 2) . '
' . ($this->lineNumber + 3);
	}

	/**
	 * @depends testDisplay
	 */
	public function testEditSaveNotModifiedFail(AcceptanceTester $I)
	{
		$this->gotoEditPage($I);

		$I->see($this->editPageTitle, 'h2');
		$I->dontSee('(modified)', 'h2');
		$I->see('Number of lines: 2');

		$I->click('Save');

		$I->see($this->editPageTitle, 'h2');
		$I->see('Number of lines: 2');
	}

	/**
	 * @depends testEditSaveNotModifiedFail
	 */
	public function testEditSaveNotModifiedForcedFail(AcceptanceTester $I)
	{
		$I->see($this->editPageTitle, 'h2');
		$I->dontSee('(modified)', 'h2');
		$I->see('Number of lines: 2');

		$I->checkOption('#forcesave');
		$I->click('Save');

		$I->see($this->editPageTitle, 'h2');
		$I->see('Number of lines: 2');
	}

	/**
	 * @depends testEditSaveNotModifiedForcedFail
	 */
	public function testEditModifyRule(AcceptanceTester $I)
	{
		$I->expect('changes are applied incrementally');
		
		$I->see($this->editPageTitle, 'h2');
		$I->dontSee('(modified)', 'h2');
		$I->see('Number of lines: 2');

		if ($this->QUICK) {
			$this->modifyRuleQuick($I);
		} else {
			$this->modifyRule($I);
		}

		$I->see($this->editPageTitle . ' (modified)', 'h2');
		$I->see('Number of lines: 4');
	}

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->fillField('#blank', "\n\n");
		$I->click('Apply');

		$I->see($this->editPageTitle . ' (modified)', 'h2');
		$I->see('Number of lines: 3');

		$I->fillField('#blank', "\n\n\n");
		$I->click('Apply');

		$I->see($this->editPageTitle . ' (modified)', 'h2');
		$I->see('Number of lines: 4');
	}

	/**
	 * @depends testEditModifyRule
	 */
	public function testEditSaveModifiedWithErrorsFail(AcceptanceTester $I)
	{
		$I->click('Save');

		$I->see($this->editPageTitle . ' (modified)', 'h2');
		$I->see('Number of lines: 4');
	}

	/**
	 * @depends testEditSaveModifiedWithErrorsFail
	 */
	public function testEditSaveModifiedWithErrorsForced(AcceptanceTester $I)
	{
		$I->checkOption('#forcesave');
		$I->click('Save');

		$I->dontSee($this->editPageTitle, 'h2');
		$I->dontSee('Number of lines: 4');
	}

	/**
	 * @depends testDisplayModifiedWithErrorsForced
	 */
	public function testEditRevertModifications(AcceptanceTester $I)
	{
		$this->gotoEditPage($I);

		$I->expect('incrementally reverting modifications brings us back to the original rule');

		$I->see($this->editPageTitle, 'h2');
		$I->dontSee('(modified)', 'h2');
		$I->see('Number of lines: 4');

		if ($this->QUICK) {
			$this->revertModificationsQuick($I);
		} else {
			$this->revertModifications($I);
		}

		$I->see($this->editPageTitle . ' (modified)', 'h2');
		$I->see('Number of lines: 2');
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->fillField('#blank', "\n\n");
		$I->click('Apply');

		$I->see($this->editPageTitle . ' (modified)', 'h2');
		$I->see('Number of lines: 3');

		$I->fillField('#blank', "\n");
		$I->click('Apply');

		$I->see($this->editPageTitle . ' (modified)', 'h2');
		$I->see('Number of lines: 2');
	}

	/**
	 * @depends testEditRevertModifications
	 */
	public function testEditBackToModifiedRule(AcceptanceTester $I)
	{
		$I->expect('modifying again brings us back to the saved modified rule, (modified) should disappear');

		$this->modifyRuleQuick($I);

		$I->see($this->editPageTitle, 'h2');
		$I->dontSee('(modified)', 'h2');
		$I->see('Number of lines: 4');
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->fillField('#blank', "\n\n\n");
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->fillField('#blank', "\n");
		$I->click('Apply');
	}

	/**
	 * @depends testEditBackToModifiedRule
	 */
	public function testDisplayGeneratedModifiedWithErrors(AcceptanceTester $I)
	{
		$I->expect('modified rule with errors is generated on Display page correctly');

		$I->click('Display & Install');
		$I->wait(STALE_ELEMENT_INTERVAL);
		$I->seeInCurrentUrl('conf.write.php');
		$I->see('Display line numbers');

		$I->dontSee(' ' . $this->lineNumber . ': 
  ' . ($this->lineNumber + 1) . ': 
  ' . ($this->lineNumber + 2) . ': 
  ' . ($this->lineNumber + 3) . ': ', '#rules');

		$I->checkOption('#forcedisplay');

		$I->see(' ' . $this->lineNumber . ': 
  ' . ($this->lineNumber + 1) . ': 
  ' . ($this->lineNumber + 2) . ': 
  ' . ($this->lineNumber + 3) . ': ', '#rules');
	}
}
?>
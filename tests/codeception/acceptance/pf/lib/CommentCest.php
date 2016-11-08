<?php 
/*
 * Copyright (C) 2004-2016 Soner Tari
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

class CommentCest extends Rule
{
	protected $type= 'Comment';
	protected $ruleNumber= 21;
	protected $lineNumber= 28;
	protected $sender= 'comment';

	protected $origRule= 'Line1
Line2';
	protected $expectedDispOrigRule= '';

	protected $modifiedRule= 'Line1
Line2
Line3
Line4';
	protected $expectedDispModifiedRule= '';

	function __construct()
	{
		parent::__construct();

		$this->expectedDispOrigRule= $this->ruleNumber . ' ' . $this->type . ' ' . $this->lineNumber . '
' . ($this->lineNumber + 1) . ' Line1
Line2 e u d x';
		$this->expectedDispModifiedRule= $this->ruleNumber . ' ' . $this->type . ' ' . $this->lineNumber . '
' . ($this->lineNumber + 1) . '
' . ($this->lineNumber + 2) . '
' . ($this->lineNumber + 3) . ' Line1
Line2
Line3
Line4 e u d x';

		$this->dLink= NULL;
	}

	/**
	 * @depends testDisplay
	 */
	public function testEditSaveNotModifiedFail(AcceptanceTester $I)
	{
		$this->gotoEditPage($I);

		$I->see($this->editPageTitle, 'h2');
		$I->dontSee('(modified)', 'h2');

		$I->click('Save');

		$I->see($this->editPageTitle, 'h2');
	}

	/**
	 * @depends testEditSaveNotModifiedFail
	 */
	public function testEditSaveNotModifiedForcedFail(AcceptanceTester $I)
	{
		$I->see($this->editPageTitle, 'h2');
		$I->dontSee('(modified)', 'h2');

		$I->checkOption('#forcesave');
		$I->click('Save');

		$I->see($this->editPageTitle, 'h2');
	}

	/**
	 * @depends testEditSaveNotModifiedForcedFail
	 */
	public function testEditModifyRule(AcceptanceTester $I)
	{
		$I->expect('changes are applied incrementally');
		
		$I->see($this->editPageTitle, 'h2');
		$I->dontSee('(modified)', 'h2');

		if ($this->QUICK) {
			$this->modifyRuleQuick($I);
		} else {
			$this->modifyRule($I);
		}

		$I->see($this->editPageTitle . ' (modified)', 'h2');
	}

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->fillField('#comment', 'Line1
Line2
Line3');
		$I->click('Apply');

		$I->see($this->editPageTitle . ' (modified)', 'h2');

		$I->fillField('#comment', 'Line1
Line2
Line3
Line4');
		$I->click('Apply');

		$I->see($this->editPageTitle . ' (modified)', 'h2');
	}

	/**
	 * @depends testEditModifyRule
	 */
	public function testEditSaveModifiedWithErrorsFail(AcceptanceTester $I)
	{
		$I->click('Save');

		$I->see($this->editPageTitle . ' (modified)', 'h2');
	}

	/**
	 * @depends testEditSaveModifiedWithErrorsFail
	 */
	public function testEditSaveModifiedWithErrorsForced(AcceptanceTester $I)
	{
		$I->checkOption('#forcesave');
		$I->click('Save');

		$I->dontSee($this->editPageTitle, 'h2');
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

		if ($this->QUICK) {
			$this->revertModificationsQuick($I);
		} else {
			$this->revertModifications($I);
		}

		$I->see($this->editPageTitle . ' (modified)', 'h2');
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->fillField('#comment', 'Line1
Line2
Line3');
		$I->click('Apply');

		$I->see($this->editPageTitle . ' (modified)', 'h2');

		$I->fillField('#comment', 'Line1
Line2');
		$I->click('Apply');

		$I->see($this->editPageTitle . ' (modified)', 'h2');
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
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->fillField('#comment', 'Line1
Line2
Line3
Line4');
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->fillField('#comment', 'Line1
Line2');
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
		$I->seeInCurrentUrl('conf.php?submenu=displayinstall');
		$I->see('Display line numbers');

		$I->dontSee(' ' . $this->lineNumber . ': # Line1
  ' . ($this->lineNumber + 1) . ': # Line2
  ' . ($this->lineNumber + 2) . ': # Line3
  ' . ($this->lineNumber + 3) . ': # Line4', '#rules');

		$I->checkOption('#forcedisplay');

		$I->see(' ' . $this->lineNumber . ': # Line1
  ' . ($this->lineNumber + 1) . ': # Line2
  ' . ($this->lineNumber + 2) . ': # Line3
  ' . ($this->lineNumber + 3) . ': # Line4', '#rules');
	}
}
?>
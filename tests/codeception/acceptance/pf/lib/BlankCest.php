<?php 
/* $pfre: BlankCest.php,v 1.4 2016/08/16 18:07:47 soner Exp $ */

/*
 * Copyright (c) 2016 Soner Tari.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this
 *    software must display the following acknowledgement: This
 *    product includes software developed by Soner Tari
 *    and its contributors.
 * 4. Neither the name of Soner Tari nor the names of
 *    its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written
 *    permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
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
' . ($this->lineNumber + 1) . '

e u d x';
		$this->expectedDispModifiedRule= $this->ruleNumber . ' ' . $this->type . ' ' . $this->lineNumber . '
' . ($this->lineNumber + 1) . '
' . ($this->lineNumber + 2) . '
' . ($this->lineNumber + 3) . '



e u d x';

		$this->editPageTitle= 'Edit ' . $this->type . ' ' . $this->ruleNumber;
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
		$I->seeInCurrentUrl('conf.php?submenu=displayinstall');
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
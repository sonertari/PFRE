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

class Rule
{
	protected $type;
	protected $ruleNumber;
	protected $lineNumber;
	protected $sender;

	protected $editPageTitle;
	protected $trTitle;

	protected $origRule;
	protected $modifiedRule;
	protected $revertedRule;
	protected $generatedRule;

	protected $expectedDispOrigRule;
	protected $expectedDispModifiedRule;

	protected $eLink;
	protected $uLink;
	protected $dLink;
	protected $xLink;

	/// @todo Make this an option
	protected $QUICK= FALSE;

	/// @todo Reuse this table from rules.php in View
	private $ruleTypes= array(
		'filter' => 'Filter',
		'antispoof' => 'Antispoof',
		'anchor' => 'Anchor',
		'macro' => 'Macro',
		'table' => 'Table',
		'afto' => 'Af Translate',
		'natto' => 'Nat',
		'binatto' => 'Binat',
		'divertto' => 'Divert',
		'divertpacket' => 'Divert Packet',
		'rdrto' => 'Redirect',
		'route' => 'Route',
		'queue' => 'Queue',
		'scrub' => 'Scrub',
		'option' => 'Option',
		'timeout' => 'Timeout',
		'limit' => 'Limit',
		'state' => 'State Defaults',
		'loadanchor' => 'Load Anchor',
		'include' => 'Include',
		'blank' => 'Blank Line',
		'comment' => 'Comment',
		);

	function __construct()
	{
		$this->type= $this->ruleTypes[strtolower(ltrim($this->type, '_'))];

		$this->QUICK= QUICK;

		$this->revertedRule= $this->origRule;
		$this->generatedRule= $this->modifiedRule;

		$this->editPageTitle= 'Edit ' . $this->type . ' Rule ' . $this->ruleNumber;

		$this->trTitle= $this->type . ' rule';

		$this->expectedDispOrigRule= $this->ruleNumber . ' ' . $this->type . ' ' . $this->lineNumber . ' ' . $this->expectedDispOrigRule;
		$this->expectedDispModifiedRule= $this->ruleNumber . ' ' . $this->type . ' ' . $this->lineNumber . ' ' . $this->expectedDispModifiedRule;

		$this->eLink= 'http://pfre/pf/conf.php?sender=' . $this->sender . '&rulenumber=' . $this->ruleNumber;
		$this->uLink= 'http://pfre/pf/conf.php?up=' . $this->ruleNumber;
		$this->dLink= 'http://pfre/pf/conf.php?down=' . $this->ruleNumber;
		$this->xLink= 'http://pfre/pf/conf.php?del=' . $this->ruleNumber;
	}

	public function _before(AcceptanceTester $I, Helper\ConfigureWebDriver $config)
	{
		/// @attention Disable clear_cookies before each test
		// Because Codeception enables clear_cookies after each test function
		$config->setClearCookies(FALSE);

		// This is to resume failed tests, otherwise this should never happen between tests
		$this->authenticate($I);
	}

	protected function login(AcceptanceTester $I)
	{
		//$I->maximizeWindow();

		$I->amOnPage('/');

		$this->authenticate($I);
	}

	protected function authenticate(AcceptanceTester $I)
	{
		if (preg_match('|/login\.php|', $I->grabFromCurrentUrl())) {
			$I->see('PF Rule Editor');
			$I->see('User');
			$I->see('Password');

			$I->fillField('UserName', 'admin');
			$I->fillField('Password', 'soner123');
			$I->click('Login');

			$I->seeInCurrentUrl('pf/conf.php');

			$I->selectOption('#Locale', 'English');
			$I->seeOptionIsSelected('#Locale', 'English');
		}
	}

	protected function loadTestRules(AcceptanceTester $I)
	{
		$I->click('Load & Save');
		$I->wait(STALE_ELEMENT_INTERVAL);
		$I->seeInCurrentUrl('conf.php?submenu=loadsave');
		$I->see('Load ruleset');

		$I->attachFile(\Codeception\Util\Locator::find('input', ['type' => 'file']), 'test.conf');

		$I->click('Upload');
	}

	/**
	 * @before login
	 * @before loadTestRules
	 */
	public function testDisplay(AcceptanceTester $I, Codeception\Test\Unit $tester)
	{
		$I->click('Rules');
		$I->wait(STALE_ELEMENT_INTERVAL);
		$I->seeInCurrentUrl('conf.php?submenu=rules');

		$I->seeOptionIsSelected('category', 'All');

		$I->expect('rule is displayed correctly');

		$actualDisp = $I->grabTextFrom(\Codeception\Util\Locator::find('tr', ['title' => $this->trTitle]));
		$tester->assertEquals($this->expectedDispOrigRule, $actualDisp);
		
		$I->seeLink('e', $this->eLink);
		$I->seeLink('u', $this->uLink);
		$I->seeLink('d', $this->dLink);
		$I->seeLink('x', $this->xLink);
	}

	/**
	 * @depends testDisplay
	 */
	public function testEditSaveNotModifiedFail(AcceptanceTester $I)
	{
		$this->gotoEditPage($I);

		$I->see($this->editPageTitle, 'h2');
		$I->dontSee('(modified)', 'h2');
		$I->see($this->origRule, 'h4');

		$I->click('Save');

		$I->see($this->editPageTitle, 'h2');
		$I->see($this->origRule, 'h4');
	}

	protected function gotoEditPage(AcceptanceTester $I)
	{
		/// @todo Wait here, otherwise selenium gives: "stale element reference: element is not attached to the page document"
		// http://seleniumhq.org/exceptions/stale_element_reference.html
		// The issue at this point seems to be caused by clicking Rules tab while on the rules page, effectively refreshing the page unnecessarily
		//$I->click('Rules');
		$I->wait(STALE_ELEMENT_INTERVAL);
		// @attention Do not check the URL, it changes depending on where you have come from
		//$I->seeInCurrentUrl('conf.php?submenu=rules');

		// These methods work too
		//$I->click(['xpath' => '//a[contains(@href, "rulenumber=' . $this->ruleNumber . '")]']);
		//$I->click('//a[contains(@href, "rulenumber=' . $this->ruleNumber . '")]');

		$I->seeLink('e', 'http://pfre/pf/conf.php?sender=' . $this->sender . '&rulenumber=' . $this->ruleNumber);
		$I->click(\Codeception\Util\Locator::href('conf.php?sender=' . $this->sender . '&rulenumber=' . $this->ruleNumber));

		$I->wait(STALE_ELEMENT_INTERVAL);
		$I->seeInCurrentUrl('conf.php?sender=' . $this->sender . '&rulenumber=' . $this->ruleNumber);
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
		$I->see($this->origRule, 'h4');
	}

	/**
	 * @depends testEditSaveNotModifiedForcedFail
	 */
	public function testEditModifyRule(AcceptanceTester $I)
	{
		$I->expect('changes are applied incrementally');
		
		$I->see($this->editPageTitle, 'h2');
		$I->dontSee('(modified)', 'h2');
		$I->see($this->origRule, 'h4');

		if ($this->QUICK) {
			$this->modifyRuleQuick($I);
		} else {
			$this->modifyRule($I);
		}

		$I->see($this->editPageTitle . ' (modified)', 'h2');
		$I->see($this->modifiedRule, 'h4');
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
	}

	protected function modifyRule(AcceptanceTester $I)
	{
	}

	/**
	 * @depends testEditModifyRule
	 */
	public function testEditSaveModifiedWithErrorsFail(AcceptanceTester $I)
	{
		$I->click('Save');

		$I->see($this->editPageTitle . ' (modified)', 'h2');
		$I->see($this->modifiedRule, 'h4');
	}

	/**
	 * @depends testEditSaveModifiedWithErrorsFail
	 */
	public function testEditSaveModifiedWithErrorsForced(AcceptanceTester $I)
	{
		$I->checkOption('#forcesave');
		$I->click('Save');

		$I->dontSee($this->editPageTitle, 'h2');
		$I->dontSeeElement('h4');
	}

	/**
	 * @depends testEditSaveModifiedWithErrorsForced
	 */
	public function testDisplayModifiedWithErrorsForced(AcceptanceTester $I, Codeception\Test\Unit $tester)
	{
		$I->expect('modified rule with errors is displayed correctly');

		$display = $I->grabTextFrom(\Codeception\Util\Locator::find('tr', ['title' => $this->trTitle]));
		$tester->assertEquals($this->expectedDispModifiedRule, $display);
		
		$I->seeLink('e', $this->eLink);
		$I->seeLink('u', $this->uLink);
		$I->seeLink('d', $this->dLink);
		$I->seeLink('x', $this->xLink);
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
		$I->see($this->modifiedRule, 'h4');

		if ($this->QUICK) {
			$this->revertModificationsQuick($I);
		} else {
			$this->revertModifications($I);
		}

		$I->see($this->editPageTitle . ' (modified)', 'h2');
		$I->see($this->revertedRule, 'h4');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
	}

	protected function revertModifications(AcceptanceTester $I)
	{
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
		$I->see($this->modifiedRule, 'h4');
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

		$I->dontSee(' ' . $this->lineNumber . ': ' . $this->generatedRule, '#rules');

		$I->checkOption('#forcedisplay');

		$I->see(' ' . $this->lineNumber . ': ' . $this->generatedRule, '#rules');
	}

	/// @attention Make logout a test too, so that we always logout in the end
	public function logout(AcceptanceTester $I)
	{
		$I->seeLink('Logout');
		$I->click('Logout');

		$I->seeInCurrentUrl('login.php');
	}

	protected function clickDeleteLink(AcceptanceTester $I, $delId, $value)
	{
		$I->click(\Codeception\Util\Locator::href('conf.php?sender=' . $this->sender . '&rulenumber=' . $this->ruleNumber . '&' . $delId . '=' . $value . '&state=edit'));
	}

	protected function clickApplySeeResult(AcceptanceTester $I, $expectedRule)
	{
		$I->click('Apply');
		$this->seeResult($I, $expectedRule);
	}

	protected function seeResult(AcceptanceTester $I, $expectedRule)
	{
		$I->see($this->editPageTitle . ' (modified)', 'h2');
		$I->see($expectedRule, 'h4');
	}
}
?>

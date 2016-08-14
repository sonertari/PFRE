<?php 
/* $pfre: RuleSetTest.php,v 1.1 2016/08/12 18:28:26 soner Exp $ */

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

class TableCest
{
	public function _before(Helper\ConfigureWebDriver $config)
	{
		/// @attention Disable clear_cookies before each test
		// Because Codeception enables clear_cookies after each test function
		$config->setClearCookies(FALSE);
	}

	protected function login(AcceptanceTester $I)
	{
		$I->wantTo('test rules page');
		$I->amOnPage('/');

		$I->see('PF Rule Editor');
		$I->see('User');
		$I->see('Password');

		$I->fillField('UserName', 'admin');
		$I->fillField('Password', 'soner123');
		$I->click('Login');

		$I->seeInCurrentUrl('pf/conf.php');
	}

	protected function loadTestRules(AcceptanceTester $I)
	{
		$I->click('Load & Save');
		$I->see('Load rulebase');

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

		$I->seeOptionIsSelected('category', 'All');

		$I->expect('table rule is displayed correctly');

		$display = $I->grabTextFrom(\Codeception\Util\Locator::find('tr', ['title' => 'Table rule']));
		$tester->assertEquals($display,
			'4 Table test const persist counters 192.168.0.1
192.168.0.2
file "/etc/pf.restrictedips1"
file "/etc/pf.restrictedips2"
Test e u d x');
		
		$I->seeLink('e', 'http://pfre/pf/conf.php?sender=table&rulenumber=4');
		$I->seeLink('u', 'http://pfre/pf/conf.php?up=4');
		$I->seeLink('d', 'http://pfre/pf/conf.php?down=4');
		$I->seeLink('x', 'http://pfre/pf/conf.php?del=4');
	}

	/**
	 * @depends testDisplay
	 */
	public function testEditExistingRule(AcceptanceTester $I)
	{
		// These 3 methods all work
		//$I->click(['xpath' => '//a[contains(@href, "rulenumber=14")]']);
		//$I->click('//a[contains(@href, "rulenumber=14")]');
		$I->click(\Codeception\Util\Locator::href('conf.php?sender=table&rulenumber=4'));

		$I->maximizeWindow();

		$origRule= 'table <test> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test';

		$I->expect('changes are applied incrementally');

		$I->dontSee('Edit Table Rule 4 (modified)');
		$I->see($origRule, 'h4');

		$this->modifyRule($I);

		$I->expect('then back to the original');

		$I->fillField('identifier', 'test');
		$I->click('Apply');
		$I->see('Edit Table Rule 4 (modified)');
		$I->see('table <test> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1', 'h4');

		$I->checkOption('#const');
		$I->click('Apply');
		$I->see('Edit Table Rule 4 (modified)');
		$I->see('table <test> const file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1', 'h4');

		$I->checkOption('#persist');
		$I->click('Apply');
		$I->see('Edit Table Rule 4 (modified)');
		$I->see('table <test> persist const file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1', 'h4');

		$I->checkOption('#counters');
		$I->click('Apply');
		$I->see('Edit Table Rule 4 (modified)');
		$I->see('table <test> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1', 'h4');
		
		$I->click(\Codeception\Util\Locator::href('conf.php?sender=table&rulenumber=4&delValue=1.1.1.1&state=edit'));
		$I->see('Edit Table Rule 4 (modified)');
		$I->see('table <test> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2 } # Test1', 'h4');

		$I->click(\Codeception\Util\Locator::href('conf.php?sender=table&rulenumber=4&delFile=/etc/pf.restrictedips3&state=edit'));
		$I->see('Edit Table Rule 4 (modified)');
		$I->see('table <test> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test1', 'h4');

		$I->fillField('comment', 'Test');
		$I->click('Apply');
		$I->dontSee('Edit Table Rule 4 (modified)');
		$I->see($origRule);
	}

	protected function modifyRule($I)
	{
		$I->fillField('identifier', 'test1');
		$I->click('Apply');
		$I->see('Edit Table Rule 4 (modified)');
		$I->see('table <test1> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test', 'h4');

		$I->uncheckOption('#const');
		$I->click('Apply');
		$I->see('Edit Table Rule 4 (modified)');
		$I->see('table <test1> persist counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test', 'h4');

		$I->uncheckOption('#persist');
		$I->click('Apply');
		$I->see('Edit Table Rule 4 (modified)');
		$I->see('table <test1> counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test', 'h4');

		$I->uncheckOption('#counters');
		$I->click('Apply');
		$I->see('Edit Table Rule 4 (modified)');
		$I->see('table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test', 'h4');
		
		$I->fillField('addValue', '1.1.1.1');
		$I->click('Apply');
		$I->see('Edit Table Rule 4 (modified)');
		$I->see('table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test', 'h4');

		$I->fillField('addFile', '/etc/pf.restrictedips3');
		$I->click('Apply');
		$I->see('Edit Table Rule 4 (modified)');
		$I->see('table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test', 'h4');

		$I->fillField('comment', 'Test1');
		$I->click('Apply');
		$I->see('Edit Table Rule 4 (modified)');
		$I->see('table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1', 'h4');
	}

	/**
	 * @depends testEditExistingRule
	 */
	public function testEditSaveNotModifiedFail(AcceptanceTester $I)
	{
		$I->dontSee('Edit Table Rule 4 (modified)');

		$I->click('Save');

		$I->see('Edit Table Rule 4');
		$I->see('table <test> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test', 'h4');
	}

	/**
	 * @depends testEditSaveNotModifiedFail
	 */
	public function testEditSaveNotModifiedForcedFail(AcceptanceTester $I)
	{
		$I->dontSee('Edit Table Rule 4 (modified)');

		$I->checkOption('#forcesave');
		$I->click('Save');

		$I->see('Edit Table Rule 4');
		$I->see('table <test> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test', 'h4');
	}

	/**
	 * @depends testEditSaveNotModifiedForcedFail
	 */
	public function testEditSaveModifiedWithErrorsFail(AcceptanceTester $I)
	{
		$I->dontSee('Edit Table Rule 4 (modified)');

		$this->modifyRule($I);

		$I->click('Save');

		$I->see('Edit Table Rule 4 (modified)');
		$I->see('table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1', 'h4');
	}

	/**
	 * @depends testEditSaveModifiedWithErrorsFail
	 */
	public function testEditSaveModifiedWithErrorsForced(AcceptanceTester $I)
	{
		$I->checkOption('#forcesave');
		$I->click('Save');

		$I->dontSee('Edit Table Rule 4');
		$I->dontSeeElement('h4');
	}

	/**
	 * @depends testEditSaveModifiedWithErrorsForced
	 */
	public function testDisplayModifiedWithErrorsForced(AcceptanceTester $I, Codeception\Test\Unit $tester)
	{
		$I->expect('modified table rule with errors is displayed correctly');

		$I->see('table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1', 'pre');

		$display = $I->grabTextFrom(\Codeception\Util\Locator::find('tr', ['title' => 'Table rule']));
		$tester->assertEquals($display,
			'4 Table test1 192.168.0.1
192.168.0.2
1.1.1.1
file "/etc/pf.restrictedips1"
file "/etc/pf.restrictedips2"
file "/etc/pf.restrictedips3"
Test1 e u d x');
		
		$I->seeLink('e', 'http://pfre/pf/conf.php?sender=table&rulenumber=4');
		$I->seeLink('u', 'http://pfre/pf/conf.php?up=4');
		$I->seeLink('d', 'http://pfre/pf/conf.php?down=4');
		$I->seeLink('x', 'http://pfre/pf/conf.php?del=4');
	}

	/**
	 * @depends testDisplayModifiedWithErrorsForced
	 * @after logout
	 */
	public function testDisplayGeneratedModifiedWithErrors(AcceptanceTester $I)
	{
		$I->expect('modified table rule with errors is generated on Display page correctly');

		$I->click('Display & Install');
		$I->see('Display line numbers');

		/// @todo Check why the following does not work
		//$I->seeInPageSource(htmlentities('table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1'));
		$I->see('table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1', 'pre');
		$I->dontSee(' 10: table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1', '#rules');

		$I->checkOption('#forcedisplay');

		$I->see(' 10: table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1', '#rules');
	}

	protected function logout(AcceptanceTester $I)
	{
		$I->seeLink('Logout');
		$I->click('Logout');

		$I->seeInCurrentUrl('login.php');
	}
}
?>
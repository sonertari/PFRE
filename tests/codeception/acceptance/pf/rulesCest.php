<?php 
/* $pfre: rulesCest.php,v 1.3 2016/08/16 07:32:12 soner Exp $ */

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

class rulesCest
{
	/// @todo Reuse these tables from rules.php in View
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

	private $ruleType2Class= array(
		'filter' => 'Filter',
		'antispoof' => 'Antispoof',
		'anchor' => 'Anchor',
		'macro' => 'Macro',
		'table' => 'Table',
		'afto' => 'AfTo',
		'natto' => 'NatTo',
		'binatto' => 'BinatTo',
		'divertto' => 'DivertTo',
		'divertpacket' => 'DivertPacket',
		'rdrto' => 'RdrTo',
		'route' => 'Route',
		'queue' => 'Queue',
		'scrub' => 'Scrub',
		'option' => 'Option',
		'timeout' => 'Timeout',
		'limit' => 'Limit',
		'state' => 'State',
		'loadanchor' => 'LoadAnchor',
		'include' => '_Include',
		'blank' => 'Blank',
		'comment' => 'Comment',
	);

	protected $tabSwitchInterval= 1;

	public function _before(Helper\ConfigureWebDriver $config)
	{
		/// @attention Disable clear_cookies before each test
		// Because Codeception enables clear_cookies after each test function
		$config->setClearCookies(FALSE);
	}

	protected function login(AcceptanceTester $I)
	{
		$I->maximizeWindow();

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
		$I->wait($this->tabSwitchInterval);
		$I->seeInCurrentUrl('conf.php?submenu=loadsave');
		$I->see('Load rulebase');

		$I->attachFile(\Codeception\Util\Locator::find('input', ['type' => 'file']), 'test.conf');

		$I->click('Upload');

		$I->click('Rules');
	}

	/**
	 * @before login
	 * @before loadTestRules
	 */
	public function testShow(AcceptanceTester $I)
	{		
		$I->seeOptionIsSelected('#category', 'All');

		$ruleNumber= 0;
		foreach ($this->ruleTypes as $sender => $type) {
			$I->expect("the list has $type rules, and $type rules only");
			$I->selectOption('category', $type);
			$I->click('Show');

			$I->seeNumberOfElements(['xpath' => '//a[contains(@href, "conf.php?del=")]'], 1);
			$I->seeNumberOfElements(\Codeception\Util\Locator::find('tr', ['title' => ltrim($this->ruleType2Class[$sender], '_') . ' rule']), 1);

			$I->seeLink('e', "http://pfre/pf/conf.php?sender=$sender&rulenumber=$ruleNumber");

			$ruleNumber++;
		}

		$I->selectOption('#category', 'All');
		$I->click('Show');
	}

	/**
	 * @before loadTestRules
	 */
	public function testAddLast(AcceptanceTester $I)
	{
		$ruleNumber= count($this->ruleTypes);
		foreach ($this->ruleTypes as $sender => $type) {
			$I->expect("clicking the Add button creates a $type rule as rule number $ruleNumber, and takes us to the edit page for that $type rule");

			$I->seeInField('#ruleNumber', $ruleNumber);

			$I->selectOption('#category', $type);
			$I->click('Add');

			$I->see('Edit ' . ltrim($this->ruleType2Class[$sender], '_'), 'h2');
			$I->see($ruleNumber . ' (modified)', 'h2');

			$I->checkOption('#forcesave');
			$I->click('Save');

			$I->seeLink('e', "http://pfre/pf/conf.php?sender=$sender&rulenumber=$ruleNumber");

			$ruleNumber++;
		}
	}

	/**
	 * @before loadTestRules
	 */
	public function testAddFirst(AcceptanceTester $I)
	{
		$count= count($this->ruleTypes);
	
		$ruleNumber= 0;
		foreach ($this->ruleTypes as $sender => $type) {
			$I->expect("clicking the Add button creates a $type rule as rule number $ruleNumber, and takes us to the edit page for that $type rule");
			
			$I->seeInField('#ruleNumber', $count++);

			$I->selectOption('#category', $type);
			$I->fillField('#ruleNumber', $ruleNumber);
			$I->click('Add');

			$I->see('Edit ' . ltrim($this->ruleType2Class[$sender], '_'), 'h2');
			$I->see($ruleNumber . ' (modified)', 'h2');

			$I->checkOption('#forcesave');
			$I->click('Save');

			$I->seeLink('e', "http://pfre/pf/conf.php?sender=$sender&rulenumber=$ruleNumber");

			/// @attention No need to delete the new rule
			//$I->click(['xpath' => '//a[contains(@href, "conf.php?del=' . $ruleNumber . '")]']);
			//$I->wait(1);
			//$I->acceptPopup();
		}
	}

	/**
	 * @before loadTestRules
	 */
	public function testAddMiddle(AcceptanceTester $I)
	{
		$count= count($this->ruleTypes);
	
		$ruleNumber= round(count($this->ruleTypes) / 2);
		foreach ($this->ruleTypes as $sender => $type) {
			$I->expect("clicking the Add button creates a $type rule as rule number $ruleNumber, and takes us to the edit page for that $type rule");

			$I->seeInField('#ruleNumber', $count++);

			$I->selectOption('#category', $type);
			$I->fillField('#ruleNumber', $ruleNumber);
			$I->click('Add');

			$I->see('Edit ' . ltrim($this->ruleType2Class[$sender], '_'), 'h2');
			$I->see($ruleNumber . ' (modified)', 'h2');

			$I->checkOption('#forcesave');
			$I->click('Save');

			$I->seeLink('e', "http://pfre/pf/conf.php?sender=$sender&rulenumber=$ruleNumber");
		}
	}

	/**
	 * @before loadTestRules
	 */
	public function testAddGreaterThanRuleCount(AcceptanceTester $I)
	{
		$count= count($this->ruleTypes);
	
		$I->expect("clicking the Add button creates a Filter rule as rule number $count, and takes us to the edit page for that Filter rule");

		$I->seeInField('#ruleNumber', $count);

		$I->selectOption('#category', 'Filter');
		$I->fillField('#ruleNumber', $count * 2);
		$I->click('Add');

		$I->see("Edit Filter Rule $count (modified)", 'h2');

		$I->checkOption('#forcesave');
		$I->click('Save');

		$I->seeLink('e', "http://pfre/pf/conf.php?sender=filter&rulenumber=$count");
	}

	/**
	 * @before loadTestRules
	 */
	public function testEdit(AcceptanceTester $I)
	{
		$count= count($this->ruleTypes);

		$ruleNumber= 0;
		foreach ($this->ruleTypes as $sender => $type) {
			$I->expect("clicking the Edit button takes us to the edit page for $type rule $ruleNumber");

			$I->seeInField('#ruleNumber', $count);

			$I->fillField('#ruleNumber', $ruleNumber);
			$I->click('Edit');

			$I->see('Edit ' . ltrim($this->ruleType2Class[$sender], '_'), 'h2');
			$I->see($ruleNumber, 'h2');
			$I->dontSee('(modified)', 'h2');

			$I->click('Cancel');
			$ruleNumber++;
		}
	}

	/**
	 * @before loadTestRules
	 */
	public function testEditGreaterThanRuleCount(AcceptanceTester $I)
	{
		$count= count($this->ruleTypes);
	
		$I->expect("clicking the Edit button creates a Filter rule as rule number $count, and takes us to the edit page for that Filter rule");

		$I->seeInField('#ruleNumber', $count);

		$I->selectOption('#category', 'Filter');
		$I->fillField('#ruleNumber', $count * 2);
		$I->click('Edit');

		$I->see("Edit Filter Rule $count (modified)", 'h2');

		$I->checkOption('#forcesave');
		$I->click('Save');

		$I->seeLink('e', "http://pfre/pf/conf.php?sender=filter&rulenumber=$count");
	}

	/**
	 * For popups:
	 * 
	 * @attention Use Selenium 3.0.0-beta4, works fine even with ChromeDriver 2.21
	 * $ java -jar selenium-server-standalone-3.0.0-beta4.jar
	 * 
	 * @attention With Selenium 2.53.1 use ChromeDriver >=2.24, otherwise the driver cannot even see popups
	 * $ java -jar selenium-server-standalone-2.53.1.jar -Dwebdriver.chrome.driver=chromedriver
	 * 
	 * @attention Selenium 3.0.0-beta4 command line is different from that of 2.53.1 above, if an option to be provided:
	 * $ java -Dwebdriver.chrome.driver=chromedriver -jar selenium-server-standalone-3.0.0-beta4.jar
	 * 
	 * @before loadTestRules
	 */
	public function testDelete(AcceptanceTester $I)
	{
		$count= count($this->ruleTypes);
	
		$ruleNumber= 0;
		foreach ($this->ruleTypes as $sender => $type) {
			$I->expect("clicking the Delete button deletes a $type rule $ruleNumber");

			$I->seeInField('#ruleNumber', $count);
			$I->seeLink('e', "http://pfre/pf/conf.php?sender=$sender&rulenumber=$ruleNumber");

			$I->fillField('#ruleNumber', $ruleNumber);
			$I->click('Delete');

			$I->wait(1);
			$I->seeInPopup('Are you sure you want to delete the rule?');
			$I->acceptPopup();

			$I->dontSeeLink('e', "http://pfre/pf/conf.php?sender=$sender&rulenumber=$ruleNumber");

			$I->selectOption('#category', $type);
			$I->fillField('#ruleNumber', $ruleNumber);
			$I->click('Add');

			$I->see('Edit ' . ltrim($this->ruleType2Class[$sender], '_'), 'h2');
			$I->see($ruleNumber . ' (modified)', 'h2');

			$I->checkOption('#forcesave');
			$I->click('Save');

			$I->seeLink('e', "http://pfre/pf/conf.php?sender=$sender&rulenumber=$ruleNumber");
			$ruleNumber++;
		}
	}

	/**
	 * @before loadTestRules
	 */
	public function testMove(AcceptanceTester $I)
	{
		$count= count($this->ruleTypes);
	
		$ruleNumber= 0;
		$delta= 1;
		$moveTo= 0;
		while ($ruleNumber < $count) {
			$I->seeLink('e', "http://pfre/pf/conf.php?sender=filter&rulenumber=$ruleNumber");

			$moveTo= $ruleNumber + $delta;

			$I->fillField('#ruleNumber', $ruleNumber);
			$I->fillField('#moveTo', $moveTo);
			$I->click('Move');
			
			if ($moveTo < $count) {
				$I->dontSeeLink('e', "http://pfre/pf/conf.php?sender=filter&rulenumber=$ruleNumber");
				$I->seeLink('e', "http://pfre/pf/conf.php?sender=filter&rulenumber=$moveTo");
			} else {
				$I->seeLink('e', "http://pfre/pf/conf.php?sender=filter&rulenumber=$ruleNumber");
				$I->dontSeeLink('e', "http://pfre/pf/conf.php?sender=filter&rulenumber=$moveTo");
			}

			$ruleNumber= $moveTo;
			$delta*= 2;
		}

		$this->loadTestRules($I);

		$ruleNumber= $count - 1;
		$delta= 1;
		while ($ruleNumber >= 0) {
			$I->seeLink('e', "http://pfre/pf/conf.php?sender=comment&rulenumber=$ruleNumber");

			$moveTo= $ruleNumber - $delta;

			$I->fillField('#ruleNumber', $ruleNumber);
			$I->fillField('#moveTo', $moveTo);
			$I->click('Move');
			
			if ($moveTo >= 0) {
				$I->dontSeeLink('e', "http://pfre/pf/conf.php?sender=comment&rulenumber=$ruleNumber");
				$I->seeLink('e', "http://pfre/pf/conf.php?sender=comment&rulenumber=$moveTo");
			} else {
				$I->seeLink('e', "http://pfre/pf/conf.php?sender=comment&rulenumber=$ruleNumber");
				$I->dontSeeLink('e', "http://pfre/pf/conf.php?sender=comment&rulenumber=$moveTo");
			}

			$ruleNumber= $moveTo;
			$delta*= 2;
		}
	}

	/**
	 * @before loadTestRules
	 */
	public function testDeleteAll(AcceptanceTester $I)
	{		
		$count= count($this->ruleTypes);

		$I->seeNumberOfElements(['xpath' => '//a[contains(@href, "conf.php?del=")]'], $count);

		$I->click('Delete All');

		$I->wait(1);
		$I->seeInPopup('Are you sure you want to delete the entire rulebase?');
		$I->acceptPopup();

		$I->seeNumberOfElements(['xpath' => '//a[contains(@href, "conf.php?del=")]'], 0);
	}

	/**
	 * @before loadTestRules
	 */
	public function testDown(AcceptanceTester $I)
	{
		$count= count($this->ruleTypes);
	
		$ruleNumber= 0;
		while ($ruleNumber < $count - 1) {
			$I->seeLink('e', "http://pfre/pf/conf.php?sender=filter&rulenumber=$ruleNumber");

			$I->click(['xpath' => '//a[contains(@href, "conf.php?down=' . $ruleNumber . '")]']);
			//$I->wait(1);
			
			$I->dontSeeLink('e', "http://pfre/pf/conf.php?sender=filter&rulenumber=$ruleNumber");

			$ruleNumber++;
			$I->seeLink('e', "http://pfre/pf/conf.php?sender=filter&rulenumber=$ruleNumber");
		}
	}

	/**
	 * @before loadTestRules
	 */
	public function testUp(AcceptanceTester $I)
	{
		$count= count($this->ruleTypes);
	
		$ruleNumber= $count - 1;
		while ($ruleNumber > 0) {
			$I->seeLink('e', "http://pfre/pf/conf.php?sender=comment&rulenumber=$ruleNumber");

			$I->click(['xpath' => '//a[contains(@href, "conf.php?up=' . $ruleNumber . '")]']);
			//$I->wait(1);
			
			$I->dontSeeLink('e', "http://pfre/pf/conf.php?sender=comment&rulenumber=$ruleNumber");

			$ruleNumber--;
			$I->seeLink('e', "http://pfre/pf/conf.php?sender=comment&rulenumber=$ruleNumber");
		}
	}

	/// @attention Make logout a test too, so that we always logout in the end
	public function logout(AcceptanceTester $I)
	{
		$I->seeLink('Logout');
		$I->click('Logout');

		$I->seeInCurrentUrl('login.php');
	}
}
?>
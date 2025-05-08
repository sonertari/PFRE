<?php 
/*
 * Copyright (C) 2004-2025 Soner Tari
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

class confeditorCest
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

	public function _before(Helper\ConfigureWebDriver $config)
	{
		/// @attention Disable clear_cookies before each test
		// Because Codeception enables clear_cookies after each test function
		$config->setClearCookies(FALSE);
	}

	protected function login(AcceptanceTester $I)
	{
		//$I->maximizeWindow();

		$I->amOnPage('/');

		$I->see('PF Rule Editor');
		$I->see('User');
		$I->see('Password');

		$I->fillField('UserName', 'admin');
		$I->fillField('Password', 'soner123');
		$I->click('Login');

		$I->seeInCurrentUrl('pf/conf.editor.php');

		// @attention Don't use moveMouseOver(), dropdown menu appears too late sometimes and the test fails
		//$I->moveMouseOver('#rightmenu');
		$I->click('#rightmenu');
		$I->wait(POPUP_DISPLAY_INTERVAL);
		$I->see('Language');
		$I->click('#languagemenu');
		$I->wait(POPUP_DISPLAY_INTERVAL);
		$I->see('English');
		$I->click('English');
	}

	protected function loadTestRules(AcceptanceTester $I)
	{
		$I->click('Load & Save');
		$I->wait(STALE_ELEMENT_INTERVAL);
		$I->seeInCurrentUrl('conf.files.php');
		$I->see('Load ruleset');

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

			$I->seeNumberOfElements(['xpath' => '//a[contains(@href, "conf.editor.php?del=")]'], 1);
			$I->seeNumberOfElements(\Codeception\Util\Locator::find('tr', ['title' => "$type rule"]), 1);

			$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=$sender&rulenumber=$ruleNumber");

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

			$I->see("Edit $type", 'h2');
			$I->see($ruleNumber . ' (modified)', 'h2');

			$I->checkOption('#forcesave');
			$I->click('Save');

			$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=$sender&rulenumber=$ruleNumber");

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

			$I->see("Edit $type", 'h2');
			$I->see($ruleNumber . ' (modified)', 'h2');

			$I->checkOption('#forcesave');
			$I->click('Save');

			$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=$sender&rulenumber=$ruleNumber");

			/// @attention No need to delete the new rule
			//$I->click(['xpath' => '//a[contains(@href, "conf.editor.php?del=' . $ruleNumber . '")]']);
			//$I->wait(POPUP_DISPLAY_INTERVAL);
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

			$I->see("Edit $type", 'h2');
			$I->see($ruleNumber . ' (modified)', 'h2');

			$I->checkOption('#forcesave');
			$I->click('Save');

			$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=$sender&rulenumber=$ruleNumber");
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

		$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=filter&rulenumber=$count");
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

			$I->see("Edit $type", 'h2');
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

		$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=filter&rulenumber=$count");
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
			$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=$sender&rulenumber=$ruleNumber");

			$I->fillField('#ruleNumber', $ruleNumber);
			$I->click('Delete');

			$I->wait(POPUP_DISPLAY_INTERVAL);
			$I->seeInPopup('Are you sure you want to delete the rule?');
			$I->acceptPopup();
			$I->wait(POPUP_DISPLAY_INTERVAL);

			$I->dontSeeLink('e', "http://pfre/pf/conf.editor.php?sender=$sender&rulenumber=$ruleNumber");

			$I->selectOption('#category', $type);
			$I->fillField('#ruleNumber', $ruleNumber);
			$I->click('Add');

			$I->see("Edit $type", 'h2');
			$I->see($ruleNumber . ' (modified)', 'h2');

			$I->checkOption('#forcesave');
			$I->click('Save');

			$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=$sender&rulenumber=$ruleNumber");
			$ruleNumber++;
		}
	}

	/**
	 * @before loadTestRules
	 */
	public function testMoveDown(AcceptanceTester $I)
	{
		$count= count($this->ruleTypes);
	
		$ruleNumber= 0;
		$delta= 1;
		$moveTo= 0;
		while ($ruleNumber < $count) {
			$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=filter&rulenumber=$ruleNumber");

			$moveTo= $ruleNumber + $delta;

			$I->fillField('#ruleNumber', $ruleNumber);
			$I->fillField('#moveTo', $moveTo);
			$I->click('Move');
			
			$I->seeNumberOfElements(\Codeception\Util\Locator::find('tr', ['title' => 'Filter rule']), 1);

			if ($moveTo < $count) {
				$I->dontSeeLink('e', "http://pfre/pf/conf.editor.php?sender=filter&rulenumber=$ruleNumber");
				$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=filter&rulenumber=$moveTo");
			} else {
				$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=filter&rulenumber=$ruleNumber");
				$I->dontSeeLink('e', "http://pfre/pf/conf.editor.php?sender=filter&rulenumber=$moveTo");
			}

			$ruleNumber= $moveTo;
			$delta*= 2;
		}
	}

	/**
	 * @before loadTestRules
	 */
	public function testMoveUp(AcceptanceTester $I)
	{
		$count= count($this->ruleTypes);

		$ruleNumber= $count - 1;
		$delta= 1;
		while ($ruleNumber >= 0) {
			$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=comment&rulenumber=$ruleNumber");

			$moveTo= $ruleNumber - $delta;

			$I->fillField('#ruleNumber', $ruleNumber);
			$I->fillField('#moveTo', $moveTo);
			$I->click('Move');
			
			$I->seeNumberOfElements(\Codeception\Util\Locator::find('tr', ['title' => 'Filter rule']), 1);

			if ($moveTo >= 0) {
				$I->dontSeeLink('e', "http://pfre/pf/conf.editor.php?sender=comment&rulenumber=$ruleNumber");
				$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=comment&rulenumber=$moveTo");
			} else {
				$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=comment&rulenumber=$ruleNumber");
				$I->dontSeeLink('e', "http://pfre/pf/conf.editor.php?sender=comment&rulenumber=$moveTo");
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

		$I->seeNumberOfElements(['xpath' => '//a[contains(@href, "conf.editor.php?del=")]'], $count);

		$I->click('Delete All');

		$I->wait(POPUP_DISPLAY_INTERVAL);
		$I->seeInPopup('Are you sure you want to delete the entire ruleset?');
		$I->acceptPopup();
		$I->wait(POPUP_DISPLAY_INTERVAL);

		$I->seeNumberOfElements(['xpath' => '//a[contains(@href, "conf.editor.php?del=")]'], 0);
	}

	/**
	 * @before loadTestRules
	 */
	public function testDeleteAllCancel(AcceptanceTester $I)
	{		
		$count= count($this->ruleTypes);

		$I->seeNumberOfElements(['xpath' => '//a[contains(@href, "conf.editor.php?del=")]'], $count);

		$I->click('Delete All');

		$I->wait(POPUP_DISPLAY_INTERVAL);
		$I->seeInPopup('Are you sure you want to delete the entire ruleset?');
		$I->cancelPopup();

		$I->seeNumberOfElements(['xpath' => '//a[contains(@href, "conf.editor.php?del=")]'], $count);
	}

	/**
	 * @before loadTestRules
	 */
	public function testDown(AcceptanceTester $I)
	{
		$count= count($this->ruleTypes);
	
		$ruleNumber= 0;
		while ($ruleNumber < $count - 1) {
			$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=filter&rulenumber=$ruleNumber");

			$I->click(['xpath' => '//a[contains(@href, "conf.editor.php?down=' . $ruleNumber . '")]']);

			$I->wait(STALE_ELEMENT_INTERVAL);
			$I->dontSeeLink('e', "http://pfre/pf/conf.editor.php?sender=filter&rulenumber=$ruleNumber");

			$ruleNumber++;
			$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=filter&rulenumber=$ruleNumber");
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
			$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=comment&rulenumber=$ruleNumber");

			$I->click(['xpath' => '//a[contains(@href, "conf.editor.php?up=' . $ruleNumber . '")]']);

			$I->wait(STALE_ELEMENT_INTERVAL);
			$I->dontSeeLink('e', "http://pfre/pf/conf.editor.php?sender=comment&rulenumber=$ruleNumber");

			$ruleNumber--;
			$I->seeLink('', "http://pfre/pf/conf.editor.php?sender=comment&rulenumber=$ruleNumber");
		}
	}

	/// @attention Make logout a test too, so that we always logout in the end
	public function logout(AcceptanceTester $I)
	{
		$I->click('#rightmenu');
		$I->wait(POPUP_DISPLAY_INTERVAL);
		$I->seeLink('Logout');
		$I->click('Logout');

		$I->seeInCurrentUrl('login.php');
	}
}
?>

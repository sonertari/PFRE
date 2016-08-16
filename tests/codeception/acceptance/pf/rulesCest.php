<?php 
/* $pfre: rulesCest.php,v 1.1 2016/08/14 13:13:29 soner Exp $ */

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

	/**
	 * @before login
	 */
	public function testShowTable(AcceptanceTester $I)
	{
		$I->click('Load & Save');
		$I->see('Load rulebase');

		$I->attachFile(\Codeception\Util\Locator::find('input', ['type' => 'file']), 'pf.conf');

		$I->click('Upload');
		
		/// @todo Check why submitForm() gives Element state error
		//$I->submitForm(\Codeception\Util\Locator::find('form', ['enctype' => 'multipart/form-data']),
		//$I->submitForm('#uploadForm',
		//	array(
		//		'file' => array(
		//			'name' => 'pf.conf'
		//			),
		//		'max_file_size' => '300000',
		//		'submitButton' => 'Upload'
		//		)
		//	);

		$I->click('Rules');

		$I->seeOptionIsSelected('category', 'All');

		$I->selectOption('category', 'Table');
		$I->seeOptionIsSelected('category', 'Table');

		$I->click('Show');
		
		$I->expect('the list has table rules, and table rules only');
		$I->seeNumberOfElements(['xpath' => '//a[contains(@href, "conf.php?del=")]'], 4);
		$I->seeNumberOfElements(\Codeception\Util\Locator::find('tr', ['title' => 'Table rule']), 4);
		$I->seeNumberOfElements(\Codeception\Util\Locator::find('tr', ['title' => 'Table rule', 'class' => 'oddline']), 2);

		$I->expect('table rule numbers are 11, 12, 13, 14');
		$I->seeLink('e', 'http://pfre/pf/conf.php?sender=table&rulenumber=11');
		$I->seeLink('e', 'http://pfre/pf/conf.php?sender=table&rulenumber=12');
		$I->seeLink('e', 'http://pfre/pf/conf.php?sender=table&rulenumber=13');
		$I->seeLink('e', 'http://pfre/pf/conf.php?sender=table&rulenumber=14');
	}

	/**
	 * @depends testShowTable
	 */
	public function testEditTable(AcceptanceTester $I)
	{
		// These 3 methods all work
		//$I->click(['xpath' => '//a[contains(@href, "rulenumber=14")]']);
		//$I->click('//a[contains(@href, "rulenumber=14")]');
		$I->click(\Codeception\Util\Locator::href('conf.php?sender=table&rulenumber=14'));

		$I->see('table <id> persist const counters file "/etc/pf.restrictedips" { 192.168.0.1 }');

		$I->fillField('identifier', 'test');
		$I->click('Apply');

		$I->fillField('addValue', '1.1.1.1');
		$I->click('Apply');

		$I->see('table <test> persist const counters file "/etc/pf.restrictedips" { 192.168.0.1, 1.1.1.1 }');

		$I->click('Save');
	}

	/**
	 * @attention Use Selenium 3.0.0-beta4, works fine even with ChromeDriver 2.21
	 * $ java -jar selenium-server-standalone-3.0.0-beta4.jar
	 * 
	 * @attention With Selenium 2.53.1 use ChromeDriver >=2.24, otherwise the driver cannot even see popups
	 * $ java -jar selenium-server-standalone-2.53.1.jar -Dwebdriver.chrome.driver=chromedriver
	 * 
	 * @attention Selenium 3.0.0-beta4 command line is different from that of 2.53.1 above, if an option to be provided:
	 * $ java -Dwebdriver.chrome.driver=chromedriver -jar selenium-server-standalone-3.0.0-beta4.jar
	 * 
	 * @depends testEditTable
	 * @after logout
	 */
	public function testDeleteTable(AcceptanceTester $I)
	{
		/// @todo Check why the following 3 methods do not work, a bug in xpath locator?
		//$I->click(\Codeception\Util\Locator::href('conf.php?del=14'));
		//$aLink = $I->grabMultiple(['xpath' => '//a[contains(@href, "conf.php?del=14")]'], 'href');
		//$I->click(\Codeception\Util\Locator::href($aLink[0]));
		//$I->click(['xpath' => '//a[@href="http://pfre/pf/conf.php?del=14"]']);

		$I->click(['xpath' => '//a[contains(@href, "conf.php?del=14")]']);
		
		/// @todo May need to wait if the webdriver or the server is slow?
		$I->wait(1);
		$I->seeInPopup('Are you sure you want to delete Table rule number 14?');

		//$I->wait(1);
		$I->acceptPopup();

		//$I->wait(1);
		$I->expect('rule 14 is deleted');
		$I->seeLink('e', 'http://pfre/pf/conf.php?sender=table&rulenumber=11');
		$I->seeLink('e', 'http://pfre/pf/conf.php?sender=table&rulenumber=12');
		$I->seeLink('e', 'http://pfre/pf/conf.php?sender=table&rulenumber=13');
		$I->dontSeeLink('e', 'http://pfre/pf/conf.php?sender=table&rulenumber=14');
	}

	protected function logout(AcceptanceTester $I)
	{
		$I->seeLink('Logout');
		$I->click('Logout');

		$I->seeInCurrentUrl('login.php');
	}
}
?>
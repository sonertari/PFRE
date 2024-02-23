<?php 
/*
 * Copyright (C) 2004-2024 Soner Tari
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

class confsetupCest
{
	public function _before(Helper\ConfigureWebDriver $config)
	{
		/// @attention Disable clear_cookies before each test
		// Because Codeception enables clear_cookies after each test function
		$config->setClearCookies(FALSE);
	}

	protected function login(AcceptanceTester $I)
	{
		$I->amOnPage('/');

		$I->see('PF Rule Editor');
		$I->see('User');
		$I->see('Password');

		$I->fillField('UserName', 'admin');
		$I->fillField('Password', 'soner123');
		$I->click('Login');

		$I->seeInCurrentUrl('pf/conf.editor.php');

		$I->click('#rightmenu');
		$I->wait(POPUP_DISPLAY_INTERVAL);
		$I->see('Language');
		$I->click('#languagemenu');
		$I->wait(POPUP_DISPLAY_INTERVAL);
		$I->see('English');
		$I->click('English');

		$I->click('Setup');
	}

	/**
	 * @before login
	 */
	public function testChangePasswordAdmin(AcceptanceTester $I)
	{
		$I->seeInField('User', 'admin');
		$I->fillField('CurrentPassword', 'soner123');
		$I->fillField('NewPassword', 'soner124');
		$I->fillField('ReNewPassword', 'soner124');

		$I->click('#ApplyPassword');

		$I->seeInCurrentUrl('login.php');

		$I->fillField('UserName', 'admin');
		$I->fillField('Password', 'soner124');
		$I->click('Login');

		$I->seeInCurrentUrl('pf/conf.editor.php');

		$I->click('Setup');

		$I->seeInField('User', 'admin');
		$I->fillField('CurrentPassword', 'soner124');
		$I->fillField('NewPassword', 'soner123');
		$I->fillField('ReNewPassword', 'soner123');

		$I->click('#ApplyPassword');

		$I->seeInCurrentUrl('login.php');
	}

	/**
	 * @before login
	 * @depends testChangePasswordAdmin
	 */
	public function testChangePasswordUser(AcceptanceTester $I)
	{
		$I->fillField('User', 'user');
		$I->fillField('CurrentPassword', 'soner123');
		$I->fillField('NewPassword', 'soner124');
		$I->fillField('ReNewPassword', 'soner124');

		$I->click('#ApplyPassword');

		$I->see('User password changed: user');
		$I->seeInCurrentUrl('pf/conf.setup.php');
		$this->logout($I);

		$I->fillField('UserName', 'user');
		$I->fillField('Password', 'soner124');
		$I->click('Login');

		$I->seeInCurrentUrl('pf/conf.editor.php');
		$this->logout($I);
	}

	/**
	 * @before login
	 * @depends testChangePasswordUser
	 */
	public function testChangePasswordUserWithoutPasswd(AcceptanceTester $I)
	{
		$I->fillField('User', 'user');
		// Admin can change user password without knowing current user password
		$I->fillField('CurrentPassword', '');
		$I->fillField('NewPassword', 'soner123');
		$I->fillField('ReNewPassword', 'soner123');

		$I->click('#ApplyPassword');

		$I->see('User password changed: user');
		$I->seeInCurrentUrl('pf/conf.setup.php');
		$this->logout($I);

		$I->fillField('UserName', 'user');
		$I->fillField('Password', 'soner123');
		$I->click('Login');

		$I->seeInCurrentUrl('pf/conf.editor.php');
		$this->logout($I);
	}

	/**
	 * @before login
	 * @depends testChangePasswordUserWithoutPasswd
	 */
	public function testChangePasswordAdminAuthFail(AcceptanceTester $I)
	{
		$I->seeInField('User', 'admin');
		$I->fillField('CurrentPassword', 'soner124');
		$I->fillField('NewPassword', 'soner123');
		$I->fillField('ReNewPassword', 'soner123');

		$I->click('#ApplyPassword');
		$I->wait(STALE_ELEMENT_INTERVAL);

		$I->see('Authentication failed');
		$I->seeInCurrentUrl('pf/conf.setup.php');
	}

	/**
	 * @depends testChangePasswordAdminAuthFail
	 */
	public function testChangePasswordAdminInvalidPasswd(AcceptanceTester $I)
	{
		$I->seeInField('User', 'admin');
		$I->fillField('CurrentPassword', 'soner123');
		$I->fillField('NewPassword', 'soner');
		$I->fillField('ReNewPassword', 'soner');

		$I->click('#ApplyPassword');

		$I->see('Not a valid password');
		$I->seeInCurrentUrl('pf/conf.setup.php');
	}

	/**
	 * @depends testChangePasswordAdminInvalidPasswd
	 */
	public function testChangePasswordAdminPasswdsNoMatch(AcceptanceTester $I)
	{
		$I->seeInField('User', 'admin');
		$I->fillField('CurrentPassword', 'soner123');
		$I->fillField('NewPassword', 'soner');
		$I->fillField('ReNewPassword', 'soner123');

		$I->click('#ApplyPassword');

		$I->see('Passwords do not match');
		$I->seeInCurrentUrl('pf/conf.setup.php');
	}

	/**
	 * @depends testChangePasswordAdminPasswdsNoMatch
	 */
	public function testChangePasswordAdminInvalidUser(AcceptanceTester $I)
	{
		$I->fillField('User', 'soner');

		$I->click('#ApplyPassword');

		$I->see('pfre currently supports only admin and user usernames');
		$I->seeInCurrentUrl('pf/conf.setup.php');
	}

	public function testSetLogLevel(AcceptanceTester $I)
	{
		$I->selectOption('LogLevel', 'LOG_ERR');

		$I->click('#ApplyLogLevel');

		$I->seeOptionIsSelected('LogLevel', 'LOG_ERR');

		$I->selectOption('LogLevel', 'LOG_INFO');

		$I->click('#ApplyLogLevel');

		$I->seeOptionIsSelected('LogLevel', 'LOG_INFO');
	}

	public function testSetHelpBoxes(AcceptanceTester $I)
	{
		$I->see('This setting enables or disables help boxes');
		$I->see('These defaults are permanently stored in web user interface settings');

		$I->click('DisableHelpBoxes');

		$I->dontSee('This setting enables or disables help boxes');
		$I->dontSee('These defaults are permanently stored in web user interface settings');

		$I->click('EnableHelpBoxes');

		$I->see('This setting enables or disables help boxes');
		$I->see('These defaults are permanently stored in web user interface settings');
	}

	public function testSetSessionTimeout(AcceptanceTester $I)
	{
		$I->fillField('SessionTimeout', '5');

		$I->click('#ApplySessionTimeout');

		$I->seeInField('SessionTimeout', '10');
		$I->see('0:10');

		$I->fillField('SessionTimeout', '300');

		$I->click('#ApplySessionTimeout');

		$I->seeInField('SessionTimeout', '300');
		$I->see('5:00');
	}

	public function testSetMaxAnchorNesting(AcceptanceTester $I)
	{
		$I->fillField('MaxAnchorNesting', '5');

		$I->click('#ApplyMaxAnchorNesting');

		$I->seeInField('MaxAnchorNesting', '5');

		$I->fillField('MaxAnchorNesting', '2');

		$I->click('#ApplyMaxAnchorNesting');

		$I->seeInField('MaxAnchorNesting', '2');
	}

	public function testSetPfctlTimeout(AcceptanceTester $I)
	{
		$I->fillField('PfctlTimeout', '0');

		$I->click('#ApplyPfctlTimeout');

		$I->seeInField('PfctlTimeout', '0');

		$I->fillField('PfctlTimeout', '5');

		$I->click('#ApplyPfctlTimeout');

		$I->seeInField('PfctlTimeout', '5');
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

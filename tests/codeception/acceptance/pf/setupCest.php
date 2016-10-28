<?php 
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

class setupCest
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

		$I->seeInCurrentUrl('pf/conf.php');

		$I->selectOption('#Locale', 'English');
		$I->seeOptionIsSelected('#Locale', 'English');

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

		$I->seeInCurrentUrl('pf/conf.php');

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
		$I->seeInCurrentUrl('pf/conf.php');
		$this->logout($I);

		$I->fillField('UserName', 'user');
		$I->fillField('Password', 'soner124');
		$I->click('Login');

		$I->seeInCurrentUrl('pf/conf.php');
		$I->See('Resource not available: setup');
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
		$I->seeInCurrentUrl('pf/conf.php');
		$this->logout($I);

		$I->fillField('UserName', 'user');
		$I->fillField('Password', 'soner123');
		$I->click('Login');

		$I->seeInCurrentUrl('pf/conf.php');
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

		$I->see('Authentication failed');
		$I->seeInCurrentUrl('pf/conf.php');
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
		$I->seeInCurrentUrl('pf/conf.php');
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
		$I->seeInCurrentUrl('pf/conf.php');
	}

	/**
	 * @depends testChangePasswordAdminPasswdsNoMatch
	 */
	public function testChangePasswordAdminInvalidUser(AcceptanceTester $I)
	{
		$I->fillField('User', 'soner');

		$I->click('#ApplyPassword');

		$I->see('pfre currently supports only admin and user usernames');
		$I->seeInCurrentUrl('pf/conf.php');
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
		$I->see('Logout (0:10) admin@');

		$I->fillField('SessionTimeout', '300');

		$I->click('#ApplySessionTimeout');

		$I->seeInField('SessionTimeout', '300');
		$I->see('Logout (5:00) admin@');
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
		$I->seeLink('Logout');
		$I->click('Logout');

		$I->seeInCurrentUrl('login.php');
	}
}
?>
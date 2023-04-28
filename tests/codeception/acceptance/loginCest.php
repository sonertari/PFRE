<?php 
/*
 * Copyright (C) 2004-2023 Soner Tari
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

class loginCest
{
	/**
	 * @example {"UserName": "admin", "Password": "soner123"}
	 * @example {"UserName": "user", "Password": "soner123"}
	 */
	public function testLogin(AcceptanceTester $I, \Codeception\Example $example)
	{
		$I->wantTo('test ' . $example['UserName'] . ' login');

		$I->amOnPage('/');

		$I->see('PF Rule Editor');
		$I->see('User');
		$I->see('Password');

		$I->fillField('UserName', $example['UserName']);
		$I->fillField('Password', $example['Password']);
		$I->click('Login');

		$I->seeInCurrentUrl('pf/conf.editor.php');
		$I->click('#rightmenu');
		$I->wait(POPUP_DISPLAY_INTERVAL);
		$I->see($example['UserName'] . '@');

		$I->seeLink('Logout');
		$I->click('Logout');

		$I->seeInCurrentUrl('login.php');
	}

	/**
	 * @depends testLogin
	 * @example {"UserName": "soner", "Password": "soner123"}
	 * @example {"UserName": "user", "Password": "soner124"}
	 */
	public function testLoginFail(AcceptanceTester $I, \Codeception\Example $example)
	{
		$I->wantTo('test ' . $example['UserName'] . ' login fail');

		$I->amOnPage('/');

		$I->see('PF Rule Editor');
		$I->see('User');
		$I->see('Password');

		$I->fillField('UserName', $example['UserName']);
		$I->fillField('Password', $example['Password']);
		$I->click('Login');

		$I->waitForElement('#authbox', 10);

		$I->seeInCurrentUrl('login.php');
	}
}
?>
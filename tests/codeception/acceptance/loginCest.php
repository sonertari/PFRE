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

		$I->seeInCurrentUrl('pf/conf.php');
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

		$I->dontSeeInCurrentUrl('pf/conf.php');
		$I->dontSee($example['UserName'] . '@');

		$I->seeInCurrentUrl('login.php');
	}
}
?>
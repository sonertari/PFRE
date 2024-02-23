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

class conffilesCest
{
	private $testRules= 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 set queue (std, service) rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) # Test
antispoof log ( all, matches, user, to pflog0 ) quick for em0 inet label "test" # Test
anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 set queue (std, service) rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) {
	block
	anchor out {
		pass proto tcp from any to port { 25, 80, 443 }
	}
	pass in proto tcp to any port 22
} # Test
test = "{ ssh, 2222 }" # Test
table <test> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test
pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 set queue (std, service) rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test
match in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 set queue (std, service) rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) nat-to 192.168.0.1 port ssh source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address static-port # Test
pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 set queue (std, service) rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) binat-to 192.168.0.1 port ssh source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test
pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 set queue (std, service) rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-to 192.168.0.1 port ssh # Test
pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 set queue (std, service) rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test
pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 set queue (std, service) rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) rdr-to 192.168.0.1 port ssh source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test
pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 set queue (std, service) rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) route-to { 192.168.0.1, 192.168.0.2 } source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test
queue test on em0 parent rootq bandwidth 20M burst 90M for 100ms min 5M burst 10M for 50ms max 100M burst 1M for 10ms flows 1024 quantum 1 qlimit 100 default # Test
pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh scrub (no-df, min-ttl 1, max-mss 2, random-id, reassemble tcp) user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 set queue (std, service) rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) # Test
set skip on { lo, em0 } # Test
set timeout { frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test
set limit { states 1, frags 2, src-nodes 3, tables 4, table-entries 5 } # Test
set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test
load anchor test from "/etc/pfre/include.conf" # Test
include "/etc/pfre/include.conf" # Test


# Line1
# Line2
';

	private $HOME;

	function __construct()
	{
		// Works with Chrome, but not ideal
		$this->HOME= posix_getpwuid(posix_getuid())['dir'];
	}

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
	}

	/**
	 * @before login
	 */
	public function testUpload(AcceptanceTester $I)
	{
		$I->click('Load & Save');
		$I->wait(STALE_ELEMENT_INTERVAL);
		$I->seeInCurrentUrl('conf.files.php');
		$I->see('Load ruleset');

		$I->attachFile(\Codeception\Util\Locator::find('input', ['type' => 'file']), 'test.conf');

		$I->click('Upload');
		/// @attention test.conf does not require #forceupload, because it does not have validation errors
		$I->see('File uploaded: test.conf');
	}

	/**
	 * @depends testUpload
	 */
	public function testSave(AcceptanceTester $I)
	{
		$I->fillField('#saveFilename', 'test.conf');
		$I->click('Save');

		$I->see('Failed saving: /etc/pfre/test.conf, ruleset has errors');
		
		$I->seeInField('#saveFilename', 'test.conf');
		$I->checkOption('#forcesave');
		$I->click('Save');

		$I->see('Saved: /etc/pfre/test.conf');
	}

	/**
	 * @depends testSave
	 */
	public function testLoad(AcceptanceTester $I)
	{
		$I->selectOption('#filename', 'test.conf');
		$I->seeOptionIsSelected('#filename', 'test.conf');

		$I->click('Load');

		/// @attention test.conf does not require #forceupload, because it does not have validation errors
		$I->see('Rules loaded: /etc/pfre/test.conf');
	}

	/**
	 * @depends testLoad
	 */
	public function testDownload(AcceptanceTester $I, Codeception\Test\Unit $tester)
	{
		$file= "$this->HOME/Downloads/test.conf";

		if (file_exists($file)) {
			unlink($file);
		}

		$I->click('Download');

		$I->wait(3);
		$actual= file_get_contents($file);

		$tester->assertEquals($this->testRules, $actual);
	}

	/**
	 * @depends testDownload
	 */
	public function testReload(AcceptanceTester $I)
	{
		$I->click('Reload');

		$I->see('Main pf rules reloaded: /etc/pf.conf');
	}

	/**
	 * @depends testReload
	 */
	public function testDelete(AcceptanceTester $I)
	{
		$I->selectOption('#deleteFilename', 'test.conf');
		$I->seeOptionIsSelected('#deleteFilename', 'test.conf');

		$I->click('Delete');

		$I->wait(POPUP_DISPLAY_INTERVAL);
		$I->seeInPopup('Are you sure you want to delete the rules file?');
		$I->acceptPopup();
		$I->wait(POPUP_DISPLAY_INTERVAL);

		$I->see('Rules file deleted: /etc/pfre/test.conf');

		$I->dontSeeOptionIsSelected('#deleteFilename', 'test.conf');
	}

	/**
	 * @depends testDelete
	 */
	public function testUploadFail(AcceptanceTester $I)
	{
		$I->attachFile(\Codeception\Util\Locator::find('input', ['type' => 'file']), 'dump.sql');

		$I->dontSeeCheckboxIsChecked('#forceupload');
		$I->click('Upload');

		$I->see('0: Error loading, rule loaded partially');
		$I->see('Load Error: Ruleset contains errors');
		$I->see('Failed uploading: dump.sql');
	}

	/**
	 * @depends testUploadFail
	 */
	public function testUploadForce(AcceptanceTester $I)
	{
		$I->attachFile(\Codeception\Util\Locator::find('input', ['type' => 'file']), 'dump.sql');

		$I->checkOption('#forceupload');
		$I->click('Upload');

		$I->see('0: Error loading, rule load forced');
		$I->see('Load Error: Ruleset contains errors');
		$I->see('File uploaded: dump.sql');
	}

	/**
	 * @depends testUploadForce
	 */
	public function testSaveFail(AcceptanceTester $I)
	{
		$I->dontSeeCheckboxIsChecked('#forcesave');
		$I->fillField('#saveFilename', 'dump.conf');
		$I->click('Save');

		$I->see('0: Error loading, rule loaded partially');
		$I->see('Will not test rules with errors');
		$I->see('Failed saving: /etc/pfre/dump.conf, ruleset has errors');
	}

	/**
	 * @depends testSaveFail
	 */
	public function testSaveForce(AcceptanceTester $I)
	{
		$I->checkOption('#forcesave');
		$I->seeInField('#saveFilename', 'dump.conf');
		$I->click('Save');

		$I->see('0: Error loading, rule load forced');
		$I->see('Saved: /etc/pfre/dump.conf');
	}

	/**
	 * @depends testSaveForce
	 */
	public function testLoadFail(AcceptanceTester $I)
	{
		$I->dontSeeCheckboxIsChecked('#forceload');
		$I->selectOption('#filename', 'dump.conf');
		$I->seeOptionIsSelected('#filename', 'dump.conf');

		$I->click('Load');

		$I->see('0: Error loading, rule loaded partially');
		$I->see('Load Error: Ruleset contains errors');
		$I->see('Failed loading: /etc/pfre/dump.conf');
	}

	/**
	 * @depends testLoadFail
	 */
	public function testLoadForce(AcceptanceTester $I)
	{
		$I->checkOption('#forceload');
		$I->seeOptionIsSelected('#filename', 'dump.conf');

		$I->click('Load');

		$I->see('0: Error loading, rule load forced');
		$I->see('Load Error: Ruleset contains errors');
		$I->see('Rules loaded: /etc/pfre/dump.conf');
	}

	/**
	 * @depends testLoadForce
	 */
	public function testDownloadFail(AcceptanceTester $I, Codeception\Test\Unit $tester)
	{
		$file= "$this->HOME/Downloads/dump.conf";

		if (file_exists($file)) {
			unlink($file);
		}

		$I->dontSeeCheckboxIsChecked('#forcedownload');
		$I->click('Download');

		$I->see('0: Error loading, rule loaded partially');
		$I->see('Failed downloading, cannot generate pf rules');

		$tester->assertFileNotExists($file);
	}

	/**
	 * @depends testDownloadFail
	 */
	public function testDownloadForce(AcceptanceTester $I, Codeception\Test\Unit $tester)
	{
		$file= "$this->HOME/Downloads/dump.conf";

		if (file_exists($file)) {
			unlink($file);
		}

		$I->checkOption('#forcedownload');
		$I->click('Download');

		$I->wait(3);
		$actual= file_get_contents($file);

		$tester->assertEquals('/* = "this"
', $actual);

		// Still see the same failed message, because the page exits after sending file
		$I->see('Failed downloading, cannot generate pf rules');

		// Clean-up after ourselves
		$I->selectOption('#deleteFilename', 'dump.conf');
		$I->seeOptionIsSelected('#deleteFilename', 'dump.conf');

		$I->click('Delete');

		$I->wait(POPUP_DISPLAY_INTERVAL);
		$I->seeInPopup('Are you sure you want to delete the rules file?');
		$I->acceptPopup();
		$I->wait(POPUP_DISPLAY_INTERVAL);

		$I->see('Rules file deleted: /etc/pfre/dump.conf');

		$I->dontSeeOptionIsSelected('#deleteFilename', 'dump.conf');
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

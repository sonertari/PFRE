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

require_once ('Rule.php');

class TableCest extends Rule
{
	protected $type= 'Table';
	protected $ruleNumber= 4;
	protected $lineNumber= 10;
	protected $sender= 'table';

	protected $origRule= 'table <test> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test';
	protected $expectedDispOrigRule= 'test const persist counters 192.168.0.1
192.168.0.2
file "/etc/pf.restrictedips1"
file "/etc/pf.restrictedips2"
Test e u d x';

	protected $modifiedRule= 'table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1';
	protected $expectedDispModifiedRule= 'test1 192.168.0.1
192.168.0.2
1.1.1.1
file "/etc/pf.restrictedips1"
file "/etc/pf.restrictedips2"
file "/etc/pf.restrictedips3"
Test1 e u d x';

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->fillField('identifier', 'test1');
		$this->clickApplySeeResult($I, 'table <test1> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test');

		$I->uncheckOption('#const');
		$this->clickApplySeeResult($I, 'table <test1> persist counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test');

		$I->uncheckOption('#persist');
		$this->clickApplySeeResult($I, 'table <test1> counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test');

		$I->uncheckOption('#counters');
		$this->clickApplySeeResult($I, 'table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test');
		
		$I->fillField('addValue', '1.1.1.1');
		$this->clickApplySeeResult($I, 'table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test');

		$I->fillField('addFile', '/etc/pf.restrictedips3');
		$this->clickApplySeeResult($I, 'table <test1> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test');

		$I->fillField('comment', 'Test1');
		$this->clickApplySeeResult($I, $this->modifiedRule);
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->fillField('identifier', 'test');
		$this->clickApplySeeResult($I, 'table <test> file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#const');
		$this->clickApplySeeResult($I, 'table <test> const file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#persist');
		$this->clickApplySeeResult($I, 'table <test> persist const file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#counters');
		$this->clickApplySeeResult($I, 'table <test> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2, 1.1.1.1 } # Test1');
		
		$this->clickDeleteLink($I, 'delValue', '1.1.1.1');
		$this->seeResult($I, 'table <test> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" file "/etc/pf.restrictedips3" { 192.168.0.1, 192.168.0.2 } # Test1');

		$this->clickDeleteLink($I, 'delFile', '/etc/pf.restrictedips3');
		$this->seeResult($I, 'table <test> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 } # Test1');

		$I->fillField('comment', 'Test');
		$this->clickApplySeeResult($I, $this->revertedRule);
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->fillField('identifier', 'test1');
		$I->uncheckOption('#const');
		$I->uncheckOption('#persist');
		$I->uncheckOption('#counters');
		$I->fillField('addValue', '1.1.1.1');
		$I->fillField('addFile', '/etc/pf.restrictedips3');
		$I->fillField('comment', 'Test1');
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->fillField('identifier', 'test');
		$I->checkOption('#const');
		$I->checkOption('#persist');
		$I->checkOption('#counters');
		$I->click('Apply');
		
		$this->clickDeleteLink($I, 'delValue', '1.1.1.1');
		$this->clickDeleteLink($I, 'delFile', '/etc/pf.restrictedips3');

		$I->fillField('comment', 'Test');
		$I->click('Apply');
	}
}
?>
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

class LimitCest extends Rule
{
	protected $type= 'Limit';
	protected $ruleNumber= 16;
	protected $lineNumber= 22;
	protected $sender= 'limit';

	protected $origRule= 'set limit { states 1, frags 2, src-nodes 3, tables 4, table-entries 5 } # Test';
	protected $expectedDispOrigRule= 'states: 1, frags: 2, src-nodes: 3, tables: 4, table-entries: 5 Test e u d x';

	protected $modifiedRule= ' # Test1';
	protected $expectedDispModifiedRule= 'Test1 e u d x';

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->fillField('#states', '');
		$this->clickApplySeeResult($I, 'set limit { frags 2, src-nodes 3, tables 4, table-entries 5 } # Test');

		$I->fillField('#frags', '');
		$this->clickApplySeeResult($I, 'set limit { src-nodes 3, tables 4, table-entries 5 } # Test');

		$I->fillField('#srcnodes', '');
		$this->clickApplySeeResult($I, 'set limit { tables 4, table-entries 5 } # Test');

		$I->fillField('#tables', '');
		$this->clickApplySeeResult($I, 'set limit table-entries 5 # Test');

		$I->fillField('#table-entries', '');
		$this->clickApplySeeResult($I, ' # Test');

		$I->fillField('#comment', 'Test1');
		$this->clickApplySeeResult($I, $this->modifiedRule);
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->fillField('#states', '1');
		$this->clickApplySeeResult($I, 'set limit states 1 # Test1');

		$I->fillField('#frags', '2');
		$this->clickApplySeeResult($I, 'set limit { states 1, frags 2 } # Test1');

		$I->fillField('#srcnodes', '3');
		$this->clickApplySeeResult($I, 'set limit { states 1, frags 2, src-nodes 3 } # Test1');

		$I->fillField('#tables', '4');
		$this->clickApplySeeResult($I, 'set limit { states 1, frags 2, src-nodes 3, tables 4 } # Test1');

		$I->fillField('#table-entries', '5');
		$this->clickApplySeeResult($I, 'set limit { states 1, frags 2, src-nodes 3, tables 4, table-entries 5 } # Test1');

		$I->fillField('#comment', 'Test');
		$this->clickApplySeeResult($I, $this->revertedRule);
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->fillField('#states', '');
		$I->fillField('#frags', '');
		$I->fillField('#srcnodes', '');
		$I->fillField('#tables', '');
		$I->fillField('#table-entries', '');
		$I->fillField('#comment', 'Test1');
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->fillField('#states', '1');
		$I->fillField('#frags', '2');
		$I->fillField('#srcnodes', '3');
		$I->fillField('#tables', '4');
		$I->fillField('#table-entries', '5');
		$I->fillField('#comment', 'Test');
		$I->click('Apply');
	}
}
?>
<?php
/* $pfre: RuleSetTest.php,v 1.1 2016/08/10 17:25:22 soner Exp $ */

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

namespace ViewTest;

class RuleSetTest extends \PHPUnit_Framework_TestCase
{
	function createRulesArray($ruleTypes)
	{
		global $TEST_PATH;

		$rules= array();

		foreach ($ruleTypes as $cat) {
			require_once ($TEST_PATH . '/Model/lib/' . ltrim($cat . 'Test', '_') . '.php');

			$catTest= 'ModelTest\\' . $cat . 'Test';
			$test= new $catTest();

			$rules[]= array(
				'cat' => $cat,
				'rule' => $test->rule,
				);
		}

		return $rules;
	}

	function testUp()
	{
		$actualRules= $this->createRulesArray(array('Filter', 'Antispoof'));

		$actualRuleSet= new \View\RuleSet();
		$actualRuleSet->loadArray($actualRules);

		$actualRuleSet->up(1);

		$expectedRules= $this->createRulesArray(array('Antispoof', 'Filter'));

		$expectedRuleSet= new \View\RuleSet();
		$expectedRuleSet->loadArray($expectedRules);

		$expected= $expectedRuleSet->rules;
		$actual= $actualRuleSet->rules;

		$this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual));
	}

	function testDown()
	{
		$actualRules= $this->createRulesArray(array('Filter', 'Antispoof'));

		$actualRuleSet= new \View\RuleSet();
		$actualRuleSet->loadArray($actualRules);

		$actualRuleSet->down(0);

		$expectedRules= $this->createRulesArray(array('Antispoof', 'Filter'));

		$expectedRuleSet= new \View\RuleSet();
		$expectedRuleSet->loadArray($expectedRules);

		$expected= $expectedRuleSet->rules;
		$actual= $actualRuleSet->rules;

		$this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual));
	}

	function testDel()
	{
		$actualRules= $this->createRulesArray(array('Filter', 'Antispoof'));

		$actualRuleSet= new \View\RuleSet();
		$actualRuleSet->loadArray($actualRules);

		$actualRuleSet->del(0);

		$expectedRules= $this->createRulesArray(array('Antispoof'));

		$expectedRuleSet= new \View\RuleSet();
		$expectedRuleSet->loadArray($expectedRules);

		$expected= $expectedRuleSet->rules;
		$actual= $actualRuleSet->rules;

		$this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual));
	}

	function testMove()
	{
		$actualRules= $this->createRulesArray(array('Filter', 'Antispoof'));

		$actualRuleSet= new \View\RuleSet();
		$actualRuleSet->loadArray($actualRules);

		$actualRuleSet->move(0, 1);

		$expectedRules= $this->createRulesArray(array('Antispoof', 'Filter'));

		$expectedRuleSet= new \View\RuleSet();
		$expectedRuleSet->loadArray($expectedRules);

		$expected= $expectedRuleSet->rules;
		$actual= $actualRuleSet->rules;

		$this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual));
	}

	function testAdd()
	{
		$actualRules= $this->createRulesArray(array('Filter', 'Antispoof'));

		$actualRuleSet= new \View\RuleSet();
		$actualRuleSet->loadArray($actualRules);

		$actualRuleSet->add(1);

		$expectedRules= $this->createRulesArray(array('Filter', 'Filter', 'Antispoof'));

		$expectedRuleSet= new \View\RuleSet();
		$expectedRuleSet->loadArray($expectedRules);
		$expectedRuleSet->rules[1]= '';

		$expected= $expectedRuleSet->rules;
		$actual= $actualRuleSet->rules;

		$this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual));
	}

	function testGetQueueNames()
	{
		$actualRules= $this->createRulesArray(array('Queue'));

		$actualRuleSet= new \View\RuleSet();
		$actualRuleSet->loadArray($actualRules);

		$expected= array('test');
		$actual= $actualRuleSet->getQueueNames();

		$this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual));
	}
}
?>
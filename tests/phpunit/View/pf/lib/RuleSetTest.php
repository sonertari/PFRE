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

namespace ViewTest;

use \View\RuleSet;

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

		$actualRuleSet= new RuleSet();
		$actualRuleSet->loadArray($actualRules);

		$actualRuleSet->up(1);

		$expectedRules= $this->createRulesArray(array('Antispoof', 'Filter'));

		$expectedRuleSet= new RuleSet();
		$expectedRuleSet->loadArray($expectedRules);

		$expected= $expectedRuleSet->rules;
		$actual= $actualRuleSet->rules;

		$this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual));
	}

	function testDown()
	{
		$actualRules= $this->createRulesArray(array('Filter', 'Antispoof'));

		$actualRuleSet= new RuleSet();
		$actualRuleSet->loadArray($actualRules);

		$actualRuleSet->down(0);

		$expectedRules= $this->createRulesArray(array('Antispoof', 'Filter'));

		$expectedRuleSet= new RuleSet();
		$expectedRuleSet->loadArray($expectedRules);

		$expected= $expectedRuleSet->rules;
		$actual= $actualRuleSet->rules;

		$this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual));
	}

	function testDel()
	{
		$actualRules= $this->createRulesArray(array('Filter', 'Antispoof'));

		$actualRuleSet= new RuleSet();
		$actualRuleSet->loadArray($actualRules);

		$actualRuleSet->del(0);

		$expectedRules= $this->createRulesArray(array('Antispoof'));

		$expectedRuleSet= new RuleSet();
		$expectedRuleSet->loadArray($expectedRules);

		$expected= $expectedRuleSet->rules;
		$actual= $actualRuleSet->rules;

		$this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual));
	}

	function testMove()
	{
		$actualRules= $this->createRulesArray(array('Filter', 'Antispoof'));

		$actualRuleSet= new RuleSet();
		$actualRuleSet->loadArray($actualRules);

		$actualRuleSet->move(0, 1);

		$expectedRules= $this->createRulesArray(array('Antispoof', 'Filter'));

		$expectedRuleSet= new RuleSet();
		$expectedRuleSet->loadArray($expectedRules);

		$expected= $expectedRuleSet->rules;
		$actual= $actualRuleSet->rules;

		$this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual));
	}

	function testAdd()
	{
		$actualRules= $this->createRulesArray(array('Filter', 'Antispoof'));

		$actualRuleSet= new RuleSet();
		$actualRuleSet->loadArray($actualRules);

		$actualRuleSet->add(1);

		$expectedRules= $this->createRulesArray(array('Filter', 'Filter', 'Antispoof'));

		$expectedRuleSet= new RuleSet();
		$expectedRuleSet->loadArray($expectedRules);
		$expectedRuleSet->rules[1]= '';

		$expected= $expectedRuleSet->rules;
		$actual= $actualRuleSet->rules;

		$this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual));
	}

	function testGetQueueNames()
	{
		$actualRules= $this->createRulesArray(array('Queue'));

		$actualRuleSet= new RuleSet();
		$actualRuleSet->loadArray($actualRules);

		$expected= array('test');
		$actual= $actualRuleSet->getQueueNames();

		$this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual));
	}

	function testIsNotModified()
	{
		$actualRules= $this->createRulesArray(array('Filter'));

		$actualRuleSet= new RuleSet();
		$actualRuleSet->loadArray($actualRules);

		$newRule= new \ModelTest\FilterTest();

		$this->assertFalse($actualRuleSet->isModified(0, $newRule));
	}

	function testIsModified()
	{
		$actualRules= $this->createRulesArray(array('Filter'));

		$actualRuleSet= new RuleSet();
		$actualRuleSet->loadArray($actualRules);

		$newRule= new \ModelTest\FilterTest();
		$newRule->rule['action']= 'match';

		$this->assertTrue($actualRuleSet->isModified(0, $newRule));
	}

	function testIsModifiedNonExistentRule()
	{
		$actualRules= $this->createRulesArray(array('Filter'));

		$actualRuleSet= new RuleSet();
		$actualRuleSet->loadArray($actualRules);

		$newRule= new \ModelTest\FilterTest();

		$this->assertTrue($actualRuleSet->isModified($actualRuleSet->nextRuleNumber(), $newRule));
	}
}
?>
<?php
/*
 * Copyright (C) 2004-2018 Soner Tari
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

namespace ModelTest;

use Model\RuleSet;

class RuleSetBase extends \PHPUnit_Framework_TestCase
{
	protected $cat= '';
	protected $catTest= '';

	protected $in= '';
	protected $rules= array();
	protected $out= '';

	function __construct()
	{
		if (preg_match('/^(' . __NAMESPACE__ . '\\.+)RuleSetTest$/', get_called_class(), $match)) {
			$this->cat= $match[1];
			$this->catTest= $this->cat . 'Test';

			$test= new $this->catTest();

			$this->in= $test->in;
			$this->rules= array(
					array(
					'cat' => $this->cat,
					'rule' => $test->rule,
					)
				);
			$this->out= $test->out;
		}

		parent::__construct();
	}

	function testParser() {
		$expected= $this->rules;
		ksort($expected);

		$ruleSet= new RuleSet();
		$ruleSet->parse($this->in);

		$actual= $ruleSet->rules;
		ksort($actual);

		$this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual));
	}

	function testGenerator() {
		$ruleSet= new RuleSet();
		$ruleSet->load($this->rules);

		$this->assertEquals($this->out, $ruleSet->generate());
	}
	
	function testParserGenerator() {
		$ruleSet= new RuleSet();
		$ruleSet->parse($this->in);

		$this->assertEquals($this->out, $ruleSet->generate());
	}
}
?>
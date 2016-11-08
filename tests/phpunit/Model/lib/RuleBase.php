<?php
/*
 * Copyright (C) 2004-2016 Soner Tari
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

class RuleBase extends \PHPUnit_Framework_TestCase
{
	protected $cat= '';

	public $in= '';
	public $rule= array();
	public $out= '';

	function __construct()
	{
		if (preg_match('/^' . __NAMESPACE__ . '\\\\(.+)Test$/', get_called_class(), $match)) {
			$this->cat= 'Model\\' . $match[1];
		}

		parent::__construct();
	}

	function testParser() {
		$rule= new $this->cat($this->in);

		$expected= $this->rule;
		ksort($expected);

		$actual= $rule->rule;
		ksort($actual);

		$this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual));
	}

	function testGenerator() {
		$rule= new $this->cat('');

		$rule->load($this->rule);

		$this->assertEquals($this->out, $rule->generate());
	}
	
	function testParserGenerator() {
		$rule= new $this->cat($this->in);

		$this->assertEquals($this->out, $rule->generate());
	}
}
?>
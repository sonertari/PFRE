<?php
/*
 * Copyright (C) 2004-2020 Soner Tari
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

class RuleSetTest extends RuleSetBase
{
	private $ruleTypes= array(
		'Filter',
		'Antispoof',
		'Anchor',
		'Macro',
		'Table',
		'AfTo',
		'NatTo',
		'BinatTo',
		'DivertTo',
		'DivertPacket',
		'RdrTo',
		'Route',
		'Queue',
		'Scrub',
		'Option',
		'Timeout',
		'Limit',
		'State',
		'LoadAnchor',
		'_Include',
		'Blank',
		'Comment',
	);

	function __construct()
	{
		parent::__construct();

		foreach ($this->ruleTypes as $cat) {
			require_once (ltrim($cat . 'Test', '_') . '.php');

			$catTest= __NAMESPACE__ . '\\' . $cat . 'Test';
			$test= new $catTest();

			$this->rules[]= array(
				'cat' => $cat,
				'rule' => $test->rule,
				);
			$this->out.= $test->out;
		}

		$this->in= $this->out;
	}

	function testGeneratorPrintNumbers()
	{
		$ruleSet= new RuleSet();
		$ruleSet->parse($this->out);

		$ruleNumber= 0;
		$s= '';
		foreach (explode("\n", $this->out) as $line) {
			$s.= sprintf('% 4d', $ruleNumber++) . ": $line\n";
		}
		$str= $s;

		$this->assertEquals($str, $ruleSet->generate(TRUE));
	}
}
?>
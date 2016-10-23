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
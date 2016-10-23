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
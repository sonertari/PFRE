<?php
/* $pfre: OptionTest.php,v 1.2 2016/08/10 05:45:34 soner Exp $ */

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

require_once('Rule.php');

class OptionTest extends RuleTest
{
	protected $ruleLoginterface= 'set loginterface $int_if';
	protected $sampleLoginterface= array(
		'type' => 'loginterface',
		'loginterface' => '$int_if',
		);

	protected $ruleBlockPolicy= 'set block-policy drop';
	protected $sampleBlockPolicy= array(
		'type' => 'block-policy',
		'block-policy' => 'drop',
		);

	protected $ruleStatePolicy= 'set state-policy if-bound';
	protected $sampleStatePolicy= array(
		'type' => 'state-policy',
		'state-policy' => 'if-bound',
		);

	protected $ruleOptimization= 'set optimization high-latency';
	protected $sampleOptimization= array(
		'type' => 'optimization',
		'optimization' => 'high-latency',
		);

	protected $ruleRulesetOptimization= 'set ruleset-optimization basic';
	protected $sampleRulesetOptimization= array(
		'type' => 'ruleset-optimization',
		'ruleset-optimization' => 'basic',
		);

	protected $ruleDebug= 'set debug notice';
	protected $sampleDebug= array(
		'type' => 'debug',
		'debug' => 'notice',
		);

	protected $ruleHostid= 'set hostid 1';
	protected $sampleHostid= array(
		'type' => 'hostid',
		'hostid' => '1',
		);

	protected $ruleSkip= 'set skip on { em0, em1 }';
	protected $sampleSkip= array(
		'type' => 'skip',
		'skip' => array(
			'em0',
			'em1',
			),
		);

	protected $ruleFingerprints= 'set fingerprints "/etc/pf.os"';
	protected $sampleFingerprints= array(
		'type' => 'fingerprints',
		'fingerprints' => '/etc/pf.os',
		);

	protected $ruleReassemble= 'set reassemble yes no-df';
	protected $sampleReassemble= array(
		'type' => 'reassemble',
		'reassemble' => 'yes',
		'no-df' => TRUE,
		);

	function __construct()
	{
		/// @attention Need one of the options tested here, otherwise base class test*() functions fail
		$this->sample= $this->sampleSkip;

		parent::__construct();

		$this->rule= $this->ruleSkip . $this->ruleComment;
	}

	function testParserLoginterface() {
		$this->rule= $this->ruleLoginterface . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleLoginterface,
			$this->sampleComment
			);

		$this->testParser();
	}

	function testGeneratorLoginterface() {
		$this->rule= $this->ruleLoginterface . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleLoginterface,
			$this->sampleComment
			);

		$this->testGenerator();
	}

	function testParserGeneratorLoginterface() {
		$this->rule= $this->ruleLoginterface . $this->ruleComment;

		$this->testParserGenerator();
	}

	function testParserBlockPolicy() {
		$this->rule= $this->ruleBlockPolicy . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleBlockPolicy,
			$this->sampleComment
			);

		$this->testParser();
	}

	function testGeneratorBlockPolicy() {
		$this->rule= $this->ruleBlockPolicy . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleBlockPolicy,
			$this->sampleComment
			);

		$this->testGenerator();
	}

	function testParserGeneratorBlockPolicy() {
		$this->rule= $this->ruleBlockPolicy . $this->ruleComment;

		$this->testParserGenerator();
	}

	function testParserStatePolicy() {
		$this->rule= $this->ruleStatePolicy . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleStatePolicy,
			$this->sampleComment
			);

		$this->testParser();
	}

	function testGeneratorStatePolicy() {
		$this->rule= $this->ruleStatePolicy . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleStatePolicy,
			$this->sampleComment
			);

		$this->testGenerator();
	}

	function testParserGeneratorStatePolicy() {
		$this->rule= $this->ruleStatePolicy . $this->ruleComment;

		$this->testParserGenerator();
	}

	function testParserOptimization() {
		$this->rule= $this->ruleOptimization . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleOptimization,
			$this->sampleComment
			);

		$this->testParser();
	}

	function testGeneratorOptimization() {
		$this->rule= $this->ruleOptimization . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleOptimization,
			$this->sampleComment
			);

		$this->testGenerator();
	}

	function testParserGeneratorOptimization() {
		$this->rule= $this->ruleOptimization . $this->ruleComment;

		$this->testParserGenerator();
	}

	function testParserRulesetOptimization() {
		$this->rule= $this->ruleRulesetOptimization . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleRulesetOptimization,
			$this->sampleComment
			);

		$this->testParser();
	}

	function testGeneratorRulesetOptimization() {
		$this->rule= $this->ruleRulesetOptimization . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleRulesetOptimization,
			$this->sampleComment
			);

		$this->testGenerator();
	}

	function testParserGeneratorRulesetOptimization() {
		$this->rule= $this->ruleRulesetOptimization . $this->ruleComment;

		$this->testParserGenerator();
	}

	function testParserDebug() {
		$this->rule= $this->ruleDebug . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleDebug,
			$this->sampleComment
			);

		$this->testParser();
	}

	function testGeneratorDebug() {
		$this->rule= $this->ruleDebug . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleDebug,
			$this->sampleComment
			);

		$this->testGenerator();
	}

	function testParserGeneratorDebug() {
		$this->rule= $this->ruleDebug . $this->ruleComment;

		$this->testParserGenerator();
	}

	function testParserHostid() {
		$this->rule= $this->ruleHostid . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleHostid,
			$this->sampleComment
			);

		$this->testParser();
	}

	function testGeneratorHostid() {
		$this->rule= $this->ruleHostid . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleHostid,
			$this->sampleComment
			);

		$this->testGenerator();
	}

	function testParserGeneratorHostid() {
		$this->rule= $this->ruleHostid . $this->ruleComment;

		$this->testParserGenerator();
	}

	function testParserFingerprints() {
		$this->rule= $this->ruleFingerprints . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleFingerprints,
			$this->sampleComment
			);

		$this->testParser();
	}

	function testGeneratorFingerprints() {
		$this->rule= $this->ruleFingerprints . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleFingerprints,
			$this->sampleComment
			);

		$this->testGenerator();
	}

	function testParserGeneratorFingerprints() {
		$this->rule= $this->ruleFingerprints . $this->ruleComment;

		$this->testParserGenerator();
	}

	function testParserReassemble() {
		$this->rule= $this->ruleReassemble . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleReassemble,
			$this->sampleComment
			);

		$this->testParser();
	}

	function testGeneratorReassemble() {
		$this->rule= $this->ruleReassemble . $this->ruleComment;
		$this->sample = array_merge(
			$this->sampleReassemble,
			$this->sampleComment
			);

		$this->testGenerator();
	}

	function testParserGeneratorReassemble() {
		$this->rule= $this->ruleReassemble . $this->ruleComment;

		$this->testParserGenerator();
	}
}
?>
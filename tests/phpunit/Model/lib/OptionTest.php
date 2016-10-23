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

require_once('Rule.php');

class OptionTest extends Rule
{
	protected $inLoginterface= 'set loginterface $int_if';
	protected $ruleLoginterface= array(
		'type' => 'loginterface',
		'loginterface' => '$int_if',
		);

	protected $inBlockPolicy= 'set block-policy drop';
	protected $ruleBlockPolicy= array(
		'type' => 'block-policy',
		'block-policy' => 'drop',
		);

	protected $inStatePolicy= 'set state-policy if-bound';
	protected $ruleStatePolicy= array(
		'type' => 'state-policy',
		'state-policy' => 'if-bound',
		);

	protected $inOptimization= 'set optimization high-latency';
	protected $ruleOptimization= array(
		'type' => 'optimization',
		'optimization' => 'high-latency',
		);

	protected $inRulesetOptimization= 'set ruleset-optimization basic';
	protected $ruleRulesetOptimization= array(
		'type' => 'ruleset-optimization',
		'ruleset-optimization' => 'basic',
		);

	protected $inDebug= 'set debug notice';
	protected $ruleDebug= array(
		'type' => 'debug',
		'debug' => 'notice',
		);

	protected $inHostid= 'set hostid 1';
	protected $ruleHostid= array(
		'type' => 'hostid',
		'hostid' => '1',
		);

	protected $inSkip= 'set skip on { lo, em0 }';
	protected $ruleSkip= array(
		'type' => 'skip',
		'skip' => array(
			'lo',
			'em0',
			),
		);

	protected $inFingerprints= 'set fingerprints "/etc/pf.os"';
	protected $ruleFingerprints= array(
		'type' => 'fingerprints',
		'fingerprints' => '/etc/pf.os',
		);

	protected $inReassemble= 'set reassemble yes no-df';
	protected $ruleReassemble= array(
		'type' => 'reassemble',
		'reassemble' => 'yes',
		'no-df' => TRUE,
		);

	function __construct()
	{
		/// @attention Need one of the options tested here, otherwise base class test*() functions fail
		$this->rule= $this->ruleSkip;

		parent::__construct();

		$this->in= $this->inSkip . $this->inComment;
		$this->out= $this->in . "\n";
	}

	function testParserLoginterface() {
		$this->in= $this->inLoginterface . $this->inComment;
		$this->rule = array_merge(
			$this->ruleLoginterface,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testParser();
	}

	function testGeneratorLoginterface() {
		$this->in= $this->inLoginterface . $this->inComment;
		$this->rule = array_merge(
			$this->ruleLoginterface,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testGenerator();
	}

	function testParserGeneratorLoginterface() {
		$this->in= $this->inLoginterface . $this->inComment;
		$this->out= $this->in . "\n";

		$this->testParserGenerator();
	}

	function testParserBlockPolicy() {
		$this->in= $this->inBlockPolicy . $this->inComment;
		$this->rule = array_merge(
			$this->ruleBlockPolicy,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testParser();
	}

	function testGeneratorBlockPolicy() {
		$this->in= $this->inBlockPolicy . $this->inComment;
		$this->rule = array_merge(
			$this->ruleBlockPolicy,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testGenerator();
	}

	function testParserGeneratorBlockPolicy() {
		$this->in= $this->inBlockPolicy . $this->inComment;
		$this->out= $this->in . "\n";

		$this->testParserGenerator();
	}

	function testParserStatePolicy() {
		$this->in= $this->inStatePolicy . $this->inComment;
		$this->rule = array_merge(
			$this->ruleStatePolicy,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testParser();
	}

	function testGeneratorStatePolicy() {
		$this->in= $this->inStatePolicy . $this->inComment;
		$this->rule = array_merge(
			$this->ruleStatePolicy,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testGenerator();
	}

	function testParserGeneratorStatePolicy() {
		$this->in= $this->inStatePolicy . $this->inComment;
		$this->out= $this->in . "\n";

		$this->testParserGenerator();
	}

	function testParserOptimization() {
		$this->in= $this->inOptimization . $this->inComment;
		$this->rule = array_merge(
			$this->ruleOptimization,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testParser();
	}

	function testGeneratorOptimization() {
		$this->in= $this->inOptimization . $this->inComment;
		$this->rule = array_merge(
			$this->ruleOptimization,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testGenerator();
	}

	function testParserGeneratorOptimization() {
		$this->in= $this->inOptimization . $this->inComment;
		$this->out= $this->in . "\n";

		$this->testParserGenerator();
	}

	function testParserRulesetOptimization() {
		$this->in= $this->inRulesetOptimization . $this->inComment;
		$this->rule = array_merge(
			$this->ruleRulesetOptimization,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testParser();
	}

	function testGeneratorRulesetOptimization() {
		$this->in= $this->inRulesetOptimization . $this->inComment;
		$this->rule = array_merge(
			$this->ruleRulesetOptimization,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testGenerator();
	}

	function testParserGeneratorRulesetOptimization() {
		$this->in= $this->inRulesetOptimization . $this->inComment;
		$this->out= $this->in . "\n";

		$this->testParserGenerator();
	}

	function testParserDebug() {
		$this->in= $this->inDebug . $this->inComment;
		$this->rule = array_merge(
			$this->ruleDebug,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testParser();
	}

	function testGeneratorDebug() {
		$this->in= $this->inDebug . $this->inComment;
		$this->rule = array_merge(
			$this->ruleDebug,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testGenerator();
	}

	function testParserGeneratorDebug() {
		$this->in= $this->inDebug . $this->inComment;
		$this->out= $this->in . "\n";

		$this->testParserGenerator();
	}

	function testParserHostid() {
		$this->in= $this->inHostid . $this->inComment;
		$this->rule = array_merge(
			$this->ruleHostid,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testParser();
	}

	function testGeneratorHostid() {
		$this->in= $this->inHostid . $this->inComment;
		$this->rule = array_merge(
			$this->ruleHostid,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testGenerator();
	}

	function testParserGeneratorHostid() {
		$this->in= $this->inHostid . $this->inComment;
		$this->out= $this->in . "\n";

		$this->testParserGenerator();
	}

	function testParserFingerprints() {
		$this->in= $this->inFingerprints . $this->inComment;
		$this->rule = array_merge(
			$this->ruleFingerprints,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testParser();
	}

	function testGeneratorFingerprints() {
		$this->in= $this->inFingerprints . $this->inComment;
		$this->rule = array_merge(
			$this->ruleFingerprints,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testGenerator();
	}

	function testParserGeneratorFingerprints() {
		$this->in= $this->inFingerprints . $this->inComment;
		$this->out= $this->in . "\n";

		$this->testParserGenerator();
	}

	function testParserReassemble() {
		$this->in= $this->inReassemble . $this->inComment;
		$this->rule = array_merge(
			$this->ruleReassemble,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testParser();
	}

	function testGeneratorReassemble() {
		$this->in= $this->inReassemble . $this->inComment;
		$this->rule = array_merge(
			$this->ruleReassemble,
			$this->ruleComment
			);
		$this->out= $this->in . "\n";

		$this->testGenerator();
	}

	function testParserGeneratorReassemble() {
		$this->in= $this->inReassemble . $this->inComment;
		$this->out= $this->in . "\n";

		$this->testParserGenerator();
	}
}
?>
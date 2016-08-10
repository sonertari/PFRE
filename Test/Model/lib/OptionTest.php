<?php
/* $pfre$ */

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
	/// @todo Test other types of options too
	protected $ruleSkip= 'set skip on { em0, em1 }';
	protected $sampleSkip= array(
		'type' => 'skip',
		'skip' => array(
			em0,
			em1,
			),
		);

	protected $ruleOptimization= 'set optimization high-latency';
	protected $sampleOptimization= array(
		'type' => 'optimization',
		'optimization' => 'high-latency',
		);

	function __construct()
	{
		$this->sample= $this->sampleSkip;

		parent::__construct();

		$this->rule= $this->ruleSkip . $this->ruleComment;
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
}
?>
<?php
/* $pfre: AnchorTest.php,v 1.4 2016/08/11 18:29:20 soner Exp $ */

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

require_once('FilterBase.php');

class AnchorTest extends FilterBase
{
	protected $inAnchor= 'anchor "test"';
	protected $ruleAnchor= array(
		'identifier' => 'test',
		);

	// This is the same rule inline anchor rule in pf.conf(5)
	protected $inInline= 'inline 	block
	anchor out {
		pass proto tcp from any to port { 25, 80, 443 }
	}
	pass in proto tcp to any port 22';

	protected $ruleInline= array(
		'inline' => '	block
	anchor out {
		pass proto tcp from any to port { 25, 80, 443 }
	}
	pass in proto tcp to any port 22',
		);

	protected $outInline= '{
	block
	anchor out {
		pass proto tcp from any to port { 25, 80, 443 }
	}
	pass in proto tcp to any port 22
}';

	function __construct()
	{
		$this->rule= array_merge(
			$this->ruleAnchor,
			$this->ruleInline
			);

		parent::__construct();

		$this->inFilterHead= $this->inAnchor . ' ' . $this->inDirection . ' ' . $this->inInterface . ' ' . $this->inAf . ' ' . $this->inProto . ' ' . $this->inSrcDest;

		$this->in= $this->inFilterHead . ' ' . $this->inFilterOpts . ' ' . $this->inInline . $this->inComment;

		$this->out= $this->inFilterHead . ' ' . $this->inFilterOpts . ' ' . $this->outInline . $this->inComment . "\n";
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
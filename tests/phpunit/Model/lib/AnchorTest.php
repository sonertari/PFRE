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

require_once('FilterBase.php');

class AnchorTest extends FilterBase
{
	protected $inAnchor= 'anchor "test"';
	protected $ruleAnchor= array(
		'identifier' => 'test',
		);

	// This is the same inline anchor rule in pf.conf(5)
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
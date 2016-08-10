<?php
/* $pfre: Anchor.php,v 1.7 2016/08/06 23:48:36 soner Exp $ */

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

$Nesting= 0;

class Anchor extends FilterBase
{	
	protected $keyAnchor= array(
		// identifier can be empty, but "anchors without explicit rules must specify a name"
		'anchor' => array(
			'method' => 'parseDelimitedStr',
			'params' => array('identifier'),
			),
		'inline' => array(
			'method' => 'parseNextNVP',
			'params' => array('inline'),
			),
		);

	protected $typeAnchor= array(
		'identifier' => array(
			'regex' => RE_ANCHOR_ID,
			),
		'inline' => array(
			'func' => 'IsInlineAnchor',
			'force' => TRUE,
			),
		);

	function __construct($str)
	{
		$this->keywords= $this->keyAnchor;

		$this->typedef= $this->typeAnchor;

		parent::__construct($str);
	}

	function sanitize()
	{
		$inline= '';
		$pos= strpos($this->str, 'inline');
		if ($pos) {
			// Do not sanitize inline rules
			$inline= trim(substr($this->str, $pos));
			$this->str= substr($this->str, 0, $pos);
		}

		parent::sanitize();

		if ($inline !== '') {
			$this->str.= $inline;
		}
	}

	function split()
	{
		$inline= '';
		$pos= strpos($this->str, 'inline');
		if ($pos) {
			// Do not split inline rules
			// Skip inline keyword
			$inline= substr($this->str, $pos + strlen('inline') + 1);
			$this->str= substr($this->str, 0, $pos);
		}

		parent::split();

		if ($inline !== '') {
			$this->words[]= 'inline';
			$this->words[]= $inline;
		}
	}

	function generate()
	{
		$this->str= 'anchor';
		$this->genValue('identifier', '"', '"');

		$this->genValue('direction');
		$this->genInterface();
		$this->genValue('af');
		$this->genItems('proto', 'proto');
		$this->genSrcDest();

		$this->genFilterOpts();

		// Inline rules should come last
		$this->genInline();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}

	function genInline()
	{
		if (isset($this->rule['inline'])) {
			// Inline rules should start on a new line
			// Ending brace (anchor-close) should be at the start of a new line
			/// @attention Inline rules are parsed and untainted in the Model before passing to pfctl
			$this->str.= " {\n" . $this->rule['inline'] . "\n}";
		}
	}
}

function IsInlineAnchor($str, $force= FALSE)
{
	global $LOG_LEVEL, $Nesting, $MaxAnchorNesting;

	$result= FALSE;
	
	// Do not allow more than $MaxAnchorNesting count of nested inline rules
	$max= $Nesting + 1 > $MaxAnchorNesting;
	if ($max) {
		Error("Validation Error: Reached max nesting for inline anchors: <pre>" . htmlentities(print_r($str, TRUE)) . '</pre>');
		pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Validation Error: Reached max nesting for inline anchors: $str");
	}

	if (!$max || $force) {
		$Nesting++;
		$ruleSet= new RuleSet();
		$result= $ruleSet->parse($str, $force);
		if (!$result) {
			if (LOG_DEBUG <= $LOG_LEVEL) {
				Error('Validation Error: Invalid inline rules, parser output: <pre>' . htmlentities(print_r(json_decode(json_encode($ruleSet), TRUE), TRUE)) . '</pre>');
			}
			pfrec_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Validation Error: Invalid inline rules: ' . print_r(json_decode(json_encode($ruleSet), TRUE), TRUE));
		}
		$Nesting--;
	}
	return $result;
}
?>

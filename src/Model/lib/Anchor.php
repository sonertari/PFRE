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

namespace Model;

/** 
 * Keeps the count of nested anchors in inline rules.
 */
$Nesting= 0;

/** 
 * Class for Anchor rules.
 */
class Anchor extends FilterBase
{	
	/** 
	 * Keywords for anchor rules.
	 * 
	 * Identifier can be empty, but "anchors without explicit rules must specify a name".
	 * 
	 * 'inline' keyword is inserted by the anchor parser.
	 */
	protected $keyAnchor= array(
		'anchor' => array(
			'method' => 'parseDelimitedStr',
			'params' => array('identifier'),
			),
		'inline' => array(
			'method' => 'parseNextNVP',
			'params' => array('inline'),
			),
		);

	/** 
	 * Type definition for anchor rules.
	 * 
	 * IsInlineAnchor() validates inline rules.
	 * 
	 * 'force' element instructs type checker to pass the $force param to IsInlineAnchor().
	 * Otherwise, this does not mean that rule loading will be forced.
	 */
	protected $typeAnchor= array(
		'identifier' => array(
			'regex' => RE_ANCHOR_ID,
			),
		'inline' => array(
			'func' => 'Model\\IsInlineAnchor',
			'force' => TRUE,
			),
		);

	function __construct($str)
	{
		$this->keywords= $this->keyAnchor;

		$this->typedef= $this->typeAnchor;

		parent::__construct($str);
	}

	/** 
	 * Sanitizes anchor rule sting.
	 * 
	 * We should not sanitize inline rules, because they will be parsed by a newly created
	 * RuleSet. So we remove the inline rules, sanitize the rest of the string as usual, and
	 * reinsert the inline rules back.
	 * 
	 * Note that inline comments are parsed and removed before sanitization, hence removal
	 * and reinsertion of inline rules does not cause a problem in parsing inline comments.
	 */
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

	/** 
	 * Splits anchor rule string into words.
	 * 
	 * Similarly to sanitize(), we should not split inline rules, because they will be parsed
	 * by the newly created RuleSet. However, the difference now is that we remove the 'inline'
	 * keyword and insert the rest as the value of that keyword in the rules array.
	 */
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

	/** 
	 * Generates anchor rule.
	 * 
	 * Inline rules are always appended to the end.
	 */
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

		$this->genInline();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}

	/** 
	 * Generates inline rules.
	 * 
	 * Inline rules should start on a new line.
	 * Ending brace (anchor-close) should be at the start of a new line.
	 * 
	 * @attention Note that inline rules are parsed and untainted in the Model before passing to pfctl.
	 */
	function genInline()
	{
		if (isset($this->rule['inline'])) {
			$this->str.= " {\n" . $this->rule['inline'] . "\n}";
		}
	}
}

/** 
 * Checks and validates any inline rules.
 * 
 * Since we create a new RuleSet object for each nested anchor, we limit the number of nesting.
 * 
 * @param string $str List of rule definitions in an array.
 * @param bool $force If set, continues checking and validating even if there are errors or $MaxAnchorNesting is reached.
 */
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

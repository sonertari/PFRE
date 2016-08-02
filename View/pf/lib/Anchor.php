<?php
/* $pfre: Anchor.php,v 1.9 2016/08/02 09:54:29 soner Exp $ */

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

class Anchor extends FilterBase
{	
	function __construct($str)
	{
		$this->keywords = array(
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
		$this->genItems('interface', 'on');
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
			// textarea inserts \r\n instead of just \n, which pfctl complains about
			// Inline rules should start on a new line
			// Ending brace should be on its own line
			/// @todo Untaint inline rules, parse before passing to pfctl?
			$this->str.= " {\n" . preg_replace('/\r/', '', $this->rule['inline']) . "\n}";
		}
	}

	function dispAction()
	{
		?>
		<td title="Id" nowrap="nowrap">
			<?php echo 'anchor ' . $this->rule['identifier']; ?>
			<text
		</td>
		<?php
	}

	function input()
	{
		$this->inputKey('identifier');

		$this->inputFilterHead();

		$this->inputInline();

		$this->inputFilterOpts();

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function inputInline()
	{
		// inputKey() trims, hence this new method
		if (filter_has_var(INPUT_POST, 'state')) {
			$this->rule['inline']= filter_input(INPUT_POST, 'inline');
		}
	}

	function edit($rulenumber, $modified, $testResult, $action)
	{
		$this->index= 0;
		$this->rulenumber= $rulenumber;

		$this->editHead($modified);

		$this->editText('identifier', 'Identifier', 'anchor-id', NULL, 'name, may be nested');

		$this->editFilterHead();

		$this->editInlineRules();

		$this->editFilterOpts();

		$this->editComment();
		$this->editTail($modified, $testResult, $action);
	}

	function editInlineRules()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Inline Rules').':' ?>
			</td>
			<td>
				<textarea cols="80" rows="5" id="inline" name="inline" placeholder="Enter inline rules here"><?php echo $this->rule['inline']; ?></textarea>
				<?php $this->PrintHelp('inline') ?>
			</td>
		</tr>
		<?php
	}
}
?>

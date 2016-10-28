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

namespace View;

class Anchor extends FilterBase
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber);
		$this->dispAction();
		$this->dispValue('direction', _TITLE('Direction'));
		$this->dispInterface();
		$this->dispValue('proto', _TITLE('Proto'));
		$this->dispSrcDest();
		$this->dispValue('state-filter', _TITLE('State'));
		$this->dispQueue();
		$this->dispInline();
		$this->dispTail($ruleNumber, $count);
	}
	
	/**
	 * Counts the lines in inline rules.
	 * 
	 * Inline rules always span beyond the anchor rule itself.
	 */
	function countLines()
	{
		if (isset($this->rule['inline'])) {
			// Add 1 for anchor-close line
			return count(explode("\n", $this->rule['inline'])) + 1;
		} else {
			return 0;
		}
	}

	function dispAction()
	{
		?>
		<td title="<?php echo _TITLE('Id') ?>" nowrap="nowrap">
			<?php echo $this->rule['identifier']; ?>
		</td>
		<?php
	}

	/**
	 * Displays inline rules.
	 * 
	 * We enclose all tabs into code tags, otherwise indentation is lost.
	 */
	function dispInline()
	{
		?>
		<td title="<?php echo _TITLE('Inline rules') ?>" colspan="2" nowrap="nowrap">
			<?php echo str_replace("\t", "<code>\t</code><code>\t</code>", nl2br(htmlentities($this->rule['inline']))); ?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputKey('identifier');

		$this->inputFilterHead();
		$this->inputFilterOpts();

		$this->inputInline();

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	/**
	 * Gets submitted inline rules.
	 * 
	 * inputKey() trims, hence this new method.
	 */
	function inputInline()
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			// textarea inserts \r\n instead of just \n, which pfctl complains about, so delete \r chars
			$this->rule['inline']= preg_replace('/\r/', '', filter_input(INPUT_POST, 'inline'));
		}
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified);

		$this->editText('identifier', _TITLE('Identifier'), 'anchor-id', NULL, _CONTROL('name, may be nested'));

		$this->editFilterHead();
		$this->editFilterOpts();

		$this->editInlineRules();

		$this->editComment();
		$this->editTail($modified, $testResult, $generateResult, $action);
	}

	function editInlineRules()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Inline Rules').':' ?>
			</td>
			<td>
				<textarea cols="80" rows="5" id="inline" name="inline" placeholder="<?php echo _CONTROL('Enter inline rules here') ?>"><?php echo $this->rule['inline']; ?></textarea>
				<?php $this->editHelp('inline') ?>
			</td>
		</tr>
		<?php
	}
}
?>

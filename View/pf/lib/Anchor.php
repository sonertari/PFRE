<?php
/* $pfre: Anchor.php,v 1.14 2016/08/04 14:42:52 soner Exp $ */

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
	function dispAction()
	{
		?>
		<td title="Id" nowrap="nowrap">
			<?php echo $this->rule['identifier']; ?>
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
		$this->editIndex= 0;
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
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Inline Rules').':' ?>
			</td>
			<td>
				<textarea cols="80" rows="5" id="inline" name="inline" placeholder="Enter inline rules here"><?php echo $this->rule['inline']; ?></textarea>
				<?php $this->editHelp('inline') ?>
			</td>
		</tr>
		<?php
	}
}
?>

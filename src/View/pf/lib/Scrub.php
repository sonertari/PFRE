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

class Scrub extends Filter
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber);
		$this->dispAction();
		$this->dispValue('direction', 'Direction');
		$this->dispInterface();
		$this->dispLog();
		$this->dispValue('proto', 'Proto');
		$this->dispSrcDest();
		$this->dispValue('min-ttl', 'Min-ttl');
		$this->dispValue('max-mss', 'Max-mss');
		$this->dispScrubOpts();
		$this->dispTail($ruleNumber, $count);
	}

	function dispScrubOpts()
	{
		?>
		<td title="Options">
			<?php echo ($this->rule['no-df'] ? 'no-df<br>' : '') . ($this->rule['random-id'] ? 'random-id<br>' : '') . ($this->rule['reassemble'] ? 'reassemble ' . $this->rule['reassemble'] . '<br>' : ''); ?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputAction();

		$this->inputFilterHead();

		$this->inputLog();
		$this->inputBool('quick');

		$this->inputBool('no-df');
		$this->inputBool('random-id');
		/// @todo This is bool actually, fix parser first
		$this->inputKey('reassemble');
		$this->inputKey('min-ttl');
		$this->inputKey('max-mss');

		$this->inputFilterOpts();

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified);

		$this->editAction();

		$this->editFilterHead();

		$this->editLog();
		$this->editCheckbox('quick', 'Quick');

		$this->editScrubOptions();
		$this->editText('min-ttl', 'Min TTL', NULL, 10, 'number');
		$this->editText('max-mss', 'Max MSS', NULL, 10, 'number');

		$this->editFilterOpts();

		$this->editComment();
		$this->editTail($modified, $testResult, $generateResult, $action);
	}

	function editScrubOptions()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Scrub Options').':' ?>
			</td>
			<td>
				<input type="checkbox" id="no-df" name="no-df" value="no-df" <?php echo ($this->rule['no-df'] ? 'checked' : '')?> />
				<label for="no-df">no-df</label>
				<?php $this->editHelp('no-df') ?>
				<br>
				<input type="checkbox" id="random-id" name="random-id" value="random-id" <?php echo ($this->rule['random-id'] ? 'checked' : '')?> />
				<label for="random-id">random-id</label>
				<?php $this->editHelp('random-id') ?>
				<br>
				<input type="checkbox" id="reassemble" name="reassemble" value="tcp" <?php echo ($this->rule['reassemble'] == 'tcp' ? 'checked' : '')?> />
				<label for="reassemble">reassemble tcp</label>
				<?php $this->editHelp('reassemble-tcp') ?>
			</td>
		</tr>
		<?php
	}
}
?>
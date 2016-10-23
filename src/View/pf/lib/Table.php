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

class Table extends Rule
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber);
		$this->dispId();
		$this->dispKey('const', 'Flag');
		$this->dispKey('persist', 'Flag');
		$this->dispKey('counters', 'Flag');
		$this->dispValues();
		$this->dispTail($ruleNumber, $count);
	}

	function dispId()
	{
		?>
		<td title="Id">
			<?php echo htmlentities($this->rule['identifier']); ?>
		</td>
		<?php
	}

	function dispValues()
	{
		?>
		<td title="Values" colspan="8">
			<?php
			$this->printValue($this->rule['data']);
			$this->printValue($this->rule['file'], 'file "', '"');
			?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputKey('identifier');
		$this->inputBool('const');
		$this->inputBool('persist');
		$this->inputBool('counters');
		$this->inputDel('data', 'delValue');
		$this->inputAdd('data', 'addValue');
		$this->inputDel('file', 'delFile');
		$this->inputAdd('file', 'addFile');

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified);

		$this->editText('identifier', 'Identifier', FALSE, NULL, 'string');
		$this->editFlags();
		$this->editValues();

		$this->editComment();
		$this->editTail($modified, $testResult, $generateResult, $action);
	}

	function editFlags()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Flags').':' ?>
			</td>
			<td>
				<input type="checkbox" id="const" name="const" value="const" <?php echo $this->rule['const'] ? 'checked' : ''; ?> />
				<label for="const">const</label>
				<?php $this->editHelp('const') ?>
				<br>
				<input type="checkbox" id="persist" name="persist" value="persist" <?php echo $this->rule['persist'] ? 'checked' : ''; ?> />
				<label for="persist">persist</label>
				<?php $this->editHelp('persist') ?>
				<br>
				<input type="checkbox" id="counters" name="counters" value="counters" <?php echo $this->rule['counters'] ? 'checked' : ''; ?> />
				<label for="counters">counters</label>
				<?php $this->editHelp('counters') ?>
			</td>
		</tr>
		<?php
	}

	function editValues()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Values').':' ?>
			</td>
			<td>
				<?php
				$this->editDeleteValueLinks($this->rule['data'], 'delValue');
				$this->editDeleteValueLinks($this->rule['file'], 'delFile', 'file "', '"');
				$this->editAddValueBox('addValue', 'add host or network', 'host or network', 30);
				echo '<br />';
				$this->editAddValueBox('addFile', 'add file', 'filename', 30);
				?>
			</td>
		</tr>
		<?php
	}
}
?>
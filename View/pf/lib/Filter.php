<?php
/* $pfre: Filter.php,v 1.14 2016/08/05 22:30:06 soner Exp $ */

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

class Filter extends FilterBase
{
	function dispInterface()
	{
		?>
		<td title="Interface">
			<?php
			if (isset($this->rule['interface'])) {
				$this->printValue($this->rule['interface']);
			} elseif (isset($this->rule['rdomain'])) {
				echo 'rdomain: ' . $this->rule['rdomain'];
			}
			?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputAction();

		$this->inputFilterHead();

		$this->inputLog();
		$this->inputBool('quick');

		$this->inputFilterOpts();

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function inputAction()
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			$this->inputKey('action');
			if (filter_input(INPUT_POST, 'action') === 'block') {
				$this->inputKey('blockoption');
			} else {
				unset($this->rule['blockoption']);
			}
		}
	}

	function inputInterface()
	{
		$this->inputDel('interface', 'delInterface');
		$this->inputAdd('interface', 'addInterface');
		$this->inputKey('rdomain');
	}

	function edit($ruleNumber, $modified, $testResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified);

		$this->editAction();

		$this->editFilterHead();

		$this->editLog();
		$this->editCheckbox('quick', 'Quick');

		$this->editFilterOpts();

		$this->editComment();
		$this->editTail($modified, $testResult, $action);
	}

	function editAction()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Action').':' ?>
			</td>
			<td>
				<select id="action" name="action">
					<option label="pass" <?php echo $this->rule['action'] == 'pass' ? 'selected' : ''; ?>>pass</option>
					<option label="match" <?php echo $this->rule['action'] == 'match' ? 'selected' : ''; ?>>match</option>
					<option label="block" <?php echo $this->rule['action'] == 'block' ? 'selected' : ''; ?>>block</option>
				</select>
				<?php
				$this->editHelp($this->rule['action']);
				?>
			</td>
		</tr>
		<?php
		if ($this->rule['action'] == 'block') {
			$this->editBlockOption();
		}
	}

	function editBlockOption()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Block Option').':' ?>
			</td>
			<td>
				<select id="blockoption" name="blockoption">
					<option value=""></option>
					<option value="drop" <?php echo ($this->rule['blockoption'] == 'drop' ? 'selected' : ''); ?>>drop</option>
					<option value="return" <?php echo ($this->rule['blockoption'] == 'return' ? 'selected' : ''); ?>>return</option>
					<option value="return-rst" <?php echo ($this->rule['blockoption'] == 'return-rst' ? 'selected' : ''); ?>>return-rst</option>
					<option value="return-icmp" <?php echo ($this->rule['blockoption'] == 'return-icmp' ? 'selected' : ''); ?>>return-icmp</option>
					<option value="return-icmp6" <?php echo ($this->rule['blockoption'] == 'return-icmp6' ? 'selected' : ''); ?>>return-icmp6</option>
				</select>
				<?php $this->editHelp('block') ?>
			</td>
		</tr>
		<?php
	}

	function editInterface()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Interface').':' ?>
			</td>
			<td>
				<?php
				$this->editDeleteValueLinks($this->rule['interface'], 'delInterface');
				$this->editAddValueBox('addInterface', NULL, 'if or macro', 10, isset($this->rule['rdomain']));
				$this->editHelp('interface');
				?>
				<input type="text" name="rdomain" id="rdomain" value="<?php echo $this->rule['rdomain']; ?>" size="10" placeholder="number" <?php echo isset($this->rule['interface']) ? 'disabled' : '' ?> />
				<label for="rdomain">routing domain</label>
				<?php $this->editHelp('rdomain') ?>
			</td>
		</tr>
		<?php
	}
}
?>

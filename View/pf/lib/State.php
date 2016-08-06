<?php 
/* $pfre: State.php,v 1.6 2016/08/06 02:13:05 soner Exp $ */

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

class State extends Timeout
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber);
		$this->dispState();
		$this->dispTail($ruleNumber, $count);
	}
	
	function dispState()
	{
		?>
		<td title="State Defaults" colspan="12">
			<?php
			$this->arr= array();

			$this->dispText('max');
			$this->dispText('max-src-states');
			$this->dispText('max-src-nodes');
			$this->dispText('max-src-conn');
			$this->dispText('max-src-conn-rate');

			$this->dispBool('sloppy');
			$this->dispBool('no-sync');
			$this->dispBool('pflow');

			$this->dispBool('if-bound');
			$this->dispBool('floating');

			$this->dispOverload();
			$this->dispSourceTrack();

			$this->dispTimeoutOpts();

			echo implode(', ', $this->arr);
			?>
		</td>
		<?php
	}

	function dispText($key)
	{
		if (isset($this->rule[$key])) {
			$this->arr[]= "$key: " . $this->rule[$key];
		}
	}
	
	function dispBool($key)
	{
		if (isset($this->rule[$key])) {
			$this->arr[]= $key;
		}
	}
	
	function dispOverload()
	{
		if (isset($this->rule['overload'])) {
			$str= 'overload: <' . $this->rule['overload'] . '>';
			if (isset($this->rule['flush'])) {
				$str.= ' flush';
				if (isset($this->rule['global'])) {
					$str.= ' global';
				}
			}
			$this->arr[]= htmlentities($str);
		}
	}
	
	function dispSourceTrack()
	{
		if (isset($this->rule['source-track'])) {
			$str= 'source-track';
			if (isset($this->rule['source-track-option'])) {
				$str.= ' ' . $this->rule['source-track-option'];
			}
			$this->arr[]= $str;
		}
	}
	
	function input()
	{
		$this->inputState();

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function inputState()
	{
		$this->inputKey('max');
		$this->inputKey('max-src-states');
		$this->inputKey('max-src-nodes');
		$this->inputKey('max-src-conn');
		$this->inputKey('max-src-conn-rate');

		$this->inputBool('sloppy');
		$this->inputBool('no-sync');
		$this->inputBool('pflow');

		$this->inputBool('if-bound');
		if (!$this->rule['if-bound']) {
			$this->inputBool('floating');
		}

		$this->inputKey('overload');
		$this->inputBool('flush');
		$this->inputBool('global');

		$this->inputBool('source-track');
		$this->inputKey('source-track-option');

		$this->inputTimeout();
	}

	function edit($ruleNumber, $modified, $testResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified);

		$this->editState();

		$this->editComment();
		$this->editTail($modified, $testResult, $action);
	}

	function editState()
	{
		$this->editText('max', 'Max states', 'max', 10, 'number');
		$this->editText('max-src-states', 'Max single host states', 'max-src-states', 10, 'number');
		$this->editText('max-src-nodes', 'Max addresses', 'max-src-nodes', 10, 'number');
		$this->editText('max-src-conn', 'Max connection', 'max-src-conn', 10, 'number');
		$this->editText('max-src-conn-rate', 'Max connection rate', 'max-src-conn-rate', 20, 'number/number');
		$this->editCheckbox('sloppy', 'Sloppy tracker');
		$this->editCheckbox('no-sync', 'No pfsync');
		$this->editCheckbox('pflow', 'Export to pflow');
		$this->editIfBinding();
		$this->editOverload();
		$this->editSourceTrack();
		$this->editTimeout();
	}

	function editIfBinding()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Interface binding').':' ?>
			</td>
			<td>
				<input type="checkbox" id="if-bound" name="if-bound" value="if-bound" <?php echo ($this->rule['if-bound'] ? 'checked' : ''); ?> <?php echo (isset($this->rule['floating']) ? 'disabled' : ''); ?> />
				<label for="if-bound">if-bound</label>
				<input type="checkbox" id="floating" name="floating" value="floating" <?php echo ($this->rule['floating'] ? 'checked' : ''); ?> <?php echo (isset($this->rule['if-bound']) ? 'disabled' : ''); ?> />
				<label for="floating">floating</label>
				<?php $this->editHelp('if-binding') ?>
			</td>
		</tr>
		<?php
	}

	function editOverload()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Overload').':' ?>
			</td>
			<td>
				<input type="text" size="20" id="overload" name="overload" value="<?php echo $this->rule['overload']; ?>"  placeholder="string"/>
				<input type="checkbox" id="flush" name="flush" value="flush" <?php echo ($this->rule['flush'] ? 'checked' : ''); ?> <?php echo (!isset($this->rule['overload']) ? 'disabled' : ''); ?> />
				<label for="flush">flush</label>
				<input type="checkbox" id="global" name="global" value="global" <?php echo ($this->rule['global'] ? 'checked' : ''); ?> <?php echo (!isset($this->rule['flush']) ? 'disabled' : ''); ?> />
				<label for="global">global</label>
				<?php $this->editHelp('overload') ?>
			</td>
		</tr>
		<?php
	}

	function editSourceTrack()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Enable source track').':' ?>
			</td>
			<td>
				<input type="checkbox" id="source-track" name="source-track" value="source-track" <?php echo ($this->rule['source-track'] ? 'checked' : ''); ?> />
				<select id="source-track-option" name="source-track-option" <?php echo (!isset($this->rule['source-track']) ? 'disabled' : ''); ?>>
					<option value=""></option>
					<option value="rule" <?php echo ($this->rule['source-track-option'] == 'rule' ? 'selected' : ''); ?>>rule</option>
					<option value="global" <?php echo ($this->rule['source-track-option'] == 'global' ? 'selected' : ''); ?>>global</option>
				</select>
				<?php $this->editHelp('source-track') ?>
			</td>
		</tr>
		<?php
	}
}
?>
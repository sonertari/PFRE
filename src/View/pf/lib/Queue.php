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

class Queue extends Rule
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber);
		$this->dispValue('name', _TITLE('Name'));
		$this->dispInterface();
		$this->dispValue('parent', _TITLE('Parent'));
		$this->dispBandwidth('bandwidth', 'bw', _TITLE('Bandwidth'), 3);
		$this->dispBandwidth('min', 'min', _TITLE('Min'), 2);
		$this->dispBandwidth('max', 'max', _TITLE('Max'), 2);
		$this->dispValue('qlimit', _TITLE('Qlimit'));
		$this->dispKey('default', _TITLE('Default'));
		$this->dispTail($ruleNumber, $count);
	}
	
	function dispBandwidth($key, $pre, $title, $colspan)
	{
		?>
		<td title="<?php echo $title; ?>" colspan="<?php echo $colspan; ?>">
			<?php echo $this->rule[$key] . ($this->rule["$pre-burst"] ? '<br>burst: ' . $this->rule["$pre-burst"] : '') . ($this->rule["$pre-time"] ? '<br>time: ' . $this->rule["$pre-time"] : ''); ?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputKey('name');
		$this->inputKey('interface');
		$this->inputKey('parent');
		$this->inputKey('bandwidth');
		$this->inputKey('bw-burst');
		$this->inputKey('bw-time');
		$this->inputKey('min');
		$this->inputKey('min-burst');
		$this->inputKey('min-time');
		$this->inputKey('max');
		$this->inputKey('max-burst');
		$this->inputKey('max-time');
		$this->inputKey('qlimit');
		$this->inputBool('default');

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified);

		$this->editText('name', _TITLE('Name'), FALSE, NULL, _CONTROL('string'));
		$this->editText('interface', _TITLE('Interface'), 'queue-interface', 10, _CONTROL('if or macro'));
		$this->editText('parent', _TITLE('Parent'), NULL, NULL, _CONTROL('string'));
		$this->editBandwidth('bandwidth', 'bw', _TITLE('Bandwidth'));
		$this->editBandwidth('min', 'min', _TITLE('Min'));
		$this->editBandwidth('max', 'max', _TITLE('Max'));
		$this->editText('qlimit', _TITLE('Qlimit'), NULL, NULL, _CONTROL('number'));
		$this->editCheckbox('default', _TITLE('Default'));

		$this->editComment();
		$this->editTail($modified, $testResult, $generateResult, $action);
	}

	function editBandwidth($key, $pre, $title)
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE($title).':' ?>
			</td>
			<td>
				<table style="width: auto;">
					<tr>
						<td class="ifs">
							<input type="text" id="<?php echo $key ?>" name="<?php echo $key ?>" size="15" value="<?php echo $this->rule[$key]; ?>" placeholder="<?php echo _CONTROL('number[(K|M|G)]') ?>" />
						</td>
						<td class="optitle"><?php echo $key ?><?php $this->editHelp('bandwidth') ?></td>
					</tr>
					<tr>
						<td class="ifs">
							<input type="text" id="<?php echo $pre ?>-burst" name="<?php echo $pre ?>-burst" size="15" value="<?php echo $this->rule["$pre-burst"]; ?>"
								   placeholder="<?php echo _CONTROL('number[(K|M|G)]') ?>" <?php echo isset($this->rule[$key]) ? '' : 'disabled'; ?> />
						</td>
						<td class="optitle">burst</td>
					</tr>
					<tr>
						<td class="ifs">
							<input type="text" id="<?php echo $pre ?>-time" name="<?php echo $pre ?>-time" size="15" value="<?php echo $this->rule["$pre-time"]; ?>"
								   placeholder="<?php echo _CONTROL('number(ms)') ?>" <?php echo isset($this->rule[$key]) ? '' : 'disabled'; ?> />
						</td>
						<td class="optitle">time</td>
					</tr>
				</table>
			</td>
		</tr>
		<?php
	}
}
?>
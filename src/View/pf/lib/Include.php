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

class _Include extends Rule
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber);
		$this->dispInclude();
		$this->dispTail($ruleNumber, $count);
	}
	
	function dispInclude()
	{
		?>
		<td class="include">
			<?php echo 'include'; ?>
		</td>
		<td title="File" colspan="11">
			<?php echo $this->rule['file']; ?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputKey('file');

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified);

		$this->editInclude();

		$this->editComment();
		$this->editTail($modified, $testResult, $generateResult, $action);
	}

	function editInclude()
	{
		global $View, $PF_CONFIG_PATH;

		$View->Controller($ruleFiles, 'GetPfRuleFiles');
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('File').':' ?>
			</td>
			<td>
				<select id="file" name="file">
					<option value=""></option>
					<?php
					foreach ($ruleFiles as $file) {
						$file= "$PF_CONFIG_PATH/$file";
						?>
						<option value="<?php echo $file; ?>" label="<?php echo $file; ?>" <?php echo ($this->rule['file'] == $file ? 'selected' : ''); ?>><?php echo $file; ?></option>
						<?php
					}
					?>
				</select>

			</td>
			<td class="none">
				<?php PrintHelpBox(_HELPBOX("Only files under the $PF_CONFIG_PATH folder can be included here.")) ?>
			</td>
		</tr>
		<?php
	}
}
?>